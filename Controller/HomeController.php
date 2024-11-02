<?php
// Controller/HomeController.php

namespace Controller;

use Model\DinoModel;
use Database\MysqlConnect;

class HomeController extends Controller {
    
    private DinoModel $dinoModel;
    private MysqlConnect $mysqlConnect;
    private \mysqli $dbConnect;

    public function __construct()
    {
        parent::__construct();
        $this->mysqlConnect = new MysqlConnect();
        $this->dbConnect = $this->mysqlConnect->getConnection();
        $this->dinoModel = $this->loadModel('DinoModel', [$this->dbConnect]);
    }

    /**
     * Метод для відображення головної сторінки.
     */
    public function index() {
        if (isset($_SESSION['user_id'])) {
            // Якщо користувач аутентифікований, перенаправляємо на дашборд
            header("Location: index.php?action=dashboard");
            exit();
        } else {
            // Якщо не аутентифікований, показуємо головну сторінку
            $this->data("message", "Ласкаво просимо до Dino Management System!");
            $this->display("home");
        }
    }
}
