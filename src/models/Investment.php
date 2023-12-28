<?php

require_once __DIR__."/Transaction.php";
class Investment extends Asset
{
    private $quantity;
    private $value;
    private array $transactions = [];
    public function __construct($name, $market, $type, $ticker, $price, $quantity, $value, $transactions) 
    {
        parent::__construct($name, $market, $type, $ticker, $price);
        $this->quantity = $quantity;
        $this->value = $value;
        $this->transactions = $transactions;
    }

}
