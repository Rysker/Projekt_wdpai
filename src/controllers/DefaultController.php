<?php

require_once __DIR__."/AppController.php";

class DefaultController extends AppController
{
    public function index()
    {
        $this->render("index");
    }

    public function login()
    {
        $this->render("login");
    }
}