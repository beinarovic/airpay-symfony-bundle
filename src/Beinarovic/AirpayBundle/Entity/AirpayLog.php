<?php

namespace Beinarovic\AirpayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Beinarovic\AirpayBundle\Entity\AirpayPayment;

/**
 * AirpayLog
 *
 * @ORM\Table(name="beinarovic_airpay_log")
 * @ORM\Entity
 */
class AirpayLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="error_code", type="integer", nullable=true)
     */
    private $errorCode;

    /**
     * @var array
     *
     * @ORM\Column(name="request_post", type="array", nullable=true)
     */
    private $requestPost;

    /**
     * @var array
     *
     * @ORM\Column(name="request_get", type="array", nullable=true)
     */
    private $requestGet;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="logged_at", type="datetime")
     */
    private $loggedAt;

    /**
     * @var AirpayPayment
     *
     * @ORM\ManyToOne(targetEntity="AirpayPayment")
     * @ORM\JoinColumn(nullable=true)
     */
    private $payment;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return AirpayLog
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set errorCode
     *
     * @param integer $errorCode
     * @return AirpayLog
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Get errorCode
     *
     * @return integer 
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set requestPost
     *
     * @param array $requestPost
     * @return AirpayLog
     */
    public function setRequestPost($requestPost)
    {
        $this->requestPost = $requestPost;

        return $this;
    }

    /**
     * Get requestPost
     *
     * @return array 
     */
    public function getRequestPost()
    {
        return $this->requestPost;
    }

    /**
     * Set requestGet
     *
     * @param array $requestGet
     * @return AirpayLog
     */
    public function setRequestGet($requestGet)
    {
        $this->requestGet = $requestGet;

        return $this;
    }

    /**
     * Get requestGet
     *
     * @return array 
     */
    public function getRequestGet()
    {
        return $this->requestGet;
    }

    /**
     * Set loggedAt
     *
     * @param \DateTime $loggedAt
     * @return AirpayLog
     */
    public function setLoggedAt($loggedAt)
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    /**
     * Get loggedAt
     *
     * @return \DateTime 
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * Set payment
     *
     * @param \Beinarovic\AirpayBundle\Entity\AirpayPayment $payment
     * @return AirpayLog
     */
    public function setPayment(\Beinarovic\AirpayBundle\Entity\AirpayPayment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return \Beinarovic\AirpayBundle\Entity\AirpayPayment 
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
