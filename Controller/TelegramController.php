<?php
// Controller/TelegramController.php

namespace Controller;

use Model\DinoModel;
use Model\UserModel;
use Database\MysqlConnect;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class TelegramController extends Controller {
    private DinoModel $dinoModel;
    private UserModel $userModel; 
    private \mysqli $dbConnect;
    private string $botToken;

    private $mysqlConnect;


    public function __construct() {
        // Завантаження Telegram Bot Token з .env
        if (!isset($_ENV['TELEGRAM_BOT_TOKEN'])) {
            throw new \Exception("TELEGRAM_BOT_TOKEN не встановлено у .env файлі.");
        }
        $this->botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
    
        // Налаштування з'єднання з базою даних
        try {
            $this->mysqlConnect = new MysqlConnect();
            $this->dbConnect = $this->mysqlConnect->getConnection();

            // Встановлення кодування з'єднання
            if (!$this->dbConnect->set_charset("utf8mb4")) {
                file_put_contents(__DIR__ . '/../telegram_log.txt', "Database Charset Error: " . $this->dbConnect->error . "\n", FILE_APPEND);
                throw new \Exception("Помилка встановлення кодування: " . $this->dbConnect->error);
            }
    
            // Увімкнення звітів про помилки для mysqli
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
            file_put_contents(__DIR__ . '/../telegram_log.txt', "Successfully connected to the database with utf8mb4 charset.\n", FILE_APPEND);
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../telegram_log.txt', "Database connection error: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }

        // Ініціалізація DinoModel
        try {
            //$this->dinoModel = new \Model\DinoModel($this->dbConnect);
            $this->dinoModel = $this->loadModel('DinoModel', [$this->dbConnect]);
            file_put_contents(__DIR__ . '/../telegram_log.txt', "DinoModel initialized successfully.\n", FILE_APPEND);
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../telegram_log.txt', "Error initializing DinoModel: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
        
        $this->userModel = new UserModel($this->dbConnect);
        // Ініціалізація UserModel
        try {
            $this->userModel = $this->loadModel('UserModel', [$this->dbConnect]);
            file_put_contents(__DIR__ . '/../telegram_log.txt', "UserModel initialized successfully.\n", FILE_APPEND);
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../telegram_log.txt', "Error initializing UserModel: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
    }

    public function webhook() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    
        // Отримання вхідних даних
        $update = json_decode(file_get_contents('php://input'), true);
    
        // Логування вхідних даних
        file_put_contents('telegram_log.txt', "Received Update: " . print_r($update, true) . "\n", FILE_APPEND);
    
        if (!$update) {
            $this->sendMessage("Невідома команда або формат повідомлення.");
            return;
        }
    
        // Перевірка, чи є повідомлення
        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $userId = $message['from']['id'];
            $text = trim($message['text'] ?? '');
    
            // Логування тексту повідомлення
            file_put_contents('telegram_log.txt', "Received Message: $text\n", FILE_APPEND);
    
            // Отримання стану користувача
            $stateData = $this->getUserState($userId);
            if ($stateData) {
                // Користувач знаходиться в певному стані
                $state = $stateData['state'];
                $data = json_decode($stateData['data'], true);
    
                // Обробка повідомлення на основі стану
                $this->handleUserState($chatId, $userId, $text, $state, $data);
            } else {
                // Обробка команд
                if (strpos($text, '/') === 0) {
                    $this->handleCommand($chatId, $userId, $text);
                } else {
                    $this->sendMessage("Будь ласка, використовуйте команди для взаємодії.", $chatId);
                }
            }
        } elseif (isset($update['callback_query'])) {
            // Обробка callback запитів
            $callback = $update['callback_query'];
            $chatId = $callback['message']['chat']['id'];
            $userId = $callback['from']['id'];
            $data = $callback['data'];
    
            // Логування callback даних
            file_put_contents('telegram_log.txt', "Received Callback Data: $data\n", FILE_APPEND);
    
            $this->handleCallbackQuery($chatId, $userId, $data);
        }
    }
    

    /**
     * Обробляє команди від користувача
     *
     * @param int $chatId ID чату користувача
     * @param string $text Текст команди
     */
    private function handleCommand(int $chatId, int $userId, string $text) {
        // Розбиття команди на частини
        $parts = explode(' ', $text);
        $command = strtolower($parts[0]);

        file_put_contents('telegram_log.txt', "Handling Command: $command\n", FILE_APPEND);

        switch ($command) {
            case '/start':
                $this->startCommand($chatId);
                break;
            case '/login':
                $this->loginCommand($chatId, $userId);
                break;
            case '/register':
                $this->registerCommand($chatId, $userId);
                break;
            case '/adddino':
                $this->addDinoCommand($chatId, $parts);
                break;
            case '/listdinos':
                $this->listDinosCommand($chatId);
                break;
            case '/test': 
                $this->testCommand($chatId);
                break;
            case '/deletedino':
                $this->deleteDino($chatId, $parts);
                break;
            // Додайте інші команди тут
            default:
                $this->sendMessage("Невідома команда. Спробуйте /start, /adddino або /listdinos.", $chatId);
                break;
        }
    }



    /**
     * Обробляє команду /start
     *
     * @param int $chatId ID чату користувача
     */
    private function startCommand(int $chatId) {
        $message = "Вітаємо у Dino Management Bot\\!\n\n" .
                "Будь ласка, оберіть опцію нижче:";
        
        // Створюємо інлайн клавіатуру з кнопками "Логін" та "Реєстрація"
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Логін', 'callback_data' => 'login'],
                    ['text' => 'Реєстрація', 'callback_data' => 'register']
                ]
            ]
        ];
        
        $encodedKeyboard = json_encode($keyboard);
        
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $postFields = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => $encodedKeyboard
        ];
        
        // Використання CURL для відправки POST-запиту
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Відключення перевірки SSL (для розробки)
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            file_put_contents(__DIR__ . '/../telegram_log.txt', "startCommand: CURL Error: " . curl_error($ch) . "\n", FILE_APPEND);
        }
        
        curl_close($ch);
        
        // Логування відповіді від Telegram API
        file_put_contents(__DIR__ . '/../telegram_log.txt', "startCommand: Sent Message Response: " . $response . "\n", FILE_APPEND);
    }






    /**
     * Обробляє команду /start2
     *
     * @param int $chatId ID чату користувача
     */
    private function startCommand2(int $chatId) {
        $message = "Вітаємо у Dino Management Bot\\!\n\n" .
                "Ви можете використовувати наступні команди:\n" .
                $this->escapeMarkdownV2("/adddino <ім'я> <тип> <колір>") . " \\- Додати нового динозавра\n" .
                $this->escapeMarkdownV2("/listdinos") . " \\- Показати список динозаврів";
        $this->sendMessage($message, $chatId);
    }


    /**
     * Обробляє команду /adddino
     *
     * @param int $chatId ID чату користувача
     * @param array $parts Частини команди
     */
    private function addDinoCommand(int $chatId, array $parts) {
        if (count($parts) !== 4) {
            $this->sendMessage("Невірний формат команди\\. Використовуйте:\n/adddino <ім'я\\> <тип\\> <колір\\>", $chatId);
            return;
        }

        // Отримання параметрів команди
        $name = $parts[1];
        $type = $parts[2];
        $color = $parts[3];

        // Логування параметрів
        file_put_contents('telegram_log.txt', "Adding Dino: Name=$name, Type=$type, Color=$color\n", FILE_APPEND);

        // Додавання динозавра через модель
        try {
            $this->dinoModel->insertDino($name, $type, $color);
            $this->sendMessage("Динозавр '$name' успішно доданий\\!", $chatId);
        } catch (\Exception $e) {
            file_put_contents('telegram_log.txt', "Error Adding Dino: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->sendMessage("Сталася помилка при додаванні динозавра: " . $e->getMessage(), $chatId);
        }
    }


    /**
     * Обробляє команду /deletedino
     *
     * @param int $chatId ID чату користувача
     * @param array $parts Частини команди
     */
    private function deleteDino(int $chatId, array $parts) {
        // Перевірка, чи передано ID динозавра
        if (count($parts) !== 2 || !is_numeric($parts[1])) {
            $this->sendMessage("Невірний формат команди\\. Використовуйте:\n/deletedino <ID динозавра\\>", $chatId);
            return;
        }

        // Отримання ID динозавра
        $idDino = (int)$parts[1];

        // Спроба видалення динозавра
        try {
            if ($this->dinoModel->deleteDino($idDino)) {
                $this->sendMessage("Динозавра з ID " . $this->escapeMarkdownV2($idDino) . " успішно видалено\\!", $chatId);
            } else {
                $this->sendMessage("Не вдалося знайти динозавра з ID " . $this->escapeMarkdownV2($idDino) . ".", $chatId);
            }
        } catch (\Exception $e) {
            file_put_contents('telegram_log.txt', "Error Deleting Dino: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->sendMessage("Сталася помилка при видаленні динозавра: " . $e->getMessage(), $chatId);
        }
    }

    /**
     * Обробляє команду /listdinos
     *
     * @param int $chatId ID чату користувача
     */
    private function listDinosCommand(int $chatId) {
        // Логування початку методу
        file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: Entering listDinosCommand.\n", FILE_APPEND);
        
        try {
            // Логування перед викликом getAllDinos
            file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: Calling getAllDinos.\n", FILE_APPEND);
            
            $dinos = $this->dinoModel->getAllDinos();
            
            // Логування після отримання даних
            file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: getAllDinos returned " . count($dinos) . " dinos.\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: After getAllDinos.\n", FILE_APPEND);
            
            if (empty($dinos)) {
                $this->sendMessage("Список динозаврів порожній.", $chatId);
                file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: Sent empty dinos message.\n", FILE_APPEND);
                return;
            }
    
            $message = "Список динозаврів:\n\n";
            foreach ($dinos as $dino) {
                $message .= "ID: " . $this->escapeMarkdownV2($dino['idDino']) . "\n" .
                            "Ім'я: " . $this->escapeMarkdownV2($dino['Dino_Name']) . "\n" .
                            "Тип: " . $this->escapeMarkdownV2($dino['Dino_Type']) . "\n" .
                            "Колір: " . $this->escapeMarkdownV2($dino['Color_Name']) . "\n\n";
            }
    
            // Логування перед відправкою повідомлення
            file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: Sending dinos list message.\n", FILE_APPEND);
            $this->sendMessage($message, $chatId);
            // Логування після відправки повідомлення
            file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: Sent dinos list message.\n", FILE_APPEND);
        } catch (\Exception $e) {
            // Логування помилки
            file_put_contents(__DIR__ . '/../telegram_log.txt', "TelegramController: Error Listing Dinos: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->sendMessage("Сталася помилка при отриманні списку динозаврів: " . $e->getMessage(), $chatId);
        }
    }
    

    /**
     * Відправляє повідомлення користувачу через Telegram API
     *
     * @param string $text Текст повідомлення
     * @param int|null $chatId ID чату користувача
     */
    private function sendMessage(string $text, int $chatId = null) {
        if ($chatId === null) {
            // Необхідно визначити, як отримати $chatId у такому випадку
            file_put_contents(__DIR__ . '/../telegram_log.txt', "sendMessage: Called with null chatId.\n", FILE_APPEND);
            return;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $postFields = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'MarkdownV2' // Використовуємо 'MarkdownV2'
        ];

        // Логування перед відправкою запиту
        file_put_contents(__DIR__ . '/../telegram_log.txt', "sendMessage: Sending POST request to Telegram API.\n", FILE_APPEND);

        // Використання CURL для відправки POST-запиту
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Відключення перевірки SSL (для розробки)
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            file_put_contents(__DIR__ . '/../telegram_log.txt', "sendMessage: CURL Error: " . curl_error($ch) . "\n", FILE_APPEND);
        }

        curl_close($ch);

        // Логування відповіді від Telegram API
        file_put_contents(__DIR__ . '/../telegram_log.txt', "sendMessage: Sent Message Response: " . $response . "\n", FILE_APPEND);
    }

    

    /**
     * Відправляє інлайн клавіатуру користувачу
     *
     * @param int $chatId ID чату користувача
     */
    private function sendInlineKeyboard(int $chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Додати Динозавра', 'callback_data' => 'add_dino'],
                    ['text' => 'Список Динозаврів', 'callback_data' => 'list_dinos']
                ]
            ]
        ];

        $encodedKeyboard = json_encode($keyboard);

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $postFields = [
            'chat_id' => $chatId,
            'text' => "Оберіть дію:",
            'reply_markup' => $encodedKeyboard
        ];

        // Використання CURL для відправки POST-запиту
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Відключення перевірки SSL (для розробки)
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            file_put_contents('telegram_log.txt', "CURL Error: " . curl_error($ch) . "\n", FILE_APPEND);
        }

        curl_close($ch);

        // Логування відповіді
        file_put_contents('telegram_log.txt', "Sent Inline Keyboard Response: " . $response . "\n", FILE_APPEND);
    }

    /**
     * Екранізує спеціальні символи для MarkdownV2.
     *
     * @param string $text Текст, який потрібно екранізувати.
     * @return string Екранізований текст.
     */
    private function escapeMarkdownV2(string $text): string {
        $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!','-'];
        foreach ($chars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }

    /**
     * Отримує поточний стан користувача.
     *
     * @param int $userId ID користувача в Telegram
     * @return array|null Повертає масив зі станом та даними або null, якщо запису немає
     */
    private function getUserState(int $userId) {
        $stmt = $this->dbConnect->prepare("SELECT `state`, `data` FROM `user_state` WHERE `user_id` = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stateData = $result->fetch_assoc();
        $stmt->close();
        return $stateData;
    }

    /**
     * Встановлює поточний стан користувача.
     *
     * @param int $userId ID користувача в Telegram
     * @param string $state Новий стан
     * @param array $data Додаткові дані
     */
    private function setUserState(int $userId, string $state, array $data = []) {
        $dataJson = json_encode($data);

        // Перевіряємо, чи запис вже існує
        $stmt = $this->dbConnect->prepare("SELECT 1 FROM `user_state` WHERE `user_id` = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            // Оновлюємо запис
            $stmt = $this->dbConnect->prepare("UPDATE `user_state` SET `state` = ?, `data` = ? WHERE `user_id` = ?");
            $stmt->bind_param("ssi", $state, $dataJson, $userId);
        } else {
            // Вставляємо новий запис
            $stmt = $this->dbConnect->prepare("INSERT INTO `user_state` (`user_id`, `state`, `data`) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userId, $state, $dataJson);
        }
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Видаляє стан користувача (коли процес завершено).
     *
     * @param int $userId ID користувача в Telegram
     */
    private function clearUserState(int $userId) {
        $stmt = $this->dbConnect->prepare("DELETE FROM `user_state` WHERE `user_id` = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        file_put_contents('telegram_log.txt', "clearUserState: " . print_r($userId, true) . "\n", FILE_APPEND);
    }

    /**
     * Обробляє повідомлення від користувача на основі його поточного стану.
     *
     * @param int $chatId ID чату
     * @param int $userId ID користувача
     * @param string $text Текст повідомлення
     * @param string $state Поточний стан користувача
     * @param array $data Додаткові дані стану
     */
    private function handleUserState(int $chatId, int $userId, string $text, string $state, array $data) {
        switch ($state) {
            case 'awaiting_register_username':
                // Зберігаємо ім'я користувача та запитуємо email
                $data['username'] = $text;
                $this->setUserState($userId, 'awaiting_register_email', $data);
                $this->sendMessage("Будь ласка, введіть ваш email\\.", $chatId);
                break;

            case 'awaiting_register_email':
                // Зберігаємо email та запитуємо пароль
                $data['email'] = $text;
                $this->setUserState($userId, 'awaiting_register_password', $data);
                $this->sendMessage("Будь ласка, введіть ваш пароль\\.", $chatId);
                break;

            case 'awaiting_register_password':
                // Зберігаємо пароль та реєструємо користувача
                $data['password'] = $text;
                $this->registerUser($chatId, $userId, $data);
                break;

            case 'awaiting_login_email':
                // Зберігаємо email та запитуємо пароль
                $data['email'] = $text;
                $this->setUserState($userId, 'awaiting_login_password', $data);
                $this->sendMessage("Будь ласка, введіть ваш пароль\\.", $chatId);
                break;

            case 'awaiting_login_password':
                // Зберігаємо пароль та виконуємо логін
                $data['password'] = $text;
                $this->loginUser($chatId, $userId, $data);
                break;

            default:
                $this->sendMessage("Невідома дія\\.", $chatId);
                $this->clearUserState($userId);
                break;
        }
    }



    /**
     * Починає процес реєстрації користувача.
     *
     * @param int $chatId ID чату
     * @param int $userId ID користувача
     */
    private function registerCommand(int $chatId, int $userId) {
        // Встановлюємо стан користувача як 'awaiting_register_username'
        $this->setUserState($userId, 'awaiting_register_username');
        $this->sendMessage("Будь ласка, введіть ваше ім'я користувача\\.", $chatId);
    }

    /**
     * Починає процес логіну користувача.
     *
     * @param int $chatId ID чату
     * @param int $userId ID користувача
     */
    private function loginCommand(int $chatId, int $userId) {
        // Встановлюємо стан користувача як 'awaiting_login_email'
        $this->setUserState($userId, 'awaiting_login_email');
        $this->sendMessage("Будь ласка, введіть ваш email\\.", $chatId);
    }




    /**
     * Реєструє користувача з наданими даними.
     *
     * @param int $chatId ID чату
     * @param int $userId ID користувача
     * @param array $data Дані користувача (username, email, password)
     */
    private function registerUser(int $chatId, int $userId, array $data) {
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];

        // Перевірка, чи користувач з таким email вже існує
        if ($this->userModel->getUserByEmail($email)) {
            $this->sendMessage("Користувач з таким email вже існує\\.", $chatId);
        } else {
            // Хешування пароля
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            if ($this->userModel->registerUser($username, $email, $hashedPassword)) {
                // Реєстрація успішна
                $this->sendMessage("Реєстрація успішна\\! Тепер ви можете увійти за допомогою команди /login\\.", $chatId);
            } else {
                $this->sendMessage("Сталася помилка при реєстрації\\. Спробуйте ще раз\\.", $chatId);
            }
        }

        // Очищаємо стан користувача
        $this->clearUserState($userId);
    }

    /**
     * Виконує логін користувача з наданими даними.
     *
     * @param int $chatId ID чату
     * @param int $userId ID користувача
     * @param array $data Дані користувача (email, password)
     */
    public function loginUser(int $chatId, int $userId, array $data) {
        $email = $data['email'];
        $password = $data['password'];
    
        file_put_contents('telegram_log.txt', "loginUser: Email = $email, Password = $password\n", FILE_APPEND);
    
        $user = $this->userModel->getUserByEmail($email);
        file_put_contents('telegram_log.txt', "loginUser: Email = $email, Password = $password\n", FILE_APPEND);
        if ($user) {
            file_put_contents('telegram_log.txt', "loginUser: User found in database.\n", FILE_APPEND);
        } else {
            file_put_contents('telegram_log.txt', "loginUser: User not found in database.\n", FILE_APPEND);
        }
    
        if ($user && password_verify($password, $user['password'])) {
            // Успішна аутентифікація
            $message = "Логін успішний\\! Тепер ви можете використовувати функції бота\\.\n\n".
                    $this->escapeMarkdownV2("/listdinos") . " \\- Показати список динозаврів\n".
                    $this->escapeMarkdownV2("/adddino <ім'я> <тип> <колір>") . " \\- Додати нового динозавра\n" .
                    $this->escapeMarkdownV2("/deletedino <ID динозавра>") . " \\- Для видалення динозавра";
            $this->sendMessage($message, $chatId);

            // Зберігаємо стан користувача як авторизованого
            $this->setUserState($userId, 'authorized', ['user_id' => $user['idUser'], 'username' => $user['username']]);
            file_put_contents('telegram_log.txt', "loginUser: User authenticated successfully.\n", FILE_APPEND);
        } else {
            $this->sendMessage("Невірний email або пароль\\.", $chatId);
            file_put_contents('telegram_log.txt', "loginUser: Authentication failed.\n", FILE_APPEND);
    
            // Очищаємо стан користувача при невдалій спробі логіну
            $this->clearUserState($userId);
        }
    }
    



    /**
     * Обробляє callback-запити від інлайн клавіатури
     *
     * @param int $chatId ID чату користувача
     * @param string $data Дані з callback_data
     */
    private function handleCallbackQuery(int $chatId, int $userId, string $data) {
        switch ($data) {
            case 'login':
                $this->loginCommand($chatId, $userId);
                break;
            case 'register':
                $this->registerCommand($chatId, $userId);
                break;
            default:
                $this->sendMessage("Невідома дія\\.", $chatId);
                break;
        }
    }




}
