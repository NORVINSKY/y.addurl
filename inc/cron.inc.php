<?php

declare(strict_types=1);
set_time_limit(300);

const MAX_HARD_THREADS = 7;
const LOCK_TIMEOUT_SEC = 300; // 5 минут на выполнение задачи, потом блокировка снимается
// но реальный лимит будет вычисляться динамически на основе квоты Яндекса.
$currentTime = time();

// 1. Контроль количества потоков (процессов)
$allowedThreads = defined('THREADS') ? (int)THREADS : 1;
$allowedThreads = min($allowedThreads, MAX_HARD_THREADS);

// Считаем сколько сайтов сейчас в работе
$activeTasks = $DBH->count('sites',[
    'locked_until[>]' => $currentTime
]);

if ($activeTasks >= $allowedThreads) {
    exit;
}

// 2. Выборка сайта и атомарный захват (Locking)
$targetSite = $DBH->get('sites',['id', 'domain', 'api_key', 'yandex_host_id', 'last_used', 'proto'],[
    'status' => 1,
    'OR' => [
        'locked_until' => null,
        'locked_until[<]' => $currentTime
    ],
    'ORDER' => ['last_used' => 'ASC']
]);

if (!$targetSite) {
    exit;
}

$lockTime = $currentTime + LOCK_TIMEOUT_SEC;

$pdoStatement = $DBH->update('sites', ['locked_until' => $lockTime],[
    'id' => $targetSite['id'],
    'OR' =>[
        'locked_until' => null,
        'locked_until[<]' => $currentTime
    ]
]);

if ($pdoStatement->rowCount() === 0) {
    exit;
}

$dayLock = false;
$logger = new WebmasterLogger($db);

$siteId = (int)$targetSite['id'];
$domain = $targetSite['domain'];
$proto = $targetSite['proto'];
$hostUrl = protoDomain($proto, $domain);

$logger->log('INFO', "Сайт #{$siteId} взят в работу",['domain' => $domain]);

