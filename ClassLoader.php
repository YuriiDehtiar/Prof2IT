<?php

class ClassLoader {

    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new ClassLoader();
        }

        spl_autoload_register([self::$instance, "load"]);
    }

    public function load($name) {
        $path = __DIR__ . "/" . str_replace("\\", "/", $name) . ".php";
        if (file_exists($path)) {
            include_once $path;
        } else {
            echo "Файл для класу $name не знайдено за шляхом: $path";
        }
    }
    
}