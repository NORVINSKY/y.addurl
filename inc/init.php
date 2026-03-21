<?php
declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    // HTTP Security Headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

ob_start();
mb_internal_encoding("UTF-8");

include _LIB_ . 'Medoo.php';
include _LIB_ . 'func.php';

$_LOG = [];

use Medoo\Medoo;
$DBH = new Medoo([
    'type' => 'sqlite',
    'database' => _DATA_ . 'db.sqlite',
    'command' => [
        'PRAGMA journal_mode=WAL;', // ВАЖНО для конкурентного доступа (cron + веб-интерфейс)
        'PRAGMA synchronous=NORMAL;'
    ],
    'option' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);

$DBH->exec('PRAGMA foreign_keys = ON;');

$db = new Medoo([
    'type' => 'sqlite',
    'database' => _DATA_ . 'log_db.sqlite',
    'option' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ],
    'command' => [
        'PRAGMA journal_mode=WAL;', // ВАЖНО для конкурентного доступа (cron + веб-интерфейс)
        'PRAGMA synchronous=NORMAL;'
    ]
]);