try {
    $apiKeyData = $DBH->get('api_keys', ['token', 'is_active'], [
        'id' => $targetSite['api_key']
    ]);

    if (!$apiKeyData || !$apiKeyData['is_active']) {
        throw new Exception("API ключ не найден или не активен");
    }

    $token = $apiKeyData['token'];
    $yandexHostId = $targetSite['yandex_host_id'];

    $webmaster = new YandexWebmaster($token);

    if (empty($yandexHostId)) {

        $addResult = $webmaster->addHost($hostUrl);

        if (!$addResult['success']) {
            if ($addResult['http_code'] === 409) {
                $hostId = protoDomain($proto, $domain, 2);
            } else {
                throw new Exception("Не смог добавить сайт - " . serialize($addResult));
            }
        } else {
            $hostId = $addResult['data']['host_id'];
        }

        $hostInfo = $webmaster->getHostInfo($hostId);

        if (!$hostInfo['data']['verified']) {
            $verificationInfo = $webmaster->getVerificationInfo($hostId);

            if (!$verificationInfo['success']) {
                throw new Exception("Не могу получить информацию о верификации");
            }

            $vData = $verificationInfo['data'];
            $applicableVerifiers = $vData['applicable_verifiers'] ??[];
            $verificationType = 'HTML_FILE';

            if (!in_array($verificationType, $applicableVerifiers, true)) {
                throw new Exception("Метод $verificationType недоступен. Доступны: " . implode(', ', $applicableVerifiers));
            }

            sleep(5);

            $startResult = $webmaster->startVerification($hostId, $verificationType);
            if (!$startResult['success']) {
                $errMsg = $startResult['data']['error_message'] ?? $startResult['error'] ?? 'неизвестная ошибка';
                $logger->log('WARNING', "Не могу запустить проверку (HTTP {$startResult['http_code']}): $errMsg", ['domain' => $domain]);
            }

            $finalResult = $webmaster->waitForVerification($hostId, maxAttempts: 10, sleepSeconds: 30);

            if ($finalResult['verified']) {
                $logger->log('INFO', "{$finalResult['message']}", ['domain' => $domain]);
                $DBH->update('sites',['yandex_host_id' => $hostId], ['id' => $siteId]);
            } else {
                //ошибка верификации по таймауту или из за отсутствия файла
                $DBH->update('sites', ['status' => 3],['id' => $siteId]);
                throw new Exception($finalResult['message']);
            }
        }
    }else{ $hostId = $yandexHostId; }

        $hostInfo = $webmaster->getHostInfo($hostId);

        if ($hostInfo['data']['verified']) {
            $summaryRes = $webmaster->getSummary($hostId);

            if ($summaryRes['success']) {
                $data = $summaryRes['data'];
                $siteInfo =[
                    'sqi'          => (int)($data['sqi'] ?? 0),
                    'indexed'      => (int)($data['searchable_pages_count'] ?? 0),
                    'excl_pages'   => (int)($data['excluded_pages_count'] ?? 0),
                    'errors_fatal' => (int)($data['site_problems']['FATAL'] ?? 0)
                ];

                $DBH->update('sites',[
                    'meta_data' => json_encode($siteInfo, JSON_UNESCAPED_UNICODE)
                ], ['id' => $siteId]);

                $logger->log('INFO', "Данные обновлены. ИКС: {$siteInfo['sqi']} | В индексе: {$siteInfo['indexed']} | Фатальных ошибок: {$siteInfo['errors_fatal']}", ['domain' => $domain]);
            } else {
                $logger->log('WARNING', "Не удалось получить сводку.",['domain' => $domain]);
            }
        } else {
            $DBH->update('sites',['status' => 3], ['id' => $siteId]);
            throw new Exception("Сайт не подтверждён");
        }


    # ==========================================
    # Работа со ссылками (Справедливый Переобход)
    # ==========================================

    // Проверка, определена ли константа (чтобы не было Notice)
    $rotationDays = defined('ROTATION_DAYS') ? ROTATION_DAYS : 14;
    $rotationTimestamp = time() - ($rotationDays * 86400);

    // 1. Запрашиваем реальную квоту у Яндекса
    $quota = $webmaster->getRecrawlQuota($hostId);

    if ($quota["success"] && $quota["data"]["quota_remainder"] >= 1) {

        $availableQuota = (int)$quota["data"]["quota_remainder"];

        // 2. Получаем корневой домен для поиска "соседей"
        $parser = new SimpleDomainParser(defined('_DATA_') ? _DATA_ : null);
        $rootDomain = $parser->getRootDomain($domain);

        // Получаем ID всех активных сабдоменов этого корня
        $siblingSitesIds = $DBH->select('sites', 'id',[
            'root_domain' => $rootDomain,
            'status' => 1
        ]);

        if (empty($siblingSitesIds)) {
            $siblingSitesIds = [$siteId]; // Fallback, если БД не обновилась
        }

        // 3. Собираем информацию о потребностях всех соседей
        $safeIds = array_map('intval', $siblingSitesIds);
        $placeholders = implode(',', array_fill(0, count($safeIds), '?'));
        $params = array_merge($safeIds, [$rotationTimestamp]);

        // Выполняем агрегирующий запрос через prepared statement
        $stmt = $DBH->pdo->prepare(
            "SELECT site_id, COUNT(id) as cnt 
             FROM links 
             WHERE site_id IN ($placeholders) 
               AND (used = 0 OR last_sent_at < ?) 
             GROUP BY site_id"
        );
        $stmt->execute($params);
        $pendingQuery = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pendingCounts = array_fill_keys($siblingSitesIds, 0);
        foreach ($pendingQuery as $row) {
            $pendingCounts[(int)$row['site_id']] = (int)$row['cnt'];
        }

        // 4. Вычисляем справедливую квоту для ТЕКУЩЕГО сайта
        $myFairLimit = calculateFairLimit($siteId, $availableQuota, $pendingCounts);

        if ($myFairLimit > 0) {

            // Выбираем ровно столько ссылок, сколько разрешено алгоритмом
            $linksToSend = $DBH->select('links', ['id', 'url'],[
                'site_id' => $siteId,
                'OR' => [
                    'used' => 0,
                    'last_sent_at[<]' => $rotationTimestamp
                ],
                'LIMIT' => $myFairLimit
            ]);

            if (!empty($linksToSend)) {
                $urls = array_column($linksToSend, 'url');

                $batch = $webmaster->addRecrawlTasks($hostId, $urls);

                foreach ($batch["results"] as $task) {
                    $lnID = getIdByUrl($linksToSend, $task['url']);
                    if ($task['success'] === true) {
                        $DBH->update('links',[
                            'used' => 1,
                            'yandex_status' => '1',
                            'last_sent_at' => time()
                        ], ['id' => $lnID]);
                    } else {
                        $DBH->update('links',[
                            'yandex_status' => '2',
                            'last_sent_at' => time()
                        ], ['id' => $lnID]);
                    }
                }

                $logger->log('INFO', "На переобход - Отправлено: {$batch['sent']} (Лимит: {$myFairLimit}) | Пропущено: {$batch['skipped']} | Остаток квоты: " . ($batch['quota_remainder'] ?? 'N/A'),['domain' => $domain]);
            }
        } else {
            $logger->log('INFO', "Справедливая доля исчерпана или нет ссылок. Квота оставлена другим сабдоменам.",['domain' => $domain]);
        }

    } else {
        $dayLock = true; // Квота исчерпана полностью
    }

} catch (Exception $e) {
    $logger->log('ERROR', "Ошибка обработки: " . $e->getMessage(),[
        'domain' => $domain
    ]);
} finally {
    // 6. Освобождение блокировки в любом случае
    if ($dayLock) {
        $DBH->update('sites',['locked_until' => time() + 86400, 'last_used' => date('Y-m-d H:i:s')], ['id' => $siteId]);
    } else {
        $DBH->update('sites',['locked_until' => null, 'last_used' => date('Y-m-d H:i:s')], ['id' => $siteId]);
    }
}