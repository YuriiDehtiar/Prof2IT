<?php
// testLoginUser.php

// Увімкніть відображення помилок для налагодження
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключіть автозавантажувач та завантажувач змінних середовища
require_once 'ClassLoader.php';
require_once 'utils/loadEnv.php';

loadEnv(__DIR__ . '/.env');
ClassLoader::getInstance();

// Використовуйте необхідний простір імен
use Controller\TelegramController;

// Ініціалізуйте об'єкт TelegramController
$controller = new TelegramController();



// Встановіть тестові значення
$chatId = 123456789; // Замініть на ваш тестовий chat_id
$userId = 1289; // Замініть на ваш тестовий user_id

// Підготуйте тестові дані
$data = [
    'email' => 'testuser@example.com',   // Замініть на існуючий email з вашої бази даних
    'password' => 'testpassword'         // Замініть на відповідний пароль
];

// Зробіть метод loginUser публічним у TelegramController перед викликом
$controller->loginUser($chatId, $userId, $data);

echo "Метод loginUser виконано. Перевірте файл telegram_log.txt для отримання результатів.";
