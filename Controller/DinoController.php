<?php
// Controller/DinoController.php

namespace Controller;

use Model\DinoModel;
use Database\MysqlConnect;

class DinoController extends Controller {
    private DinoModel $dinoModel;
    private \mysqli $dbConnect; // Явно оголошено як mysqli
    private MysqlConnect $mysqlConnect; // Додана властивість для MysqlConnect

    public function __construct() {
        parent::__construct();
        
        // Створюємо об'єкт MysqlConnect
        $this->mysqlConnect = new MysqlConnect();
        
        // Отримуємо з'єднання з базою даних (mysqli)
        $this->dbConnect = $this->mysqlConnect->getConnection();
        
        // Завантажуємо модель DinoModel з передачею з'єднання з базою даних
        $this->dinoModel = $this->loadModel('DinoModel', [$this->dbConnect]);
    }

    /**
     * Метод для додавання динозавра з використанням назв.
     *
     * @param string $name Ім'я динозавра.
     * @param string $dinoTypeName Назва типу динозавра.
     * @param string $dinoColorName Назва кольору динозавра.
     */
    public function addDino($name, $dinoTypeName, $dinoColorName) {
        // Перевірка авторизації користувача
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['message'] = "Будь ласка, увійдіть у систему.";
            header("Location: index.php?action=loginForm");
            exit();
        }

        $this->dinoModel->insertDino($name, $dinoTypeName, $dinoColorName);
        $_SESSION['message'] = "Динозавр успішно доданий.";
    }

    /**
     * Метод для отримання та відображення списку динозаврів.
     */
    public function listDinos() {
        // Перевірка авторизації користувача
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['message'] = "Будь ласка, увійдіть у систему.";
            header("Location: index.php?action=loginForm");
            exit();
        }

        $dinos = $this->dinoModel->getAllDinos();
        $this->data('dinos', $dinos);
        $this->display('dinoList');
    }
}
