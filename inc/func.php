<?php
declare(strict_types=1);

use Medoo\Medoo;

/**
 * Проверка CSRF-токена. Вызывается перед обработкой POST-запросов.
 */
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
        exit;
    }
}

#Чистим строку от всякого
function cleanStr($str, $st=0){
    if($st==1){$str = strip_tags($str);}
    $str = str_replace("\n", "", $str);
    $str = str_replace("\r", "", $str);
    $str = preg_replace("/\t/"," ","$str");
    $str = preg_replace('/[ ]+/', ' ', $str);
    $str = trim($str);
    return $str;
}

#Делаем первую букву заглавной
function mb_ucfirst($string, $enc='UTF-8') {
    return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) .
        mb_substr($string, 1, mb_strlen($string, $enc), $enc);
}

#Capitalize
function mb_ucwords($str) {
    $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
    return ($str);
}

#Чистим массив от всякого
function cleanArray($a, $m=['',' ']){
    return array_diff($a, $m);
}

#Строка в ID
function str2id($s, $sp='-'){ return $st = preg_replace ("/[^a-zA-Z0-9]/", $sp, cleanStr($s)); }

#Модный var_dump
function xx($v){ echo '<pre>'; die(var_dump($v)); }

//Сортировка многомерного массива по внутренним ключам
function multisort($array, $index){
    $new_arr = []; $result = [];

    foreach($array as $k=>$v){ $new_arr[$k] = $v[$index]; }

    asort($new_arr);
    $keys = array_keys($new_arr);

    foreach($new_arr as $k=>$v){
        $result[$k] = $array[$k];
    } return $result;
}

//Перемешиваем ассоциативный массив - php.net
function shuffle_assoc( $array ){
    $keys = array_keys( $array );
    shuffle( $keys );
    return array_merge( array_flip( $keys ) , $array );
}

//генерация случайного слова
function randWord($len=5){
    $newWord = '';
    $glas = ["a","e","i","y","o","u"];
    $soglas = ["b","c","d","f","g","h","j","k","l","m","n","p","q","r","s","t","v","x","w","z"];

    for ($i=0; $i <$len/2 ; $i++) {
        $ng = rand(0, count($glas) - 1);
        $nsg = rand(0, count($soglas) - 1);
        $newWord .= $glas[$ng].$soglas[$nsg];
    } return $newWord;
}

//проверка авторизации
function isAuth(){
    return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
}

//лёгкое логирование
function ERRO_LOG($log, $e=0){
    if($e == 1){ echo "<pre>{$log}</pre>\r\n"; }
    file_put_contents(_DATA_ . 'log.dat', date("Y-m-d H:i:s").'#$#'.$log."\r\n", FILE_APPEND);
}

//сортировка многомерного массива по значению. пользоваться так: array_multisort_value($array, 'key', SORT_DESC);
function array_multisort_value(){
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
            }
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

//транслитерация
function translit($str) {
    $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');

    $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
    return str_replace($russian, $translit, $str);
}

//проверка является ли строка json объектом
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

//вывод лога
function getLOG(){
    global $_LOG; $t = '';

    if (count($_LOG) > 0){
        foreach ($_LOG as $l){
            $t .= '<p><mark>'.htmlspecialchars((string)$l, ENT_QUOTES, 'UTF-8').'</mark></p>';
        }
    }
    return $t;
}

//коды ошибок для проверки регулярки
function is_preg_error()
{
    $errors = array(
        PREG_NO_ERROR               => 'сode_0',
        PREG_INTERNAL_ERROR         => 'Code 1 : There was an internal PCRE error',
        PREG_BACKTRACK_LIMIT_ERROR  => 'Code 2 : Backtrack limit was exhausted',
        PREG_RECURSION_LIMIT_ERROR  => 'Code 3 : Recursion limit was exhausted',
        PREG_BAD_UTF8_ERROR         => 'Code 4 : The offset didn\'t correspond to the begin of a valid UTF-8 code point',
        PREG_BAD_UTF8_OFFSET_ERROR  => 'Code 5 : Malformed UTF-8 data',
    );

    return $errors[preg_last_error()];
}

//проверка регулярки
function regCheck($reg){
$re = true;
    @preg_match($reg, '');
    $p_e = is_preg_error();
    if($p_e !== 'сode_0'){
        $re = $p_e; //
    }
    return $re;
}

//обвязочка для проверки наличия подстроки
function chkStr($r, $s){
    if(stripos($r, $s) !== false){ return true; }
    return false;
}


/**
 * Проверяет, является ли строка валидным URL с протоколом http/https
 * и hostname с TLD минимум 2 символа.
 */
/**
 * Проверяет, является ли строка валидным URL с протоколом http/https
 * и hostname с TLD минимум 2 символа (включая кириллические домены .рф, .бел и т.д.).
 */
