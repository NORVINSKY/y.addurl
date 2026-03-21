<?php

#Пути
const _ROOT_ = __DIR__ . DIRECTORY_SEPARATOR; //Корень
const _LIB_ = _ROOT_ . 'inc' . DIRECTORY_SEPARATOR; //Подключаемые файлы
const _DATA_ = _ROOT_ . 'DATA' . DIRECTORY_SEPARATOR; //всякие данные
const _VIEW_ = _DATA_.'view'. DIRECTORY_SEPARATOR;; //шаб

const _APP_NAME_ = 'Y.AddURL';
const _OAUTH_INFO_URL_ = 'https://teletype.in/@norvinsky/oauth_guide';
const _PASSWD_ = 'A123456a'; //пароль для доступа к управлению
const ITMS_PP = 6; // Количество элементов на странице

const THREADS = 2; // Кол-во потоков, внутри есть более жесткое ограничение, не получится поставить больше 7
const ROTATION_DAYS = 14; // Через сколько дней можно переотправлять ссылки
