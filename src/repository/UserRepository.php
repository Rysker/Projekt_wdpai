<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/User.php';

class UserRepository extends Repository
{    
    public function addUser($email, $password): User
    {
        $stmt = $this->database->connect()->prepare('CALL add_user(?, ?)');
        $stmt->execute([$email, $password]);
        
        $stmt = $this->database->connect()->prepare('SELECT * FROM public.user WHERE email = ?');
        $stmt->execute([$email]);

        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        $hash_id = password_hash($userData['id_user'], CRYPT_BLOWFISH);
        return new User($hash_id, $userData['email'], $userData['password']);
    }

    public function hasBeenCreated($email): bool
    {
        $stmt = $this->database->connect()->prepare('SELECT COUNT(*) FROM public.user WHERE email = ?');
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function getUser($email): User
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM public.user WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return new User($user['id_user'], $user['email'], $user['password']);
    }

    public function getUsers()
    {
        $result = [];
        $stmt = $this->database->connect()->prepare('SELECT * FROM users_status');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) 
        {
             $result[] = new User(
                $user['id_user'],
                $user['email'],
                $user['status']
             );
         }

        return $result;
    }

    public function addSession($token, $id)
    {
        $stmt = $this->database->connect()->prepare(';
            INSERT INTO public.sessions (id_user, session_token)
            VALUES (?, ?)');
        $stmt->execute([$id, $token]);
    }

    public function removeSession()
    {
        $token = $_SESSION['token'];
        $userId = $this->getIdFromToken($token);
        $_SESSION = array();
        session_destroy();
        $stmt = $this->database->connect()->prepare('DELETE FROM public.sessions WHERE id_user = ?');
        $stmt->execute([$userId]);
    }

    public function updateUserStatus($id_user, $id_type)
    {
        $stmt = $this->database->connect()->prepare('UPDATE public.user SET id_status = ? where id_user = ?');
        $stmt->execute([$id_type, $id_user]);
    }

    public function isBlocked($email)
    {
        $stmt = $this->database->connect()->prepare('select status_name from public.user natural join public.status where email = ?');
        $stmt->execute([$email]);
        $status = $stmt->fetchColumn();
        return $status === 'Blocked';
    }

    public function getCurrency($id_user)
    {
        $stmt = $this->database->connect()->prepare('select currency_code from public.currency
        natural join public.user where id_user = ?;');
        $stmt->execute([$id_user]);
        return $stmt->fetchColumn();
    }

    public function getPriviliges()
    {
        $token = $_SESSION['token'];
        $userId = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('select role_name from public.role
            NATURAL JOIN public.user
            NATURAL JOIN public.usersroles
            where id_user = ?;');
        $stmt->execute([$userId]);
        $privileges = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['privileges' => $privileges]);
    }

    public function updateUserCurrency($id_currency)
    {
        $token = $_SESSION['token'];
        $userId = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('UPDATE public.user
            SET id_currency = ?
            WHERE id_user = ?;');
        $stmt->execute([$id_currency, $userId]);
    }
    
}