function isValidUrl(string $line): bool
{
    $line = trim($line);
    if ($line === '') return true; // пустые строки игнорируем

    // Только http:// или https://
    if (!preg_match('/^https?:\/\//i', $line)) {
        return false;
    }

    $parsed = parse_url($line);

    // parse_url не смог разобрать или нет host
    if ($parsed === false || empty($parsed['host'])) {
        return false;
    }

    // ОБЯЗАТЕЛЬНО используем mb_strtolower вместо strtolower для корректной обработки UTF-8
    $hostname = mb_strtolower($parsed['host'], 'UTF-8');

    // Модификатор 'u' в конце обязателен для работы Unicode-шаблонов в PHP
    return (bool) preg_match(
        '/^([\p{L}\p{N}]([\p{L}\p{N}\-]{0,61}[\p{L}\p{N}])?\.)+[\p{L}\p{N}][\p{L}\p{N}\-]{0,61}[\p{L}\p{N}]$/iu',
        $hostname
    );
}

// Обработчики API =======================================

function handleAddSites()
{
    global $DBH;
    $api_id = '';
    $proto = '';
    $links = [];
    $rootDomParse = new SimpleDomainParser();

    // Получаем данные из формы
    if (!empty($_FILES['sites_file']['tmp_name'])) {
        $content = file_get_contents($_FILES['sites_file']['tmp_name']);
        $links = array_filter(preg_split('/\r\n|\r|\n/', $content));
    } elseif (!empty($_POST['sites_text'])) {
        $links = array_filter(preg_split('/\r\n|\r|\n/', $_POST['sites_text']));
    } else {
        throw new Exception('No input data provided');
    }

    // --- Валидация всех строк до записи в БД ---
    $invalidLines = [];
    foreach ($links as $line) {
        if (!isValidUrl($line)) {
            $invalidLines[] = trim($line);
        }
    }

    if (!empty($invalidLines)) {
        $preview = array_slice($invalidLines, 0, 5);
        $more    = count($invalidLines) > 5
            ? ' ...и ещё ' . (count($invalidLines) - 5)
            : '';

        echo json_encode([
            'success' => false,
            'message' => 'Файл содержит невалидные строки (' . count($invalidLines) . '): '
                       . implode(', ', $preview) . $more,
            'invalid' => $invalidLines,
        ]);
        return;
    }
    // -------------------------------------------

    // id api ключа
    try {
        $api_id = cleanStr($_POST['api_id']);

        if (empty($api_id) || !is_numeric($api_id)) {
            throw new Exception('No input data provided');
        }
    } catch (Exception $e) {
        $errors[] = "Error processing API key ID: " . $e->getMessage();
    }

    $proto = cleanStr($_POST['proto']);

    $processed      = 0;
    $errors         = [];
    $existsDomains  = [];
    $siteIds  = [];
    $safeApiId = (int)$api_id;

    // Оборачиваем в транзакцию для производительности
    $DBH->action(function() use ($DBH, $links, $rootDomParse, $api_id, $proto, &$processed, &$errors, &$existsDomains, &$siteIds) {
        foreach ($links as $url) {
            if (empty(trim($url))) continue;

            try {
                $domain = extractDomain($url);

                if (!isset($existsDomains[$domain])) {
                    $exists = $DBH->get('sites', 'id', ['domain' => $domain]);
                } else {
                    $exists = $existsDomains[$domain];
                }

                $rootDomain = $rootDomParse->getRootDomain($domain);
                if (!$exists) {
                    $DBH->insert('sites', ['domain' => $domain, 'api_key' => $api_id, 'proto'=>$proto, 'root_domain'=>$rootDomain]);
                    $siteId = $DBH->id();
                    $existsDomains[$domain] = $siteId;
                } else {
                    $siteId = $exists;
                }

                $siteIds[$siteId] = true;

                $DBH->insert('links', [
                    'site_id' => $siteId,
                    'url'     => normalizeUrl($url)
                ]);

                $processed++;
            } catch (Exception $e) {
                $errors[] = "Error processing '$url': " . $e->getMessage();
            }
        }
    });

    $DBH->update("options", [
        "val" => Medoo::raw("(SELECT COUNT(*) FROM sites)")
    ], ["name" => "sites_cnt"]);

    $apiSitesCnt = $DBH->count('sites', ['api_key' => $safeApiId]);
    $DBH->update("api_keys", [
        "sites" => $apiSitesCnt
    ], ["id" => $safeApiId]);

    //пересчитываем ссылки для новоприбывших
    foreach($siteIds as $siteId => $valt) {
        $safeSiteId = (int)$siteId;
        $linksCnt = $DBH->count('links', ['site_id' => $safeSiteId]);
        $DBH->update("sites", [
            "linkz" => $linksCnt
        ], ["id" => $safeSiteId]);
    }

    echo json_encode([
        'success' => true,
        'message' => "Processed $processed links",
        'errors'  => $errors,
    ]);
}



