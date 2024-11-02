<?php
// Controller/Controller.php

namespace Controller;

class Controller {

    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Завантажує модель за назвою.
     *
     * @param string $modelName Назва моделі без суфікса "Model".
     * @param array $params Параметри для конструктора моделі.
     * @return object Повертає екземпляр моделі.
     */
    public function loadModel($modelName, $params = []) {
        $modelFile = __DIR__ . '/../Model/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClass = "\\Model\\" . $modelName;
            if (class_exists($modelClass)) {
                return new $modelClass(...$params);
            } else {
                error_log("Модель класу $modelClass не існує.");
                throw new \Exception("Модель класу $modelClass не існує.");
            }
        } else {
            error_log("Файл моделі $modelFile не знайдено.");
            throw new \Exception("Файл моделі $modelFile не знайдено.");
        }
    }

    /**
     * Додає дані до масиву даних контролера.
     *
     * @param string $variable Ім'я змінної.
     * @param mixed $data Дані, які потрібно передати до виду.
     */
    protected function data($variable, $data) {
        $this->data[$variable] = $data;
    }

    /**
     * Відображає вказаний вид з переданими даними.
     *
     * @param string $viewName Назва виду без розширення ".php".
     */
    protected function display($viewName) {
        if (is_array($this->data) || is_object($this->data)) {
            foreach($this->data as $variable => $data) {
                $$variable = $data;
            }
        } else {
            // Обробка випадку, коли $data не є масивом або об'єктом
            echo "Немає даних для відображення.";
        }

        include_once __DIR__ . "/../View/" . $viewName . ".php";
    }
}
