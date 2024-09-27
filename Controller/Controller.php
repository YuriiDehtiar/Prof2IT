<?php

namespace Controller;

class Controller {

    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function loadModel($title) {

    }

    protected function data($variable, $data) {
        $this->data[$variable] = $data;
    }

    protected function display($title) {
        foreach($this->data as $variable => $data) {
            $$variable = $data;
        }

        include_once __DIR__ . "/../View/" . $title . ".php";
    }
}