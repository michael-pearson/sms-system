<?php

namespace SmsBundle\Entity;

use AppBundle\Entity\User;
use SmsBundle\Entity\Status;
use Doctrine\ORM\Mapping as ORM;
use SmsBundle\Validator\Constraints as SmsAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass="SmsBundle\Repository\SmsRepository")
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
     * @SmsAssert\IsValidUKMobile
     */
    protected $number;

    /**
     * @ORM\Column(type="string", length=140)
     * @Assert\Length
     * (
     *      min = 1,
     *      max = 140,
     *      minMessage = "The message must be at least {{ limit }} characters in length.",
     *      maxMessage = "The message cannot be longer than {{ limit }} characters in length."
     * )
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(name="users_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="SmsBundle\Entity\Status", inversedBy="messages")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    protected $created_at;

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
     */
    public function setId(int $_id)
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
     */
    public function setNumber(string $_number)
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
     */
    public function setMessage(string $_message)
    {
        $this->message = $_message;
    }

    /**
     * Fetches the User for this SMS.
     *
     * @return User|null
     */
    public function getUser():?User
    {
        return $this->user;
    }

    /**
     * Sets the user for this SMS.
     *
     * @param User $_user
     */
    public function setUser(User $_user)
    {
        $this->user = $_user;
    }

    /**
     * Returns the created time for this SMS.
     *
     * @return \DateTime|null
     */
    public function getCreated_at():?\DateTime
    {
        return $this->created_at;
    }

    /**
     * Fetches the Status for this SMS.
     *
     * @return Status|null
     */
    public function getStatus():?Status
    {
        return $this->status;
    }

    /**
     * Sets the Status for this SMS.
     *
     * @param Status $_status
     */
    public function setStatus(Status $_status)
    {
        $this->status = $_status;
    }

    /**
     * The constructor is only called on creation and no on hydration,
     * so we use it to set default column values.
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
    }
}