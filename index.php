<?php
// index.php

// Включення відображення помилок (для розробки)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключаємо ClassLoader
require_once("ClassLoader.php");

// Підключаємо loadEnv.php
require_once("utils/loadEnv.php");

// Завантажуємо змінні середовища з .env
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Не вдалося завантажити .env файл: " . $e->getMessage());
}

// Ініціалізація автозавантаження класів
ClassLoader::getInstance();

// Ініціалізація додатку
\Controller\Application::getInstance()->init();
