<?php
declare(strict_types=1);

#API для фронтенда
if(substr($_SERVER['REQUEST_URI'], 0, 5) === '/api/'):

    header("Content-Type: application/json");

    // Основной роутер
    $endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    try {
        switch ($endpoint) {
            case '/api/sites/add':
                if ($method === 'POST') { verifyCsrf(); handleAddSites(); }
                break;
            case '/api/oauth':
                if ($method === 'GET') getOToken();
                break;
            case '/api/sites/start':
            case '/api/sites/stop':
            case '/api/sites/delete':
                if ($method === 'POST') { verifyCsrf(); handleSiteAction($endpoint); }
                break;
            case '/api/links/add':
                if ($method === 'POST') { verifyCsrf(); handleAddLinks(); }
                break;
            case '/api/keys/add':
            case '/api/keys/toggle-status':
            case '/api/keys/delete':
                if ($method === 'POST') { verifyCsrf(); handleKeyAction($endpoint); }
                break;
            case '/api/save-token':
                if ($method === 'POST') saveToken();
                break;
            case '/api/sites/mass-delete':
                if ($method === 'POST') { verifyCsrf(); handleMassDeleteSites(); }
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }


exit();
endif;

######## ВЫБОРКИ ДЛЯ ИНТЕРФЕЙСА ########


$APIS = $DBH->select('api_keys', '*');
$sites_cnt = $DBH->select('options', 'val',['name'=>'sites_cnt']);
$sites_cnt = $sites_cnt[0];

//пагинация
$currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, [
    'options' => [
        'default' => 1,
        'min_range' => 1
    ]
]);

// Вычисляем общее количество страниц
$totalPages = ceil($sites_cnt / ITMS_PP);

// Получаем данные для текущей страницы
$offset = ($currentPage - 1) * ITMS_PP;
$SITES = $DBH->select(
    "sites",
    // 1. Указываем связь: LEFT JOIN таблицы api_keys
    [
        "[>]api_keys" => ["api_key" => "id"]
    ],
    // 2. Перечисляем нужные столбцы (рекомендуется вместо '*')
    [
    "sites.id",
    "sites.yandex_host_id",
    "sites.domain",
    "sites.api_key",
    "sites.status",
    "sites.created_at",
    "sites.meta_data",
    "sites.locked_until",
    "sites.proto",
    "sites.last_used",
    "sites.linkz",
    "api_keys.value(api_key_value)" // Алиас: в массиве будет ключ 'api_key_value'
    ],
    // 3. Условия выборки
    [
        "LIMIT" =>[$offset, ITMS_PP],
        "ORDER" => ["sites.id" => "DESC"] // Обязательно указываем таблицу для id
    ]
);