function handleAddLinks()
{
    global $DBH;

    $siteId = $_POST['site_id'] ?? null;
    if (!$siteId || !($siteData = $DBH->get('sites', ['id', 'domain'], ['id' => $siteId]))) {
        throw new Exception('Invalid Site ID');
    }

    $targetDomain  = $siteData['domain'];
    $existingLinks = $DBH->select('links', 'url', ['site_id' => $siteId]);
    $existingLinksMap = array_flip($existingLinks);

    $inputLinks = [];
    if (!empty($_FILES['links_file']['tmp_name'])) {
        $content    = file_get_contents($_FILES['links_file']['tmp_name']);
        $inputLinks = preg_split('/\r\n|\r|\n/', $content);
    } elseif (!empty($_POST['links_text'])) {
        $inputLinks = preg_split('/\r\n|\r|\n/', $_POST['links_text']);
    } else {
        throw new Exception('No input data provided');
    }

    // --- Валидация всех строк до транзакции ---
    $invalidLines = [];
    foreach ($inputLinks as $line) {
        if (!isValidUrl($line)) {
            $invalidLines[] = trim($line);
        }
    }

    if (!empty($invalidLines)) {
        $preview = array_slice($invalidLines, 0, 5);
        $more    = count($invalidLines) > 5
            ? ' ...и ещё ' . (count($invalidLines) - 5)
            : '';

        echo json_encode([
            'success' => false,
            'message' => 'Файл содержит невалидные строки (' . count($invalidLines) . '): '
                       . implode(', ', $preview) . $more,
            'invalid' => $invalidLines,
        ]);
        return;
    }
    // ------------------------------------------

    $addedCount          = 0;
    $duplicateCount      = 0;
    $invalidCount        = 0;
    $domainMismatchCount = 0;

    $DBH->action(function() use (
        $DBH, $siteId, $targetDomain, $inputLinks, $existingLinksMap,
        &$addedCount, &$duplicateCount, &$invalidCount, &$domainMismatchCount
    ) {
        foreach ($inputLinks as $rawUrl) {
            $url = trim($rawUrl);
            if (empty($url)) continue;

            try {
                $normalizedUrl = normalizeUrl($url);
                $urlDomain     = extractDomain($normalizedUrl);

                if ($urlDomain !== $targetDomain) {
                    $domainMismatchCount++;
                    continue;
                }

                if (isset($existingLinksMap[$normalizedUrl])) {
                    $duplicateCount++;
                    continue;
                }

                $DBH->insert('links', [
                    'site_id' => $siteId,
                    'url'     => $normalizedUrl,
                ]);

                $existingLinksMap[$normalizedUrl] = true;
                $addedCount++;
            } catch (Exception $e) {
                $invalidCount++;
                continue;
            }
        }
    });


    $safeSiteId = (int)$siteId;
    $linksCnt = $DBH->count('links', ['site_id' => $safeSiteId]);
    $DBH->update("sites", [
        "linkz" => $linksCnt
    ], ["id" => $safeSiteId]);

    echo json_encode([
        'success'          => true,
        'added'            => $addedCount,
        'duplicates'       => $duplicateCount,
        'invalid'          => $invalidCount,
        'domain_mismatches'=> $domainMismatchCount,
        'total_processed'  => count($inputLinks),
        'target_domain'    => $targetDomain,
    ]);
}

// Улучшенная функция извлечения домена
function extractDomain($url) {
    $parsed = parse_url($url);
    if (!isset($parsed['host'])) {
        throw new Exception('Invalid URL: no host found');
    }

    $domain = strtolower($parsed['host']);
    $domain = preg_replace('/^www\./', '', $domain);

    // Удаляем порт если есть (example.com:8080 → example.com)
    $domain = preg_replace('/:\d+$/', '', $domain);

    return $domain;
}

function handleSiteAction($endpoint) {
    global $DBH;

    // Включаем поддержку foreign keys (ОБЯЗАТЕЛЬНО для SQLite)
    $DBH->query('PRAGMA foreign_keys = ON');

    $action = basename($endpoint);
    $siteId = filter_input(INPUT_POST, 'site_id', FILTER_VALIDATE_INT);

    // Проверка ID сайта
    if (!$siteId || $siteId < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Неверный ID сайта']);
        return;
    }

    try {
        // Проверяем существование сайта
        if (!$DBH->has('sites', ['id' => $siteId])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Сайт не найден']);
            return;
        }

        $response = [
            'success' => true,
            'message' => '',
            'action' => $action,
            'site_id' => $siteId
        ];

        switch ($action) {
            case 'start':
                $DBH->update('sites', ['status' => 1], ['id' => $siteId]);
                $response['message'] = 'Сайт запущен';
                break;

            case 'stop':
                $DBH->update('sites', ['status' => 0], ['id' => $siteId]);
                $response['message'] = 'Сайт остановлен';
                break;

            case 'delete':
                $delAPIid = $DBH->select('sites', 'api_key', ['id' => $siteId]);
                $deleted = $DBH->delete('sites', ['id' => $siteId]);
                if ($deleted->rowCount() === 0) {
                    throw new Exception('Не удалось удалить сайт');
                }

                // Получаем текущую страницу из запроса
                $currentPage = max(1, (int)($_POST['current_page'] ?? 1));

                // Считаем оставшиеся сайты на текущей странице
                $remainingOnPage = $DBH->count('sites', [
                    "id" => $DBH->select('sites', 'id', [
                        "LIMIT" => [($currentPage - 1) * ITMS_PP, ITMS_PP],
                        "ORDER" => ["id" => "DESC"] // Такой же как в основном запросе
                    ])
                ]);

                // Определяем целевой URL для редиректа
                if ($remainingOnPage === 0 && $currentPage > 1) {
                    // Если страница пустая и это не первая страница
                    $response['redirect'] = "?p=" . ($currentPage - 1);
                } else {
                    // Оставляем на текущей странице
                    $response['redirect'] = "?p=" . $currentPage;
                }

                //обновляем счетчик сайтов
                $DBH->update("options", [
                    "val" => Medoo::raw("(SELECT COUNT(*) FROM sites)")
                ], ["name" => "sites_cnt"]);

                //обновляем счетчик на API ключе
                $safeDelApiId = (int)$delAPIid[0];
                $apiSitesCnt = $DBH->count('sites', ['api_key' => $safeDelApiId]);
                $DBH->update("api_keys", [
                    "sites" => $apiSitesCnt
                ], ["id" => $safeDelApiId]);

                $response['message'] = 'Сайт успешно удален';
                break;

            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
                return;
        }

        // Отправляем единый ответ
        echo json_encode($response);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage(),
            'error_info' => $DBH->error() // Доп. информация для отладки
        ]);
    }
}

