<?php
// scripts/setWebhook.php

// Включення відображення помилок (для відлагодження)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключаємо ClassLoader
require("../ClassLoader.php");

// Підключаємо loadEnv.php
require("../utils/loadEnv.php");

// Завантажуємо змінні середовища з .env
try {
    loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    die("Не вдалося завантажити .env файл: " . $e->getMessage());
}

// Отримання Telegram Bot Token з середовища
if (!isset($_ENV['TELEGRAM_BOT_TOKEN'])) {
    die("TELEGRAM_BOT_TOKEN не встановлено у .env файлі.");
}
$token = $_ENV['TELEGRAM_BOT_TOKEN'];

// Отримання URL для вебхука
$webhook_url = 'https://7e1e-178-158-193-51.ngrok-free.app/my_mvc_project/index.php?action=telegramWebhook';

// Використання аргументів командного рядка для гнучкості
if (isset($argv[1])) {
    $webhook_url = $argv[1];
}

$url = "https://api.telegram.org/bot$token/setWebhook?url=" . urlencode($webhook_url);

// Виконання CURL запиту
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Відключення перевірки SSL (для розробки)
$response = curl_exec($ch);

// Перевірка наявності помилок
if (curl_errno($ch)) {
    echo 'CURL Error: ' . curl_error($ch);
}

curl_close($ch);

// Вивід відповіді
echo $response;
?>
