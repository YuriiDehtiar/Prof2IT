<?php
// testDinos.php

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

// Використання простору імен
use Database\MysqlConnect;
use Model\DinoModel;

// Спроба підключитися до бази даних
try {
    $mysqlConnect = new MysqlConnect();
    echo "<h2>Підключення до бази даних успішне.</h2>";
} catch (Exception $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}

// Ініціалізація моделі DinoModel
try {
    $dinoModel = new DinoModel($mysqlConnect->getConnection());
} catch (Exception $e) {
    die("Помилка ініціалізації DinoModel: " . $e->getMessage());
}

// Отримання всіх динозаврів
try {
    $dinos = $dinoModel->getAllDinos();
    echo "<h2>Список Динозаврів:</h2>";
    if (empty($dinos)) {
        echo "<p>Список динозаврів порожній.</p>";
    } else {
        echo "<ul>";
        foreach ($dinos as $dino) {
            echo "<li>";
            echo "ID: " . htmlspecialchars($dino['idDino']) . "<br>";
            echo "Ім'я: " . htmlspecialchars($dino['Dino_Name']) . "<br>";
            echo "Тип: " . htmlspecialchars($dino['Dino_Type']) . "<br>";
            echo "Колір: " . htmlspecialchars($dino['Color_Name']);
            echo "</li><br>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p>Сталася помилка при отриманні списку динозаврів: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