/**
 * @throws Exception
 */
function handleKeyAction($endpoint) {
    global $DBH;

    $action = basename($endpoint);

    // Обработка добавления нового ключа (не требует ID)
    if ($action === 'add') {
        $value = trim($_POST['add_api_key'] ?? '');
        if (empty($value)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'API Key value is required']);
            return;
        }

        try {
            // Вставляем новый ключ
            $DBH->insert('api_keys', [
                'value' => $value,
                'is_active' => 2,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Получаем ID добавленного ключа
            $keyId = $DBH->id();

            // Возвращаем полный ответ с данными ключа
            if($keyId != '0'){
                echo json_encode([
                    'success' => true,
                    'key_id' => $keyId,
                    'key_value' => $value,
                    "message" => "Ключ добавлен",
                ]);
            }else{
                echo json_encode([
                    'success' => false,
                    "message" => "Ключ уже существует",
                ]);
            }

            return;

        } catch (Exception $e) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'API key already exists']);
            return;
        }
    }

    // Для всех остальных действий требуется keyId
    $keyId = $_POST['api_key_id'] ?? null;
    if (!$keyId) throw new Exception('API Key ID is required');

    switch ($action) {
        case 'toggle-status':
            $current = $DBH->get('api_keys', 'is_active', ['id' => $keyId]);

            if ($current === null) throw new Exception('API Key not found');

            if($current == '2'){
                echo json_encode(['success' => true, 'is_active'=>$current, "message" => "Сперва авторизуй токен"]);
                break;
            }else{
                if($current == '1'){ $current = '0'; }else{ $current = '1'; }
                $updRes = $DBH->update('api_keys', [
                    'is_active' => $current,
                    'last_checked' => date('Y-m-d H:i:s')
                ], ['id' => $keyId]);
            }

            if($updRes->rowCount() > 0){
                echo json_encode(['success' => true, 'is_active'=>$current, "message" => "Статус ключа изменен"]);
            }else{
                echo json_encode(['success' => false]); //не получилось обновить статус
            }

            break;

        case 'delete':


            $deleted = $DBH->delete('api_keys', ['id' => $keyId]);

            if (!$deleted) throw new Exception('API ключ не найден или еще есть связанные сайты');

            echo json_encode(['success' => true]);

            break;

        default:
            throw new Exception('Invalid action');
    }
}

function handleMassDeleteSites() {
    global $DBH;

    $siteIds = array_filter(array_map('intval', $_POST['site_ids'] ?? [])); //массив с id на удаление

    if (empty($siteIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Не выбраны сайты для удаления']);
        return;
    }

    $currentPage = max(1, (int)($_POST['current_page'] ?? 1));

    try {
        $DBH->action(function() use ($DBH, $siteIds) {
            $DBH->exec('PRAGMA foreign_keys = ON');
            $deletedCount = $DBH->delete('sites', ['id' => $siteIds])->rowCount();

            if ($deletedCount !== count($siteIds)) {
                throw new Exception("Удалено $deletedCount из ".count($siteIds)." сайтов");
            }
        });

        // Проверка остатка на текущей странице
        $remainingOnPage = $DBH->count('sites', [
            "id" => $DBH->select('sites', 'id', [
                "LIMIT" => [($currentPage - 1) * ITMS_PP, ITMS_PP],
                "ORDER" => ["id" => "DESC"]
            ])
        ]);

        $response = [
            'success' => true,
            'message' => 'Массовое удаление выполнено'
        ];

        // Редирект только если страница пуста И это не первая страница
        if ($remainingOnPage === 0 && $currentPage > 1) {
            $response['redirect'] = "?p=" . ($currentPage - 1);
        }

        //обновляем счетчик сайтов
        $DBH->update("options", [
            "val" => Medoo::raw("(SELECT COUNT(*) FROM sites)")
        ], ["name" => "sites_cnt"]);

        //обновляем счетчики на API ключах
        $selApi = $DBH->select('api_keys', ['id']);

        foreach ($selApi as $api_id) {
            $safeId = (int)$api_id['id'];
            $apiSitesCnt = $DBH->count('sites', ['api_key' => $safeId]);
            $DBH->update("api_keys", [
                "sites" => $apiSitesCnt
            ], ["id" => $safeId]);
        }

        echo json_encode($response);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage()
        ]);
    }
}

