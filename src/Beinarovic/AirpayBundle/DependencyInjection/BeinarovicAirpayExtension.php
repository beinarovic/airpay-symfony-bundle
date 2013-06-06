<?php

namespace Beinarovic\AirpayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BeinarovicAirpayExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $container->setParameter('beinarovic.airpay.is_sandbox', $config['is_sandbox']);
        $container->setParameter('beinarovic.airpay.merchant_id', $config['merchant_id']);
        $container->setParameter('beinarovic.airpay.merchant_secret', $config['merchant_secret']);
        $container->setParameter('beinarovic.airpay.enable_logs', $config['enable_logs']);
        
        if ($config['is_sandbox'])
        {
            $container->setParameter('beinarovic.airpay.url', $config['sandbox_url']);
        }
        else
        {
            $container->setParameter('beinarovic.airpay.url', $config['sandbox_url']);
        }

        $loader->load('services.xml');
    }
}
