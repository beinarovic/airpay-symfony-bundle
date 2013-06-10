<?php
namespace Beinarovic\AirpayBundle\Errors;

class AirpayError {
    const COULD_NOT_CONNECT_TO_AIRPAY = 0;
    const PAYMENT_NOT_FOUND = 1;
    
    /* 
     * Run fetchTransaction first.
     */
    const PAYMENT_NOT_RETRIEVED = 2; 
    const HASH_DOES_NOT_MATCH = 3;
    const GOT_WRONG_RESPONSE = 4;
    const GOT_WRONG_STATUS = 5;
    const RECEIVED_ERROR_CODE = 6;
    const PAYMENT_ALREADY_CLOSED = 7;
}

?>
