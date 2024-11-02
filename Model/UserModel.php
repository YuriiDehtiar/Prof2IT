<?php
// Model/UserModel.php

namespace Model;

use mysqli;
use Exception;

class UserModel {
    private mysqli $conn;

    public function __construct(mysqli $db) {
        $this->conn = $db;
    }

    /**
     * Реєструє нового користувача.
     *
     * @param string $username Ім'я користувача.
     * @param string $email Email користувача.
     * @param string $password Хешований пароль.
     * @return bool Успішність операції.
     */
    public function registerUser($username, $email, $password): bool {
        $stmt = $this->conn->prepare("INSERT INTO User (username, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Помилка підготовки запиту: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sss", $username, $email, $password);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Помилка виконання запиту: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }

    /**
     * Отримує користувача за email.
     *
     * @param string $email Email користувача.
     * @return array|null Дані користувача або null, якщо не знайдено.
     */
    public function getUserByEmail($email): ?array {
        file_put_contents('telegram_log.txt', "getUserByEmail.\n", FILE_APPEND);
        $stmt = $this->conn->prepare("SELECT * FROM User WHERE email = ?");
        if (!$stmt) {
            error_log("Помилка підготовки запиту: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ? $user : null;
    }

    /**
     * Отримує користувача за ID.
     *
     * @param int $idUser ID користувача.
     * @return array|null Дані користувача або null, якщо не знайдено.
     */
    public function getUserById($idUser): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM User WHERE idUser = ?");
        if (!$stmt) {
            error_log("Помилка підготовки запиту: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $idUser);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ? $user : null;
    }

    /**
     * Оновлює пароль користувача.
     *
     * @param int $idUser ID користувача.
     * @param string $newPassword Новий хешований пароль.
     * @return bool Успішність операції.
     */
    public function updatePassword($idUser, $newPassword): bool {
        $stmt = $this->conn->prepare("UPDATE User SET password = ? WHERE idUser = ?");
        if (!$stmt) {
            error_log("Помилка підготовки запиту: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $newPassword, $idUser);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Помилка виконання запиту: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }
}
