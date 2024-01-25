<?php

class Asset implements JsonSerializable
{
    protected $id_asset;
    protected string $name;
    protected string $market;
    protected string $type;
    protected string $ticker;
    protected $price;
    protected $currency_sign;
    protected $currency;
    protected $modification;
    protected $observed;

    public function __construct($id_asset, $name, $market, $type, $ticker, $price, $currency, $currency_sign, $modification, $observed = false) 
    {
        $this->id_asset = $id_asset;
        $this->name = $name;
        $this->type = $type;
        $this->market = $market;
        $this->ticker = $ticker;
        $this->price = $price;
        $this->currency = $currency;
        $this->currency_sign = $currency_sign;
        $this->modification = $modification;
        $this->observed = $observed;
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

    public function getId()
    {
        return $this->id_asset;
    }

    public function setId($id_asset)
    {
        $this->id_asset = $id_asset;
    }

    public function getSign()
    {
        return $this->currency_sign;
    }

    public function getModification()
    {
        return $this->modification;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
    
    public function jsonSerialize() : mixed
    {
        return 
        [
            'id_asset' => $this->getId(),
            'name' => $this->name,
            'market' => $this->market,
            'type' => $this->type,
            'ticker' => $this->ticker,
            'price' => $this->price,
            'currency' => $this->currency,
            'sign' => $this->currency_sign,
            'modification' => $this->modification,
            'observed' => $this->observed
        ];
    }
}
