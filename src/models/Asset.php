<?php

class Asset
{
    protected string $name;
    protected string $market;
    protected string $type;
    protected string $ticker;
    protected $price;

    public function __construct($name, $market, $type, $ticker, $price) 
    {
        $this->name = $name;
        $this->type = $type;
        $this->market = $market;
        $this->ticker = $ticker;
        $this->price = $price;
    }

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }

    public function getTicker() 
    {
        return $this->ticker;
    }

    public function setTicker($ticker) 
    {
        $this->ticker = $ticker;
    }

    public function getMarket() 
    {
        return $this->market;
    }

    public function setMarket($market) 
    {
        $this->market = $market;
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

    public function setType($type)
    {
        $this->type = $type;
    }
}
