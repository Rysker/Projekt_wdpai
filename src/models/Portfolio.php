<?php

require_once 'Investment.php';
require_once 'Forex.php';
class Portfolio implements JsonSerializable
{
    private $id_portfolio;
    private string $name;
    private array $investments = [];
    private array $forex = [];
    private string $currency;

    public function __construct($id_portfolio, $name , $investments, $forex, $currency) 
    {
        $this->id_portfolio = $id_portfolio;
        $this->name = $name;
        $this->investments = $investments;
        $this->forex = $forex;
        $this->currency = $currency;
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

    public function getId()
    {
        return $this->id_portfolio;
    }

    public function setId($id_portfolio)
    {
        $this->id_portfolio = $id_portfolio;
    }

    public function getTotalValue()
    {
        $current_value = 0;
        foreach($this->investments as $investment)
        {
            $value = (double)($investment->getQuantity()) * (double)($investment->getPrice());
            if($this->currency != $investment->getCurrency())
                $value = $value * (double)($this->searchForex($investment->getCurrency()));
            $current_value = $current_value + $value;
        }
        return $current_value;
    }

    function searchForex($from)
    {
        foreach ($this->forex as $forex) 
        {
            if ($forex->getFrom() == $from) 
                return $forex->getRate();
        }
        return null;
    }

    public function jsonSerialize() : mixed
    {
        $investmentsArray = [];
        foreach ($this->investments as $investment) 
        {
            $investmentsArray[] = $investment->jsonSerialize();
        }

        return 
        [
            'id_portfolio' => $this->getId(),
            'name' => $this->name,
            'investments' => $investmentsArray,
            'total_value' => $this->getTotalValue(),
            'forex' => $this->forex,
            'currency' => $this->currency
        ];
    }
}
