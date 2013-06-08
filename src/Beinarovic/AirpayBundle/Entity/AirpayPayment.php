<?php

namespace Beinarovic\AirpayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AirpayPayment
 *
 * @ORM\Table(name="beinarovic_airpay_payment")
 * @ORM\Entity(repositoryClass="Beinarovic\AirpayBundle\Repository\AirpayPaymentRepository")
 */
class AirpayPayment
{
	const STATUS_NEW            = -2; // Custom, if no status retrieved
	const STATUS_EXPIRED        = -1; // transaction expired with no try for the payment, means that the client just closed the AirPay window without proceeding the payment
	const STATUS_WINDOW_OPEN    = 0; // transaction is created by payment() or payment_req() methods of the AirPay class, means that the AirPay window opened
	const STATUS_SUCCESS        = 1; // transaction is successfull, means that the merchant can provide the service or product to the client
	const STATUS_IN_PROCESS     = 2; // transaction is pending, means that the client have tried to pay and the payment system is still processing the payment
	const STATUS_REJECTED       = 3; // transaction is rejected, means that the client have no funds or cancelled the payment etc
	const STATUS_EXPIRED        = 4; // transaction is expired, means that it didn't receive any status after the pending for a long time (36 hours). consider it as rejected.
	const STATUS_REFUND         = 5; // transaction is refunded, means that the client received the funds back and Your account was charged for this amount
    
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
     * @ORM\Column(name="merchant_id", type="string", length=255)
     */
    private $merchantId;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255, nullable=true)
     */
    private $transactionId;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", nullable=true)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=6, nullable=true)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice", type="string", length=255, unique=true)
     */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=5, nullable=true)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="cl_fname", type="string", length=30, nullable=true)
     */
    private $clFname;

    /**
     * @var string
     *
     * @ORM\Column(name="cl_lname", type="string", length=30, nullable=true)
     */
    private $clLname;

    /**
     * @var string
     *
     * @ORM\Column(name="cl_email", type="string", length=40, nullable=true)
     */
    private $clEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="cl_country", type="string", length=20, nullable=true)
     */
    private $clCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="cl_city", type="string", length=20, nullable=true)
     */
    private $clCity;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="psys", type="string", length=255, nullable=true)
     */
    private $psys;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="custom", type="string", length=255, nullable=true)
     */
    private $custom;

    /**
     * @var createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;
    
    public function __construct($merchantId) {
        $this->merchantId = $merchantId;
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_NEW;
    }

    /**
     * Returns all form data in a array.
     * 
     * @return array
     */
    public function toArray() 
    {
        return array(
            'merchant_id'	=> $this->merchantId,
            'amount'        => $this->amount,
            'currency'      => $this->currency,
            'invoice'       => $this->invoice,
            'language'      => $this->language,
            'cl_fname'      => $this->clFname,
            'cl_lname'      => $this->clLname,
            'cl_email'      => $this->clEmail,
            'cl_country'	=> $this->clCountry,
            'cl_city'       => $this->clCity,
            'description'	=> $this->description,
            'psys'          => $this->psys,
            'hash'          => $this->hash
        );
    }
    
    /**
     * Binds server response array to entity.
     * 
     * @var array $data
     */
    public function bindResponse($resp) 
    {
        $this->transactionId = isset($resp['transaction_id']) ? $resp['transaction_id'] : $this->transactionId;
        $this->amount = isset($resp['amount']) ? $resp['amount'] : $this->amount;
        $this->currency = isset($resp['currency']) ? $resp['curency'] : $this->currency;
        $this->description = isset($resp['description']) ? $resp['description'] : $this->description;
        $this->status = isset($resp['status_id']) ? $resp['status_id'] : $this->status;
        $this->hash = isset($resp['hash']) ? $resp['hash'] : $this->hash;
    }
    
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
     * Set merchantId
     *
     * @param string $merchantId
     * @return AirpayPayment
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * Get merchantId
     *
     * @return string 
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return AirpayPayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return AirpayPayment
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set invoice
     *
     * @param string $invoice
     * @return AirpayPayment
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return string 
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set clFname
     *
     * @param string $clFname
     * @return AirpayPayment
     */
    public function setClFname($clFname)
    {
        $this->clFname = $clFname;

        return $this;
    }

    /**
     * Get clFname
     *
     * @return string 
     */
    public function getClFname()
    {
        return $this->clFname;
    }

    /**
     * Set clLname
     *
     * @param string $clLname
     * @return AirpayPayment
     */
    public function setClLname($clLname)
    {
        $this->clLname = $clLname;

        return $this;
    }

    /**
     * Get clLname
     *
     * @return string 
     */
    public function getClLname()
    {
        return $this->clLname;
    }

    /**
     * Set clEmail
     *
     * @param string $clEmail
     * @return AirpayPayment
     */
    public function setClEmail($clEmail)
    {
        $this->clEmail = $clEmail;

        return $this;
    }

    /**
     * Get clEmail
     *
     * @return string 
     */
    public function getClEmail()
    {
        return $this->clEmail;
    }

    /**
     * Set clCountry
     *
     * @param string $clCountry
     * @return AirpayPayment
     */
    public function setClCountry($clCountry)
    {
        $this->clCountry = $clCountry;

        return $this;
    }

    /**
     * Get clCountry
     *
     * @return string 
     */
    public function getClCountry()
    {
        return $this->clCountry;
    }

    /**
     * Set clCity
     *
     * @param string $clCity
     * @return AirpayPayment
     */
    public function setClCity($clCity)
    {
        $this->clCity = $clCity;

        return $this;
    }

    /**
     * Get clCity
     *
     * @return string 
     */
    public function getClCity()
    {
        return $this->clCity;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return AirpayPayment
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set psys
     *
     * @param string $psys
     * @return AirpayPayment
     */
    public function setPsys($psys)
    {
        $this->psys = $psys;

        return $this;
    }

    /**
     * Get psys
     *
     * @return string 
     */
    public function getPsys()
    {
        return $this->psys;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return AirpayPayment
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return AirpayPayment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return AirpayPayment
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return AirpayPayment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return AirpayPayment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set custom
     *
     * @param string $custom
     * @return AirpayPayment
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * Get custom
     *
     * @return string 
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Set transactionId
     *
     * @param string $transactionId
     * @return AirpayPayment
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Get transactionId
     *
     * @return string 
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
