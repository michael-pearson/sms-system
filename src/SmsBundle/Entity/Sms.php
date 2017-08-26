<?php

namespace SmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="messages")
 */
class Sms
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     */
    protected $number;

    /**
     * @ORM\Column(type="string", length=140)
     * @Assert\NotBlank()
     */
    protected $message;

    /**
     * Fetches the id for this SMS.
     *
     * @return int|null
     */
    public function getId():?int
    {
        return $this->id;
    }

    /**
     * Sets the id for this SMS.
     *
     * @param int $_id
     * @return void
     */
    public function setId(int $_id):void
    {
        $this->id = $_id;
    }

    /**
     * Fetches the mobile number for this SMS.
     *
     * @return string|null
     */
    public function getNumber():?string
    {
        return $this->number;
    }

    /**
     * Sets the mobile number for this SMS.
     *
     * @param string $_number
     * @return void
     */
    public function setNumber(string $_number):void
    {
        $this->number = $_number;
    }

    /**
     * Fetches the message for this SMS.
     *
     * @return string|null
     */
    public function getMessage():?string
    {
        return $this->message;
    }

    /**
     * Sets the message for this SMS.
     *
     * @param string $_message
     * @return void
     */
    public function setMessage(string $_message):void
    {
        $this->message = $_message;
    }
}