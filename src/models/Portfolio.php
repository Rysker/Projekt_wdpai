<?php

require_once __DIR__."/Investment.php";
class Portfolio
{
    private string $name;
    private array $investments = [];

    public function __construct($name , $investments) 
    {
        $this->name = $name;
        $this->investments = $investments;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getInvestments()
    {
        return $this->investments;
    }

    public function setInvestments($investments)
    {
        $this->investments = $investments;
    }
}
