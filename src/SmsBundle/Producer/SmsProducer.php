<?php

namespace SmsBundle\Producer;

class SmsProducer
{
    private $producer;

    public function __construct($_producer)
    {
        $this->producer = $_producer;
    }

    public function public($_message)
    {
        $this->producer->publish(serialise($_message));
    }
}