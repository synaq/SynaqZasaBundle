<?php

namespace Synaq\ZasaBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('synaq_zasa');

        $rootNode
            ->children()
            ->scalarNode('server')
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('admin_user')
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('admin_pass')
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('use_fopen')
            ->defaultTrue()
            ->end()
            ->scalarNode('auth_token_path')
            ->defaultNull()
            ->end()
            ->scalarNode('rest_base_url')
            ->defaultNull()
            ->end()
            ->scalarNode('auth_propagation_time')
            ->defaultValue(0)
            ->end()
            ->scalarNode('ignore_delegated_auth')
            ->defaultFalse()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
