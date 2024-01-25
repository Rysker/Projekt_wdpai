<?php

class Forex implements JsonSerializable
{
    protected $from;
    protected $to;
    protected $exchange_rate;

    public function __construct($from, $to, $exchange_rate) 
    {
        $this->from= $from;
        $this->to = $to;
        $this->exchange_rate = $exchange_rate;
    }

    public function getFrom() 
    {
        return $this->from;
    }


    public function getTo() 
    {
        return $this->to;
    }

    public function getRate() 
    {
        return $this->exchange_rate;
    }
    
    public function jsonSerialize() : mixed
    {
        return 
        [
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'rate' => $this->getRate()
        ];
    }
}
