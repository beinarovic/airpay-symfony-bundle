<?php
namespace Beinarovic\AirpayBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Beinarovic\AirpayBundle\Entity\AirpayPayment;

class AirpayLogEvent extends Event
{
    /**
     * @var AirpayPayment
     */
    private $payment;
    
    /**
     * @var string
     */
    private $title;
    
    /**
     * @var integer
     */
    private $error_code;
    
    public function __construct($title, $errorCode = null, AirpayPayment $payment = null)
    {
        $this->title        = $title;
        $this->error_code   = $errorCode;
        $this->payment      = $payment;
    }

    /**
     * @return AirpayPayment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }
}