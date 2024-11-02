<?php
// Controller/UserController.php

namespace Controller;

use Model\UserModel;
use Database\MysqlConnect;

class UserController extends Controller {
    private \mysqli $dbConnect; // Явне оголошення властивості з типом
    private UserModel $userModel;
    private MysqlConnect $mysqlConnect; // Властивість для MysqlConnect

    public function __construct() {
        parent::__construct(); // Виклик конструктора батьківського класу для ініціалізації $data

        $this->mysqlConnect = new MysqlConnect();
        $this->dbConnect = $this->mysqlConnect->getConnection();
        $this->userModel = $this->loadModel('UserModel', [$this->dbConnect]);
    }

    /**
     * Метод для відображення форми реєстрації.
     */
    public function showRegisterForm() {
        $this->display('register');
    }

    /**
     * Метод для обробки реєстрації користувача.
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($username && $email && $password) {
                // Перевірка, чи користувач з таким email вже існує
                if ($this->userModel->getUserByEmail($email)) {
                    $_SESSION['message'] = "Користувач з таким email вже існує.";
                } else {
                    // Хешування пароля
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    if ($this->userModel->registerUser($username, $email, $hashedPassword)) {
                        // Отримання користувача після реєстрації для автоматичного логіну
                        $user = $this->userModel->getUserByEmail($email);
                        if ($user) {
                            // Установка сесійних змінних для автоматичного логіну
                            $_SESSION['user_id'] = $user['idUser'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['message'] = "Реєстрація успішна. Ви автоматично увійшли в систему.";
                            header("Location: index.php?action=dashboard");
                            exit();
                        } else {
                            $_SESSION['message'] = "Реєстрація успішна, але виникла помилка при автоматичному вході.";
                            header("Location: index.php?action=loginForm");
                            exit();
                        }
                    } else {
                        $_SESSION['message'] = "Помилка при реєстрації.";
                    }
                }
            } else {
                $_SESSION['message'] = "Будь ласка, заповніть всі поля.";
            }

            header("Location: index.php?action=registerForm");
            exit();
        } else {
            echo "Невірний метод запиту.";
        }
    }

    /**
     * Метод для відображення форми логіну.
     */
    public function showLoginForm() {
        $this->display('login');
    }

    /**
     * Метод для обробки логіну користувача.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($email && $password) {
                $user = $this->userModel->getUserByEmail($email);
                if ($user && password_verify($password, $user['password'])) {
                    // Успішна аутентифікація
                    $_SESSION['user_id'] = $user['idUser'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: index.php?action=dashboard");
                    exit();
                } else {
                    $_SESSION['message'] = "Невірний email або пароль.";
                }
            } else {
                $_SESSION['message'] = "Будь ласка, заповніть всі поля.";
            }

            header("Location: index.php?action=loginForm");
            exit();
        } else {
            echo "Невірний метод запиту.";
        }
    }

    /**
     * Метод для логіну користувача через Telegram.
     * (Тут буде ваша логіка інтеграції з Telegram API)
     */
    public function loginWithTelegram() {
        // Реалізація логіки логіну через Telegram
    }

    /**
     * Метод для логінауту користувача.
     */
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?action=home"); // Перенаправлення на 'home'
        exit();
    }

    /**
     * Метод для відображення дашборду користувача.
     */
    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=loginForm");
            exit();
        }

        $this->data('username', $_SESSION['username']);
        $this->display('dashboard');
    }
}
