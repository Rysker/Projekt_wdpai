<?php
require_once "AppController.php";
require_once "AccessController.php";
require_once __DIR__ .'/../models/User.php';
require_once __DIR__ .'/../repository/UserRepository.php';
require_once __DIR__."/../repository/WatchlistRepository.php";
require_once __DIR__."/../repository/PortfolioRepository.php";
require_once __DIR__."/../repository/AssetRepository.php";

class SecurityController extends AppController
{
    public function login()
    {  
        if (!$this->isPost()) 
        {
            $this->render('login');
        }
        else
        {
            $email = $_POST['email'];
            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                return $this->render('login', ['messages' => ['Invalid e-mail format!']]);
            $password = $_POST['password'];
            $userRepository = new UserRepository();
            
            if (!$userRepository->hasBeenCreated($email)) 
                return $this->render('login', ['messages' => ['User does not exist!']]);

            $existingUser = $userRepository->getUser($email);
            if(!password_verify($password, $existingUser->getPassword()))
               return $this->render('login', ['messages' => ['Incorrect password!']]);

            if($userRepository->isBlocked($email))
            {
                return $this->render('login', ['messages' => ['Your account has been suspended!']]);
            }
            try 
            {
                $token = bin2hex(random_bytes(32));
                $time = $_SESSION['expire'] = time() + (10 * 60);
                $_SESSION['valid'] = true;
                $_SESSION['token'] = $token;
                $userRepository->addSession($token, $existingUser->getId(), $time);
                $access = new AccessController();
                $access->main();
            } 
            catch (PDOException $exception) 
            {
                return $this->render('login', ['messages' => ['Error during loging in!']]);
            }
        }
    }

    public function register()
    {
        if (!$this->isPost()) 
        {
            return $this->render('register');
        }
        else
        {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirmpassword'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
                return $this->render('register', ['messages' => ['Invalid email format!']]);
            
            $userRepository = new UserRepository();
            $existingUser = $userRepository->hasBeenCreated($email);

            if ($existingUser) 
                return $this->render('register', ['messages' => ['User with this email already exists!']]);

            if ($password !== $confirm_password) 
                return $this->render('register', ['messages' => ['Passwords are different!']]);

            else if (strlen($password) < 8) 
                return $this->render('register', ['messages' => ['Password must be at least 8 characters long']]);
            
            else if (!preg_match('/[A-Z]/', $password)) 
                return $this->render('register', ['messages' => ['Password must contain at least one uppercase letter']]);
            
            else if (!preg_match('/\d/', $password)) 
                return $this->render('register', ['messages' => ['Password must contain at least one number']]);
            
            else if (!preg_match('/[!@#$%^&*()]/', $password)) 
                return $this->render('register', ['messages' => ['Password must contain at least one special character']]);

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            try 
            {
                $userRepository->addUser($email, $hashedPassword);
                $this->render('login', ['messages' => ['Registration successful!']]);
            } 
            catch (PDOException $exception) 
            {
                error_log("Error downloading history: " . $exception->getMessage());
                return $this->render('register', ['messages' => ['Error during registration!']]);
            }
        }
    }
}
