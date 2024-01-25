<?php

class Transaction implements JsonSerializable
{
    private $id_transaction;
    private $quantity;
    private $price;
    private $type;
    private $date;
    public function __construct($id_transaction, $quantity, $price, $type, $date)
    {
        $this->id_transaction = $id_transaction;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->type = $type;
        $this->date = $date;
    }

    public function getId() 
    {
        return $this->id_transaction;
    }

    public function setId($id) 
    {
        $this->id_transaction = $id;
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

    public function jsonSerialize() : mixed 
    {
        return 
        [
            'id_transaction' => $this->getId(),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'type' => $this->type,
            'date' => $this->date
        ];
    }
}