//приводим url в нормальное состояние
function normalizeUrl($url)
{
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return rtrim($url, '/');
}

//отображение статуса API ключа
function apiKeyStatus($st,$k)
{
    $v = '';
    $safeK = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
    if($st == '1'){
        $v = '<span class="badge bg-success">Active</span>';
    }elseif ($st == '2'){
        $v = '<span class="badge bg-warning"><a href="https://oauth.yandex.ru/authorize?response_type=token&client_id='.$safeK.'&state='.$safeK.'">>>AUTH<<</a></span>';
    }else{ $v = '<span class="badge bg-secondary">Inactive</span>'; }
    echo $v;
}

// Проверка истек ли срок действия ключа
function isKeyExpired($expiresAt)
{
    if (empty($expiresAt)) return false;
    return strtotime($expiresAt) < time();
}

//получение OAuth токена
function getOToken()
{
    header("Content-Type: text/html; charset=UTF-8");

echo <<<OAUTH
Авторизуемся...
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли в URL хэш, содержащий access_token
    if (window.location.hash.includes('access_token=')) {
        
        const hashString = window.location.hash.substring(1);
        const hashParams = new URLSearchParams(hashString);
        
        const accessToken = hashParams.get('access_token');
        const expiresIn = hashParams.get('expires_in');
        const state = hashParams.get('state');

        if (accessToken && expiresIn) {
            // Если нужные параметры есть, отправляем их на бэкенд
            fetch('/api/save-token', { // Примерный URL вашего API-метода
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    access_token: accessToken,
                    state: state,
                    expires_in: expiresIn
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Токен успешно сохранен.
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                    
                    // Перенаправляем на страницу с API ключами
                    window.location.href = '/#api'; 
                } else {
                    // Обработка ошибки сохранения на бэкенде
                    alert('Не удалось сохранить токен: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка при отправке токена на сервер:', error);
                alert('Произошла ошибка сети.');
            });
        }
    }
});
</script>

OAUTH;


    die;
}

//Сокращает длинный API ключ для отображения в интерфейсе
function shortenApiKey( $apiKey, int $visibleChars = 3): string
{

    if(!is_string($apiKey)){ return 'N/A'; }

    // Проверяем, что ключ достаточно длинный для сокращения
    $length = strlen($apiKey);
    if ($length <= ($visibleChars * 2)) {
        return $apiKey; // Возвращаем как есть, если ключ слишком короткий
    }

    // Берем первые N символов
    $start = substr($apiKey, 0, $visibleChars);

    // Берем последние N символов
    $end = substr($apiKey, -$visibleChars);

    // Собираем сокращенную версию
    return $start . '...' . $end;
}

//сохранение токена
function saveToken(){
    global $DBH;

    $json_input = file_get_contents('php://input'); // Получаем сырое тело запроса, так как данные отправлены в формате JSON.

    $data = json_decode($json_input, true);

    // Проверяем, что JSON был корректно декодирован и содержит необходимые ключи.
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['access_token']) || !isset($data['expires_in'])) {
        http_response_code(400); // 400 Bad Request
        echo json_encode(['success' => false, 'message' => 'Некорректный формат данных или отсутствуют обязательные параметры (access_token, expires_in).']);
        exit;
    }

    $accessToken = trim((string)$data['access_token']);
    $expiresInSeconds = $data['expires_in'];
    $state = trim((string)$data['state']);

    if (empty($accessToken) || empty($state) || !is_numeric($expiresInSeconds) || $expiresInSeconds <= 0) {
        http_response_code(400); // 400 Bad Request
        echo json_encode(['success' => false, 'message' => 'access_token не может быть пустым, а expires_in должен быть положительным числом.']);
        exit;
    }

    $expirationTimestamp = date('Y-m-d H:i:s', time() + (int)$expiresInSeconds);

    $updated = $DBH->update('api_keys', ['token'=>$accessToken, 'expired'=>$expirationTimestamp, 'is_active'=>1], ['value' => $state]);

    if($updated->rowCount() > 0){
        http_response_code(200); // 200 OK
        echo json_encode([
            'success' => true,
            'message' => 'Токен успешно обработан.'
        ]);
    }else{
        http_response_code(400); // 400 Bad Request
        echo json_encode(['success' => false, 'message' => 'Не получилось авторизоваться']);
        exit;
    }
}

class WebmasterLogger {
    private Medoo $db;

    public function __construct(Medoo $db) {
        $this->db = $db;
    }

    /**
     * Запись лога.
     */
    public function log(string $level, string $message, array $context = []): void {
        $this->db->insert('api_logs', [
            'level' => strtoupper($level),
            'message' => $message,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'created_at' => time() // Явное сохранение UNIX Timestamp
        ]);
    }

