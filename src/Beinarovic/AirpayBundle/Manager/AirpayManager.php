<?php
namespace Beinarovic\AirpayBundle\Manager;

use Symfony\Component\DependencyInjection as DI;
use Beinarovic\AirpayBundle\Entity\AirpayPayment;
use Beinarovic\AirpayBundle\Repository\AirpayPaymentRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Beinarovic\AirpayBundle\Form\AirpayPaymentType;
use Symfony\Component\HttpFoundation\Request;
use Beinarovic\AirpayBundle\Errors\AirpayError;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Beinarovic\AirpayBundle\Event\AirpayLogEvent;

class AirpayManager
{
    /**
     * @var DI\ContainerInterface
     */
    private $_sc = null;
    
    /**
     * @var EntityManager
     */
    private $_em = null;
    
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher = null;
    
    /**
     * @var Request
     */
    private $request = null;
    
    /**
     * @var AirpayPaymentRepository
     */
    private $_pr = null;
    
    /**
     * @var FormFactoryInterface
     */
    private $_ff = null;
    
    /**
     * @var AirpayPayment
     */
    private $payment = null;
    
    /**
     * @var array $errors
     */
    private $errors = array();
    
    /**
     * Configuration variables from dependency injection.
     */
	private $url        = null;
	private $is_sandbox = true;
	private $merchant   = array(
        'id'		=> null,		// merchant ID provided by AirPay
        'secret'	=> null,		// merchant secret code provided by merchant
	);

    /**
     * Construct method that sets all the variables form the dependency injection.
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
	public function __construct (DI\ContainerInterface $container, Request $request, EventDispatcher $ed) {
        $this->_sc =& $container;
        $this->request = $request;
        $this->_em = $container->get('doctrine.orm.entity_manager');
        $this->eventDispatcher = $ed;
        // Settings  
        $this->merchant['id'] = $this->_sc->getParameter('beinarovic.airpay.merchant_id');
        $this->merchant['secret'] = $this->_sc->getParameter('beinarovic.airpay.merchant_secret');
        $this->url = $this->_sc->getParameter('beinarovic.airpay.url');
        $this->is_sandbox = $this->_sc->getParameter('beinarovic.airpay.is_sandbox');
	}

    /**
     * Creates a new AirpayPayment with a unique id.
     * 
     * @return AirpayPayment
     */
    public function createPayment() {
        $payment = new AirpayPayment($this->merchant['id']);
        
        $uid = uniqid();
        while ($this->getPaymentRepository()->isInvoiceAvailable($uid) === false) {
            $uid = uniqid();
        }
        $payment->setInvoice($uid);
        
        $logEvent = new AirpayLogEvent('Payment created');
        $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        
        return $payment;
    }
    
    /**
     * Validates received query parameters. Called when user is redirected back.
     * 
     * @return bool
     */
    public function paymentPassed() 
    {
        $queryParameters = $this->request->query->all();
        if (isset($queryParameters['transaction_id']) 
                && isset($queryParameters['transaction_hash'])){
            
            if (md5(htmlspecialchars_decode($queryParameters['transaction_id'])
                    .$this->merchant['id'].$this->merchant['secret']) 
                    == $queryParameters['transaction_hash']) {
                
                if($queryParameters['status'] == 1) {
                    $this->payment = $this->fetchTransaction($queryParameters['transaction_id'], true);
                    
                    return true;
                }
            }
        }
        $this->errors[] = AirpayError::HASH_DOES_NOT_MATCH;
        $logEvent = new AirpayLogEvent('Hash does not match', AirpayError::HASH_DOES_NOT_MATCH);
        $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        
        return false;
    }
    
    public function getPayment() {
        if ($this->payment !== null) {
            return $this->payment;
        }
        $this->errors[] = AirpayError::PAYMENT_NOT_RETRIEVED;
        
        $logEvent = new AirpayLogEvent('payment not retrieved', AirpayError::PAYMENT_NOT_RETRIEVED);
        $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        
        return false;
    }
    
    /**
     * Chacks if payment is a reafund.
     * 
     * @return boolean
     */
    public function isRefund()
    {
        if ($this->payment->getStatus() == AirpayPayment::STATUS_REFUND) {
            return true;
        } 
        
        return false;
    }
    
    /**
     * Chacks if payment is a reafund.
     * 
     * @return boolean
     */
    public function isSuccessful()
    {
        if ($this->payment->getStatus() == AirpayPayment::STATUS_SUCCESS) {
            return true;
        } 
        
        return false;
    }
    
    /**
     * Checks if received payment is valid. Saves the payment.
     * 
     * @return boolean
     */
    public function validate() 
    {
        $responseData = $this->request->request->all();
        
        if (md5($responseData['transaction_id'].$responseData['amount'].$responseData['currency']
                .$this->merchant['id'].$responseData['status_id'].$this->merchant['secret'])
                == $responseData['hash']) {
            
            $transactionid = $responseData['transaction_id'];
            
            $this->payment = $this->fetchTransaction($transactionid, true);
            
            if ($this->payment === null) {
                $this->errors[] = AirpayError::PAYMENT_NOT_FOUND;
                $logEvent = new AirpayLogEvent('Hash doeas not match', AirpayError::HASH_DOES_NOT_MATCH);
                $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
                
                return false;
            }
            
            if ($this->payment->getStatus() == AirpayPayment::STATUS_SUCCESS) {
                return true;
            } 
            
            $this->errors[] = AirpayError::HASH_DOES_NOT_MATCH;
            $logEvent = new AirpayLogEvent('Payment status is not successful', AirpayError::HASH_DOES_NOT_MATCH, $this->payment);
            $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        
            return false;
        }
        $this->errors[] = AirpayError::HASH_DOES_NOT_MATCH;
        $logEvent = new AirpayLogEvent('Hash does not match', AirpayError::HASH_DOES_NOT_MATCH);
        $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        
        return false;
    }

