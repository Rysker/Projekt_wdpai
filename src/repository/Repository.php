<?php

require_once __DIR__.'/../../Database.php';

class Repository 
{
    protected $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function getIdFromToken($token)
    {

        $stmt = $this->database->connect()->prepare('SELECT id_user from public.sessions natural join public.user where session_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['id_user'];
    }
}