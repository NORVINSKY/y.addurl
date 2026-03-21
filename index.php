<?php
declare(strict_types=1);

// Костыль для запуска обработчика задач через cli
// команда запуска - /usr/bin/php путь/до/сайта/index.php cron=queue >> /путь/до/cron.log 2>&1
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    // Парсим первый аргумент (cron=queue) и прокидываем его в массив $_GET
    parse_str($argv[1], $_GET);
}


require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require _LIB_ . 'init.php';

// Выполнение задач cron (только через CLI)
if (php_sapi_name() === 'cli' && !empty($_GET['cron'])) {
    if ($_GET['cron'] == 'queue') {
        require _LIB_ . 'cron.inc.php';
        if (ob_get_level() > 0)
            ob_end_flush();
        die;
    } elseif ($_GET['cron'] == 'update_psl') {
        try {
            $parser = new SimpleDomainParser(_DATA_);
            $parser->updateCache();
            echo "PSL cache updated successfully.\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        if (ob_get_level() > 0)
            ob_end_flush();
        die;
    }
}

// Сессия и CSRF только для веб-запросов
if (php_sapi_name() !== 'cli') {
    session_start();

    if (!isset($_SESSION['auth'])) {
        $_SESSION['auth'] = false;
    }

    // CSRF-токен: генерируем один раз за сессию
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

//разлогин
if (isset($_GET['logout'])) {
    $_SESSION['auth'] = false;
    header('Location: /');
    die;
}

//автризация
if (!isAuth() && $_POST && isset($_POST['auth'])) {
    $_SESSION['auth'] = cleanStr($_POST['auth']) === _PASSWD_;
}

if (isAuth()) {

    if (!empty($_GET['logs']) && $_GET['logs'] == 'get') {
        require _LIB_ . 'logs.inc.php';
        if (ob_get_level() > 0)
            ob_end_flush();
        die;
    } else {
        require _LIB_ . 'main.inc.php';
        require _VIEW_ . 'head.tpl.php';
        require _VIEW_ . 'main.php';
    }


} else {
    require _VIEW_ . 'head.tpl.php';
    require _VIEW_ . 'auth.php';
}

require _VIEW_ . 'footer.tpl.php';


if (ob_get_level() > 0)
    ob_end_flush();