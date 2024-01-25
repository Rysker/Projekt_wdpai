<?php

require_once 'Transaction.php';
require_once 'Asset.php';
class Investment extends Asset implements JsonSerializable
{
    private $quantity;
    //Value is quantity of each transaction * price of each transaction
    private $value;
    private array $transactions = [];
    public function __construct($id_asset, $name, $market, $type, $ticker, $price, $currency, $currency_sign, $modification, $quantity, $value, $transactions, $observed = false) 
    {
        parent::__construct($id_asset, $name, $market, $type, $ticker, $price, $currency, $currency_sign, $modification, $observed);
        $this->quantity = $quantity;
        $this->value = $value;
        $this->transactions = $transactions;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quanitty)
    {
        $this->quantity = $quanitty;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getTransactions()
    {
        return $this->transactions;
    }

    private function getProfit()
    {
        $current_value = (double)($this->getQuantity()) * (double)($this->getPrice());
        return (double)($current_value) - (double)($this->getValue());
    }

    public function jsonSerialize() : mixed
    {
        $transactionsArray = [];
        foreach ($this->transactions as $transaction) 
        {
            $transactionsArray[] = $transaction->jsonSerialize();
        }
        return 
        [
            'id_asset' => $this->getId(),
            'name' => $this->name,
            'market' => $this->market,
            'type' => $this->type,
            'ticker' => $this->ticker,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'currency' => $this->currency,
            'sign' => $this->currency_sign,
            'value' => number_format($this->getProfit(), 2),
            'transactions' => $transactionsArray,
            'profit' => number_format($this->getProfit(), 2) 
        ];
    }
}
