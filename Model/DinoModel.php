<?php

namespace Model;

use mysqli;

class DinoModel {
    private $conn;

    /**
     * Конструктор класу DinoModel.
     *
     * @param mysqli $db З'єднання з базою даних.
     */
    public function __construct(mysqli $db) {
        $this->conn = $db;
    }

    /**
     * Метод для пошуку ID типу динозавра за назвою.
     *
     * @param string $typeName Назва типу динозавра.
     * @return int|null ID типу динозавра або null у випадку помилки.
     */
    public function getDinoTypeIdByName($typeName) {
        $stmt = $this->conn->prepare("SELECT idDinoType FROM DinoType WHERE Dino_Type = ?");
        if (!$stmt) {
            die("Помилка підготовки запиту: " . $this->conn->error);
        }
        $stmt->bind_param("s", $typeName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return (int)$row['idDinoType'];
        } else {
            // Додаємо новий тип, якщо не знайдено
            $stmt->close();
            $insertStmt = $this->conn->prepare("INSERT INTO DinoType (Dino_Type) VALUES (?)");
            if (!$insertStmt) {
                die("Помилка підготовки запиту для додавання типу: " . $this->conn->error);
            }
            $insertStmt->bind_param("s", $typeName);
            if ($insertStmt->execute()) {
                $newId = $insertStmt->insert_id;
                $insertStmt->close();
                return (int)$newId;
            } else {
                $insertStmt->close();
                return null; // Не вдалося додати
            }
        }
    }

    /**
     * Метод для пошуку ID кольору динозавра за назвою.
     *
     * @param string $colorName Назва кольору динозавра.
     * @return int|null ID кольору динозавра або null у випадку помилки.
     */
    public function getDinoColorIdByName($colorName) {
        $stmt = $this->conn->prepare("SELECT idDinoColor FROM DinoColor WHERE Color_Name = ?");
        if (!$stmt) {
            die("Помилка підготовки запиту: " . $this->conn->error);
        }
        $stmt->bind_param("s", $colorName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return (int)$row['idDinoColor'];
        } else {
            // Додаємо новий колір, якщо не знайдено
            $stmt->close();
            $insertStmt = $this->conn->prepare("INSERT INTO DinoColor (Color_Name) VALUES (?)");
            if (!$insertStmt) {
                die("Помилка підготовки запиту для додавання кольору: " . $this->conn->error);
            }
            $insertStmt->bind_param("s", $colorName);
            if ($insertStmt->execute()) {
                $newId = $insertStmt->insert_id;
                $insertStmt->close();
                return (int)$newId;
            } else {
                $insertStmt->close();
                return null; // Не вдалося додати
            }
        }
    }

    /**
     * Метод для додавання динозавра за назвами типу і кольору.
     *
     * @param string $name Ім'я динозавра.
     * @param string $dinoTypeName Назва типу динозавра.
     * @param string $dinoColorName Назва кольору динозавра.
     * @throws \Exception Якщо виникла помилка при додаванні динозавра.
     */
    public function insertDino($name, $dinoTypeName, $dinoColorName) {
        // Отримуємо або додаємо ID типу динозавра
        $dinoTypeId = $this->getDinoTypeIdByName($dinoTypeName);
        if (!$dinoTypeId) {
            throw new \Exception("Помилка: тип динозавра не знайдено або не вдалося додати.");
        }

        // Отримуємо або додаємо ID кольору динозавра
        $dinoColorId = $this->getDinoColorIdByName($dinoColorName);
        if (!$dinoColorId) {
            throw new \Exception("Помилка: колір динозавра не знайдено або не вдалося додати.");
        }

        // Вставка даних у таблицю Dino
        $stmt = $this->conn->prepare("INSERT INTO Dino (Dino_Name, Dino_Type_ID, DinoColor_ID) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new \Exception("Помилка підготовки запиту для додавання динозавра: " . $this->conn->error);
        }
        $stmt->bind_param("sii", $name, $dinoTypeId, $dinoColorId);

        if (!$stmt->execute()) {
            throw new \Exception("Помилка при додаванні динозавра: " . $stmt->error);
        }

        $stmt->close();
    }




    /**
     * Метод для отримання всіх динозаврів.
     *
     * @return array Список динозаврів.
     */
    public function getAllDinos(): array {
        // Логування початку методу
        file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Starting getAllDinos.\n", FILE_APPEND);
    
        $query = '
            SELECT Dino.idDino, Dino.Dino_Name, DinoType.Dino_Type, DinoColor.Color_Name
            FROM Dino
            LEFT JOIN DinoType ON Dino.Dino_Type_ID = DinoType.idDinoType
            LEFT JOIN DinoColor ON Dino.DinoColor_ID = DinoColor.idDinoColor
        ';
    
        // Логування перед виконанням запиту
        file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Preparing to execute query.\n", FILE_APPEND);
    
        try {
            $result = $this->conn->query($query);
            
            if (!$result) {
                // Логування помилки запиту
                $error = $this->conn->error;
                file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Query Error: " . $error . "\n", FILE_APPEND);
                throw new \Exception("Помилка виконання запиту: " . $error);
            }
    
            // Логування після успішного виконання запиту
            file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Query executed successfully.\n", FILE_APPEND);
    
            $dinos = [];
            while ($row = $result->fetch_assoc()) {
                $dinos[] = $row;
                // Логування кожного отриманого запису
                file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Fetched Dino: " . print_r($row, true) . "\n", FILE_APPEND);
            }
    
            // Логування завершення циклу
            file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Retrieved " . count($dinos) . " dinos.\n", FILE_APPEND);
    
            return $dinos;
        } catch (\Exception $e) {
            // Логування винятку
            file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel: Exception caught: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e; // Повторно кидаємо виняток для обробки в контролері
        }
    }
    


    



  /**
     * Метод для оновлення інформації про динозавра.
     *
     * @param int $idDino ID динозавра.
     * @param string $name Нове ім'я динозавра.
     * @param string $dinoTypeName Нова назва типу динозавра.
     * @param string $dinoColorName Нова назва кольору динозавра.
     * @return bool Успішність операції.
     */
    public function updateDino($idDino, $name, $dinoTypeName, $dinoColorName) {
        $dinoTypeId = $this->getDinoTypeIdByName($dinoTypeName);
        if (!$dinoTypeId) {
            return false;
        }

        $dinoColorId = $this->getDinoColorIdByName($dinoColorName);
        if (!$dinoColorId) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE Dino SET Dino_Name = ?, Dino_Type_ID = ?, DinoColor_ID = ? WHERE idDino = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("siii", $name, $dinoTypeId, $dinoColorId, $idDino);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Метод для видалення динозавра за ID.
     *
     * @param int $idDino ID динозавра.
     * @return bool Успішність операції.
     */
    public function deleteDino($idDino) {
        $stmt = $this->conn->prepare("DELETE FROM Dino WHERE idDino = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idDino);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}