<?php

namespace Beinarovic\AirpayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('beinarovic_airpay');

        $rootNode->children()
                ->scalarNode('is_sandbox')->defaultValue(false)->end()
                ->scalarNode('enable_logs')->defaultValue(false)->end()
                ->scalarNode('url')->defaultValue('https://www.airpayment.net/new/gateway/')->end()
                ->scalarNode('sandbox_url')->defaultValue('https://www.airpayment.net/sandbox/gateway/')->end()
                ->scalarNode('merchant_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('merchant_secret')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
