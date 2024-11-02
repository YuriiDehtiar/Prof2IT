<?php
// Controller/Application.php

namespace Controller;

use Controller\DinoController;
use Controller\HomeController;
use Controller\UserController;
use Controller\TelegramController;

class Application {

    private static $instance;

    private function __construct()
    {
    }

    /**
     * Отримує екземпляр Application. Реалізовано патерн Singleton.
     *
     * @return Application Повертає екземпляр Application.
     */
    public static function getInstance(): Application {
        if(self::$instance === null) {
            self::$instance = new Application();
        }

        return self::$instance;
    }

    /**
     * Ініціалізує додаток та обробляє маршрутизацію.
     */
    public function init(): void {
        // Початок сесії, якщо ще не почата
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Перевірка наявності параметра 'action'
        if(!isset($_GET["action"])) {
            // За замовчуванням показати головну сторінку
            $controller = new HomeController();
            $controller->index();
            return;
        }

        $action = $_GET["action"];

        // Маршрутизація на основі значення 'action'
        switch($action) {
            case 'addDino':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $name = trim($_POST['dinoName'] ?? '');
                    $type = trim($_POST['dinoType'] ?? '');
                    $color = trim($_POST['dinoColor'] ?? '');

                    // Перевірка наявності даних
                    if ($name && $type && $color) {
                        // Створюємо об'єкт контролера і викликаємо метод для додавання динозавра
                        $controller = new DinoController();
                        ob_start(); // Вимикаємо прямий вивід
                        $controller->addDino($name, $type, $color);
                        $message = ob_get_clean(); // Отримуємо повідомлення
                        $_SESSION['message'] = $message;
                    } else {
                        $_SESSION['message'] = "Будь ласка, заповніть всі поля.";
                    }

                    // Перенаправлення назад до форми для запобігання повторної відправки
                    header("Location: index.php?action=listDino");
                    exit();
                } else {
                    // Якщо метод запиту не POST
                    echo "Невірний метод запиту.";
                }
                break;
            case 'listDino':
                $controller = new DinoController();
                $controller->listDinos();
                break;
            
            // Маршрутизація для користувачів
            case 'registerForm':
                $controller = new UserController();
                $controller->showRegisterForm();
                break;
            case 'register':
                $controller = new UserController();
                $controller->register();
                break;
            case 'loginForm':
                $controller = new UserController();
                $controller->showLoginForm();
                break;
            case 'login':
                $controller = new UserController();
                $controller->login();
                break;
            case 'logout':
                $controller = new UserController();
                $controller->logout();
                break;
            case 'dashboard':
                $controller = new UserController();
                $controller->dashboard();
                break;

            case 'home':
                $controller = new HomeController();
                $controller->index();
                break;
                
            // Обробка Telegram webhook
            case 'telegramWebhook':
                $controller = new TelegramController();
                $controller->webhook();
                break;
            // Можна додати інші дії тут
            
            default:
                echo "Невідома дія.";
                break;
        }
    }
}