    /**
     * Получение логов для AJAX-пуллинга на фронте.
     */
    public function getLogsSince(int $lastId = 0, int $limit = 50): array {
        return $this->db->select('api_logs', [
            'id', 'level', 'message', 'context', 'created_at'
        ], [
            'id[>]' => $lastId,
            'ORDER' => ['id' => 'ASC'],
            'LIMIT' => $limit
        ]);
    }
}

/**
 * Клиент Яндекс.Вебмастер API v4
 * Документация: https://yandex.ru/dev/webmaster/doc/dg/concepts/about.html
 * Требует PHP >= 8.0
 */
class YandexWebmaster
{
    private string $apiUrl = 'https://api.webmaster.yandex.net/v4';
    private ?int $userId = null;

    public function __construct(private readonly string $token) {}

    /**
     * Получить User ID текущего пользователя (кешируется)
     */
    public function getUserId(): ?int
    {
        if ($this->userId !== null) {
            return $this->userId;
        }

        $result = $this->sendRequest('GET', '/user');

        if ($result['success'] && isset($result['data']['user_id'])) {
            $this->userId = (int) $result['data']['user_id'];
        }

        return $this->userId;
    }

    /**
     * Добавить сайт в Яндекс.Вебмастер
     *
     * @param string $hostUrl Формат: https://example.com:443
     */
    public function addHost(string $hostUrl): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('POST', "/user/$uid/hosts", ['host_url' => $hostUrl])
        );
    }

    /**
     * Получить список добавленных сайтов
     */
    public function getHosts(): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('GET', "/user/$uid/hosts")
        );
    }

    /**
     * Получить информацию о конкретном сайте
     */
    public function getHostInfo(string $hostId): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('GET', "/user/$uid/hosts/" . urlencode($hostId))
        );
    }

    /**
     * ШАГ 1: Получить код подтверждения (UIN) и информацию о верификации
     */
    public function getVerificationInfo(string $hostId): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('GET', "/user/$uid/hosts/" . urlencode($hostId) . '/verification')
        );
    }

    /**
     * ШАГ 2: Запустить процедуру подтверждения прав
     * Вызывать ПОСЛЕ размещения токена на сайте.
     *
     * @param string $verificationType META_TAG | HTML_FILE | DNS
     */
    public function startVerification(string $hostId, string $verificationType): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest(
            'POST',
            "/user/$uid/hosts/" . urlencode($hostId) . '/verification?verification_type=' . urlencode($verificationType)
        )
        );
    }

    /**
     * Опрос статуса верификации с паузами.
     * ТОЛЬКО для CLI — использует sleep().
     *
     * @param string $hostId
     * @param int $maxAttempts  Максимум попыток
     * @param int $sleepSeconds Пауза между попытками
     */
    public function waitForVerification(string $hostId, int $maxAttempts = 7, int $sleepSeconds = 10): array
    {
        for ($i = 0; $i < $maxAttempts; $i++) {

            $status = $this->getVerificationInfo($hostId);

            // 1. Защита от временных сбоев API Яндекса
            if (!$status['success']) {
                if ($i < $maxAttempts - 1) {
                    sleep($sleepSeconds);
                    continue; // Не прерываемся, пробуем достучаться ещё раз
                }
                return['verified' => false, 'state' => 'error', 'message' => 'Не удалось получить статус после ' . $maxAttempts . ' попыток'];
            }

            $state = $status['data']['verification_state'] ?? 'NONE';

            if ($state === 'VERIFIED') {
                return['verified' => true, 'state' => $state, 'message' => 'Сайт успешно подтверждён'];
            }

            if ($state === 'VERIFICATION_FAILED') {
                $reason  = $status['data']['fail_info']['reason']  ?? 'неизвестная причина';
                $message = $status['data']['fail_info']['message'] ?? '';
                return['verified' => false, 'state' => $state, 'message' => trim("$reason. $message")];
            }

            // 2. Защита от "залипания" в статусе NONE
            // Если после двух проверок статус всё ещё NONE, значит команда startVerification не сработала на стороне Яндекса.
            if ($state === 'NONE' && $i >= 1) {
                return['verified' => false, 'state' => 'NONE', 'message' => 'Проверка прав не запустилась на серверах Яндекса'];
            }

            // IN_PROGRESS — продолжаем ждать
            if ($i < $maxAttempts - 1) {
                sleep($sleepSeconds);
            }
        }

        return['verified' => false, 'state' => 'timeout', 'message' => 'Превышено время ожидания верификации'];
    }

    /**
     * Отправить один URL на переобход (переиндексацию).
    * */
    public function addRecrawlTask(string $hostId, string $url): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest(
            'POST',
            "/user/$uid/hosts/" . $this->hostEncode($hostId) . '/recrawl/queue',
            ['url' => $url]
        )
        );
    }

    /**
     * Отправить несколько URL на переобход.
     * Останавливается при исчерпании квоты.
     *
     * @param string[] $urls
     */
    public function addRecrawlTasks(string $hostId, array $urls): array
    {
        $results        = [];
        $sent           = 0;
        $skipped        = 0;
        $quotaRemainder = null;

        foreach ($urls as $url) {
            if ($quotaRemainder !== null && $quotaRemainder <= 0) {
                $skipped++;
                $results[] = ['url' => $url, 'success' => false, 'error' => 'quota_exhausted'];
                continue;
            }

            $result = $this->addRecrawlTask($hostId, $url);

            if ($result['success']) {
                $sent++;
                $quotaRemainder = $result['data']['quota_remainder'] ?? $quotaRemainder;
                $results[] = [
                    'url'             => $url,
                    'success'         => true,
                    'task_id'         => $result['data']['task_id'] ?? null,
                    'quota_remainder' => $quotaRemainder,
                ];
            } else {
                $skipped++;
                $results[] = [
                    'url'       => $url,
                    'success'   => false,
                    'error'     => $result['data']['error_message'] ?? $result['error'] ?? 'unknown',
                    'http_code' => $result['http_code'],
                ];
            }
        }

        return [
            'sent'            => $sent,
            'skipped'         => $skipped,
            'quota_remainder' => $quotaRemainder,
            'results'         => $results,
        ];
    }


    /**
     * Проверить суточную квоту на переобход.
     * Возвращает daily_quota (лимит) и quota_remainder (остаток).
     */
    public function getRecrawlQuota(string $hostId): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('GET', "/user/$uid/hosts/" . $this->hostEncode($hostId) . '/recrawl/quota')
        );
    }

    // -------------------------------------------------------------------------
    // Приватные методы
    // -------------------------------------------------------------------------

    /**
     * Двойное кодирование hostId для подстановки в путь URL.
     * Сервер Яндекса (Jetty) отклоняет %2F в пути с ошибкой
     * "Ambiguous URI path separator". Двойное кодирование (%252F)
     * заставляет его воспринимать слеш как данные, а не разделитель пути.
     */
    private function hostEncode(string $hostId): string
    {
        $hostId = preg_replace('#^(https?)://#', '$1:', $hostId);
        return urlencode($hostId);
    }

    /**
     * Получить userId и выполнить коллбек. Возвращает ошибку, если userId не получен.
     */
    private function withUserId(callable $callback): array
    {
        $userId = $this->getUserId();

        if ($userId === null) {
            return ['success' => false, 'http_code' => 0, 'data' => null, 'error' => 'Не удалось получить User ID'];
        }

        return $callback($userId);
    }

    /**
     * Отправить запрос к API
     */
    private function sendRequest(string $method, string $path, ?array $data = null): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->apiUrl . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Authorization: OAuth ' . $this->token,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
        ]);

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'http_code' => 0, 'data' => null, 'error' => 'CURL: ' . $curlError];
        }

        $decoded = json_decode((string) $response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'data'      => null,
                'error'     => 'JSON decode error: ' . json_last_error_msg(),
            ];
        }

        return [
            'success'   => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data'      => $decoded,
            'error'     => null,
        ];
    }

    /**
     * Получить сырую историю ИКС (индекс качества сайта)
     * Документация: https://yandex.ru/dev/webmaster/doc/ru/reference/sqi-history
     */
    public function getSqiHistory(string $hostId): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('GET', "/user/$uid/hosts/" . $this->hostEncode($hostId) . '/sqi-history')
        );
    }

    /**
     * Получить текущий (последний) ИКС сайта
     * @return int Значение ИКС (0 в случае ошибки или отсутствия данных)
     */
    public function getCurrentSqi(string $hostId): int
    {
        $response = $this->getSqiHistory($hostId);

        if (!$response['success'] || empty($response['data']['points'])) {
            return 0;
        }

        $points = $response['data']['points'];

        // Сортируем точки по дате по убыванию, чтобы гарантированно взять самое свежее значение
        usort($points, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return (int)($points[0]['value'] ?? 0);
    }

    /**
     * Получить общую сводку по сайту (ИКС, страницы в поиске, ошибки)
     * Документация: https://yandex.ru/dev/webmaster/doc/ru/reference/host-id-summary
     */
    public function getSummary(string $hostId): array
    {
        return $this->withUserId(fn(int $uid) =>
        $this->sendRequest('GET', "/user/$uid/hosts/" . $this->hostEncode($hostId) . '/summary')
        );
    }

}

