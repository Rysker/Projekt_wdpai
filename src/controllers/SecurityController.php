<?php

require_once "AppController.php";
require_once __DIR__ .'/../models/User.php';

class SecurityController extends AppController
{
    public function login()
    {  
        $user = new User('tmp@test.pl', 'password');

        if (!$this->isPost()) 
        {
            return $this->render('login');
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($user->getEmail() !== $email) {
            return $this->render('login', ['messages' => ['User with this email not exist!']]);
        }

        if ($user->getPassword() !== $password) {
            return $this->render('login', ['messages' => ['Wrong password!']]);
        }

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/projects");
    }

    public function register()
    {
        $user = new User('tmp@test.pl', 'password');
        if (!$this->isPost()) 
        {
            return $this->render('register');
        }

        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirmpassword'];

        if ($user->getEmail() == $email) 
        {
            return $this->render('register', ['messages' => ['User with this email already exists!']]);
        }

        if ($password != $confirm_password) {
            return $this->render('register', ['messages' => ['Passwords are different!']]);
        }

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/projects");

    }
}
