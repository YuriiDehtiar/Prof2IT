<?php

namespace Database;

require __DIR__ . '/../utils/loadEnv.php'; // Оновлений шлях до функції

use mysqli;
use Exception;

class MysqlConnect {
    private $conn;

    public function __construct() {
        // Виклик функції для завантаження змінних з .env
        loadEnv(__DIR__ . '/../.env');

        // Параметри для підключення до бази даних
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? 3306;
        $database = $_ENV['DB_DATABASE'] ?? 'mydb';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        // Перевірка наявності обов'язкових параметрів
        if (!$host || !$port || !$database || !$username || $password === null) {
            throw new Exception("One or more database connection parameters are missing.");
        }

        // Підключення до MySQL
        $this->conn = new mysqli($host, $username, $password, $database, $port);

        // Перевірка з'єднання
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
        // echo "Connected successfully<br>";
    }

    /**
     * Повертає з'єднання з базою даних.
     *
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Закриває з'єднання з базою даних.
     */
    public function closeConnection() {
        if ($this->conn && !$this->conn->connect_errno) {  
            $this->conn->close();
            $this->conn = null;  
        }
    }

    /**
     * Закриває з'єднання автоматично при знищенні об'єкта.
     */
    public function __destruct() {
        $this->closeConnection();
    }
}
