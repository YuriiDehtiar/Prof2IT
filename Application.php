<?php

class Application {

    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new Application();
        }

        return self::$instance;
    }

    public function init() {
        if(!isset($_GET["route"])) {
            exit();
        }

        $route = $_GET["route"];
        $data = explode("/", $route);

        $class = "\\Controller\\" . $data[0];

        $controller = new $class();
        echo $controller->{$data[1]}();
    }
}