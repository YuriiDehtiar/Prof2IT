<?php

class ClassLoader {

    private static $instance;

    /**
     * Приватний конструктор для реалізації патерну Singleton.
     */
    private function __construct()
    {
    }

    /**
     * Отримує екземпляр ClassLoader. Реалізовано патерн Singleton.
     *
     * @return ClassLoader Повертає екземпляр ClassLoader.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ClassLoader();
            spl_autoload_register([self::$instance, "load"]);
        }
        return self::$instance;
    }

    /**
     * Автозавантажувач класів.
     *
     * @param string $name Повна назва класу з простором імен.
     */
    public function load($name) {
        // Перетворюємо простір імен у шлях до файлу
        $path = __DIR__ . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $name) . ".php";
        
        if (file_exists($path)) {
            include_once $path;
        } else {
            // Логування або обробка помилки замість прямого виводу
            error_log("Файл для класу '$name' не знайдено за шляхом: $path");
        }
    }
    
}
