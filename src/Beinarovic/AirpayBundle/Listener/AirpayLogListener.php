<?php
namespace Beinarovic\AirpayBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\EntityManager;
use Beinarovic\AirpayBundle\Entity\AirpayLog;

class AirpayLogListener
{    
    /**
     * @var Request
     */
    private $request = null;
    
    /**
     * @var ContainerInterface 
     */
    private $container = null;
    
    /**
     * @var EntityManager 
     */
    private $em = null;
    
    /**
     * @var boolean 
     */
    private $enabled = null;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->request = $container->get('request');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->enabled = $container->getParameter('beinarovic.airpay.enable_logs');
    }
    
    public function onLog(Event $event) 
    {
        if ($this->enabled === true) {
            
            $log = new AirpayLog();

            $log->setErrorCode($event->getErrorCode());
            $log->setTitle($event->getTitle());
            if ($event->getPayment()) {
                $log->setPayment($event->getPayment());
            }

            $log->setLoggedAt(new \DateTime());
            $log->setRequestGet($this->request->query->all());
            $log->setRequestPost($this->request->request->all());
            
            $this->em->persist($log);
            $this->em->flush($log);
        }
    }
}