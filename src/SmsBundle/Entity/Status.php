<?php

namespace SmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SmsBundle\Repository\StatusRepository")
 * @ORM\Table(name="message_statuses")
 */
class Status
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank()
     */
    protected $shortname;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     */
    protected $class;

     /**
     * @ORM\OneToMany(targetEntity="SmsBundle\Entity\Sms", mappedBy="status")
     */
    protected $messages;

    /**
     * Fetches the id for this status.
     *
     * @return int|null
     */
    public function getId():?int
    {
        return $this->id;
    }

    /**
     * Sets the id for this status.
     *
     * @param int $_id
     */
    public function setId(int $_id)
    {
        $this->id = $_id;
    }

    /**
     * Fetches the name for this status.
     *
     * @return string|null
     */
    public function getName():?string
    {
        return $this->name;
    }

    /**
     * Sets the name for this status.
     *
     * @param string $_name
     */
    public function setName(string $_name)
    {
        $this->name = $_name;
    }

    /**
     * Fetches the shortname for this status.
     *
     * @return string|null
     */
    public function getShortname():?string
    {
        return $this->shortname;
    }

    /**
     * Sets the shortname for this status.
     *
     * @param string $_shortname
     */
    public function setShortname(string $_shortname)
    {
        $this->shortname = $_shortname;
    }

    /**
     * Fetches the class for this status.
     *
     * @return string|null
     */
    public function getClass():?string
    {
        return $this->class;
    }

    /**
     * Sets the class for this status.
     *
     * @param string $_class
     */
    public function setClass(string $_class)
    {
        $this->class = $_class;
    }
}