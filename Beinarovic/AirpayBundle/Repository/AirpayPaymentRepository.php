<?php

namespace Beinarovic\AirpayBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Beinarovic\AirpayBundle\Entity\AirpayPayment;

class AirpayPaymentRepository extends EntityRepository
{
    /**
     * @param string $invoice
     * @return boolean
     */
    public function isInvoiceAvailable($invoice) 
    {
        $result = $this->createQueryBuilder('p')
                ->where('p.invoice = :invoice')
                ->setParameter('invoice', $invoice)
                ->getQuery()
                ->getOneOrNullResult();
        
        if ($result === null) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string $invoice
     * @return AirpayPayment
     */
    public function findByInvoice($invoice) 
    {
        return $this->createQueryBuilder('p')
                ->where('p.invoice = :invoice')
                ->setParameter('invoice', $invoice)
                ->getQuery()
                ->getOneOrNullResult();
    }
    
    /**
     * @param string $airpayTransactionId
     * @return AirpayPayment
     */
    public function findByTransactionId($airpayTransactionId) 
    {
        return $this->createQueryBuilder('p')
                ->where('p.transactionId = :transactionId')
                ->setParameter('transactionId', $airpayTransactionId)
                ->getQuery()
                ->getOneOrNullResult();
    }
}
