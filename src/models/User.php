<?php

class User implements JsonSerializable
{
    private string $email;
    private string $password;
    private string $id;

    public function __construct(string $id, string $email, string $password, )
    {
        $this->email = $email;
        $this->password = $password;
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function jsonSerialize() : mixed 
    {
        return 
        [
            'id_user' => $this->getId(),
            'email' => $this->getEmail(),
            'status' => $this->getPassword()
        ];
    }

}