/**
 * Ищет ID по URL в массиве-справочнике.
 *
 * @param array $source Массив вида: [['id' => 2305, 'url' => '...'], ...]
 * @param string $url URL для поиска
 * @return int|null Найденный ID или null, если URL не найден
 */
function getIdByUrl(array $source, string $url): ?int
{
    foreach ($source as $row) {
        if (isset($row['url'], $row['id']) && $row['url'] === $url) {
            return (int)$row['id'];
        }
    }
    return null;
}

/**
 * Форматирует домен в зависимости от требуемого типа.
 *
 * @param string $proto Протокол ('http' или 'https')
 * @param string $domain Доменное имя (например, 'domain.com')
 * @param int $type 1 (обычный URL) или 2 (Yandex host_id)
 * @return string
 */
function protoDomain(string $proto, string $domain, int $type = 1)
{
    $cleanProto = cleanStr($proto);
    $cleanDomain = cleanStr($domain);

    if ($type === 2) {
        // Формат Яндекса: http:domain.com:80 или https:domain.com:443
        $port = ($cleanProto === 'https') ? '443' : '80';
        return "{$cleanProto}:{$cleanDomain}:{$port}";
    }

    // По умолчанию (тип 1) возвращаем классический URL
    return "{$cleanProto}://{$cleanDomain}";
}

