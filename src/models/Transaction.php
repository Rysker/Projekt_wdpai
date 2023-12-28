<?php

class Transaction
{
    private $quantity;
    private $price;
    private $type;
    private $date;
    public function __construct($quantity, $price, $type, $date)
    {
        $this->quantity = $quantity;
        $this->price = $price;
        $this->type = $type;
        $this->date = $date;
    }

    public function getQuantity() 
    {
        return $this->quantity;
    }

    public function setQuantity($quantity) 
    {
        $this->quantity = $quantity;
    }

    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }

    public function getType() 
    {
        return $this->type;
    }

    public function setMarket($type) 
    {
        $this->type = $type;
    }

    public function getDate() 
    {
        return $this->date;
    }

    public function setDate($date) 
    {
        $this->date = $date;
    }
}