	/**
     * This method is used to generate a form interface. Also it trigers the 
     * entity manager's flush method to save the payment data.
     * 
     * @return FormInterface
	 */
	public function createForm(AirpayPayment $payment, $save = true) {
        if ($save == true) {
            $this->_em->persist($payment);
            $this->_em->flush($payment);
            $logEvent = new AirpayLogEvent('Created form and saved payment', AirpayError::HASH_DOES_NOT_MATCH, $payment);
            $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        }
        
        $paymentForm = $this->getFormFactory()
                ->create(new AirpayPaymentType(), $payment);
        
		return $paymentForm;
	}
    
	/**
	* List of available payment system aliases
	* It's better to cache it locally instead of request AirPay server for each client
	*
	* @return array			
     *              // CC = credit card; EM = E-money; IB = Internet bank
	*				// cat_id = same as above but integer
	*				// alias = payment system alias in AirPay system
	*				// title = payment system title in AirPay system
	*/
	public function getPaymentMethods() {
		$data = array (
			'_cmd'          => "psystems",
			'merchant_id'	=> $this->merchant['id'],
			'hash'          => md5($this->merchant['id'].$this->merchant['secret'])
		);

		$response = $this->sendRequest($this->url, $data);
        
        list ($status, $sarr) = explode("\n", $response);

        if ($status == 'OK') {
            return unserialize($sarr);
        }

        $logEvent = new AirpayLogEvent('Response was not OK ('.$response.')', AirpayError::HASH_DOES_NOT_MATCH);
        $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
        $this->errors[] = AirpayError::GOT_WRONG_STATUS;
        
		return false;
	}
    
    /**
     * @return string $action
     */
    public function getFormAction() 
    {
        return $this->url;
    }
    
    //
    // Private methods
    //
    
	/**
	* Sends a post request.
	*
	* @param  string $url
	* @param  array $post
	*
	* @return string $response
	*/
	private function sendRequest($url, $post) {
        $postFields = array(); 
		foreach ($post as $key => $value)	{
            $postFields[] = $key."=".$value;
        }

		$channel = curl_init($url);
		curl_setopt($channel, CURLOPT_POST      		, 1);
		curl_setopt($channel, CURLOPT_POSTFIELDS        , implode('&', $postFields));
		curl_setopt($channel, CURLOPT_HEADER            , 0);
		curl_setopt($channel, CURLOPT_RETURNTRANSFER    , 1);
		$response = curl_exec($channel);
        

		return $response;
	}

    /**
     * Fetches AirpayPayment by Airpay transaction id.
     * 
     * @param type $transactionId
     * @param type $update
     * @return AirpayPayment
     */
    private function fetchTransaction($transactionId, $update = false) {
        $oldPayment = $this->getPaymentRepository()->findByTransactionId($transactionId);
        
        if ($oldPayment === null || $update === true) {
            $data = array (
                '_cmd'		=> "request",
                'invoice'	=> $transactionId,
                'hash' 		=> md5($transactionId.$this->merchant['id'].$this->merchant['secret'])
            );

            $response = $this->sendRequest($this->url, $data);

            $array = explode('&', $response);
            for ($i = 0; $i < sizeof($array); $i++) {
                list($key, $value) = explode('=', $array[$i]);
                $formated[$key] = $value;
            }

            if (isset($formated['error_code']))	{
                $this->errors[] = AirpayError::RECEIVED_ERROR_CODE;
                $logEvent = new AirpayLogEvent('Got error code: '.$formated['error_code'], AirpayError::HASH_DOES_NOT_MATCH);
                $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
            
                return null;
            }

            if (md5($formated['transaction_id'].$formated['amount'].$formated['currency']
                    .$this->merchant['id'].$formated['status_id'].$this->merchant['secret']) 
                    == $formated['hash']) {

                $invoice = $formated['mc_transaction_id'];

                $payment = $this->getPaymentRepository()->findByInvoice($invoice);

                if  ($payment === null) {
                    $logEvent = new AirpayLogEvent('Payment not received', AirpayError::PAYMENT_NOT_FOUND);
                    $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
                    $this->errors[] = AirpayError::PAYMENT_NOT_FOUND;
                
                    return null;
                }
                $payment->bindResponse($formated);

                if ($update === true) {
                    $payment->setUpdatedAt(new \DateTime());
                    $this->_em->flush($payment);
                    $logEvent = new AirpayLogEvent('Got and updated payment', null, $payment);
                    $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
                }
                
                return $payment;
            }

            $this->errors[] = AirpayError::HASH_DOES_NOT_MATCH;
            $logEvent = new AirpayLogEvent('Hash does not match', AirpayError::HASH_DOES_NOT_MATCH);
            $this->eventDispatcher->dispatch('beinarovic_airpay.log', $logEvent);
            
            return null;
        }
        
        return $oldPayment;
	}
    
    /**
     * @return AirpayPaymentRepository
     */
    private function getPaymentRepository() 
    {
        if ($this->_pr === null) {
            $this->_pr = $this->_em->getRepository('BeinarovicAirpayBundle:AirpayPayment');
        }
        
        return $this->_pr;
    }
    
    /**
     * @return FormFactoryInterface
     */
    private function getFormFactory() 
    {
        if ($this->_ff === null) {
            $this->_ff = $this->_sc->get('form.factory');
        }
        
        return $this->_ff;
    }
}