/**
 * Оборачивает количество ошибок в HTML-теги в зависимости от значения.
 *
 * @param int|string $errors Значение ('n/a', 0 или число > 0)
 * @return string Отформатированная HTML-строка
 */
function formatErrorsCount(int|string $errors): string
{
    // Проверка на число
    if (is_numeric($errors)) {
        $count = (int)$errors;

        if ($count > 0) {
            return '<span class="badge bg-danger">Ошибок: ' . $count . '</span>';
        }
    }
    return '';
}


class SimpleDomainParser
{
    private $cacheFile;
    private $pslUrl = 'https://publicsuffix.org/list/public_suffix_list.dat';
    private $suffixes = array();

    /**
     * @param string|null $cacheDir Путь к папке кэша. Если null, берется константа _DATA_.
     */
    public function __construct($cacheDir = null)
    {
        // Используем переданный путь, либо константу _DATA_, либо текущую папку как fallback
        if ($cacheDir === null && defined('_DATA_')) {
            $cacheDir = _DATA_;
        } elseif ($cacheDir === null) {
            $cacheDir = __DIR__;
        }

        $this->cacheFile = rtrim($cacheDir, '/\\') . '/psl_cache.php';
        $this->load();
    }

    /**
     * Загружает кэш. Если файла нет — создает его.
     */
    private function load()
    {
        if (!file_exists($this->cacheFile)) {
            $this->updateCache();
        }
        $this->suffixes = include $this->cacheFile;
    }

    /**
     * Скачивает актуальный список PSL, атомарно сохраняет и обновляет права.
     */
    public function updateCache()
    {
        $data = file_get_contents($this->pslUrl);

        if (!$data) {
            throw new Exception("Не удалось скачать Public Suffix List по адресу {$this->pslUrl}");
        }

        $parsed = array();
        foreach (explode("\n", $data) as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '//') === 0 || strpos($line, '!') === 0 || strpos($line, '*') === 0) {
                continue;
            }
            $parsed[$line] = true;
        }

        $phpCode = "<?php\n// Сгенерировано автоматически\nreturn " . var_export($parsed, true) . ";\n";

        $tempFile = $this->cacheFile . '.' . uniqid('tmp_', true);

        // 1. Пишем во временный файл
        if (file_put_contents($tempFile, $phpCode) === false) {
            throw new Exception("Ошибка записи во временный файл кэша: {$tempFile}");
        }

        // 2. Атомарно переименовываем (заменяем старый файл новым без блокировок чтения)
        if (!rename($tempFile, $this->cacheFile)) {
            unlink($tempFile); // удаляем мусор, если rename не сработал
            throw new Exception("Ошибка переименования временного файла в {$this->cacheFile}");
        }

        // 3. Выставляем права 0666, чтобы и крон (root/user), и веб-сервер (www-data) могли его перезаписать
        chmod($this->cacheFile, 0666);
    }

    /**
     * Возвращает корневой домен.
     * Например: sub.site.com.ru -> site.com.ru
     */
    public function getRootDomain($domain)
    {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain);

        $suffix = '';
        for ($i = 0; $i < count($parts); $i++) {
            $testSuffix = implode('.', array_slice($parts, $i));
            if (isset($this->suffixes[$testSuffix])) {
                $suffix = $testSuffix;
                break;
            }
        }

        if ($suffix) {
            $suffixPartsCount = count(explode('.', $suffix));
            $rootDomainParts = array_slice($parts, -($suffixPartsCount + 1));
            return implode('.', $rootDomainParts);
        }

        return implode('.', array_slice($parts, -2));
    }
}

/**
 * Алгоритм справедливого распределения квоты (Water-Filling)
 */
function calculateFairLimit(int $currentSiteId, int $availableQuota, array $pendingCounts): int
{
    $pendingCounts = array_filter($pendingCounts, function($c) {
        return $c > 0;
    });

    if (!isset($pendingCounts[$currentSiteId])) {
        return 0;
    }

    $limits = array_fill_keys(array_keys($pendingCounts), 0);
    $remainingQuota = $availableQuota;

    while ($remainingQuota > 0 && count($pendingCounts) > 0) {
        $sitesCount = count($pendingCounts);
        $share = (int) floor($remainingQuota / $sitesCount);

        if ($share === 0) {
            foreach (array_keys($pendingCounts) as $id) {
                if ($remainingQuota > 0) {
                    $limits[$id] += 1;
                    $remainingQuota--;
                } else {
                    break;
                }
            }
            break;
        }

        foreach ($pendingCounts as $id => $count) {
            $give = min($count, $share);
            $limits[$id] += $give;
            $remainingQuota -= $give;
            $pendingCounts[$id] -= $give;

            if ($pendingCounts[$id] === 0) {
                unset($pendingCounts[$id]);
            }
        }
    }

    return $limits[$currentSiteId];
}
