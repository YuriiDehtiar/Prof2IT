<?php

namespace Controller;

class HomeController extends Controller {

   public function __construct()
   {
       parent::__construct();
   }

    public function index() {

       //$this->loadModel("Product");

      // $products = $this->product->getAll();

       $this->data("a", 10);

       $this->display("home");
    }
}