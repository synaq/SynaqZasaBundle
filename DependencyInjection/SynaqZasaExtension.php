<?php

namespace Synaq\ZasaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SynaqZasaExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        if (!isset($config['server'])) {
            throw new \InvalidArgumentException(
                'The "synaq_zasa.server" option must be set'
            );
        }

        if (!isset($config['admin_user'])) {
            throw new \InvalidArgumentException(
                'The "synaq_zasa.admin_user" option must be set'
            );
        }

        if (!isset($config['admin_pass'])) {
            throw new \InvalidArgumentException(
                'The "synaq_zasa.admin_pass" option must be set'
            );
        }

        $container->setParameter('synaq_zasa.server', $config['server']);
        $container->setParameter('synaq_zasa.admin_user', $config['admin_user']);
        $container->setParameter('synaq_zasa.admin_pass', $config['admin_pass']);
        $container->setParameter('synaq_zasa.use_fopen', $config['use_fopen']);
        $container->setParameter('synaq_zasa.auth_token_path', $config['auth_token_path']);
        $container->setParameter('synaq_zasa.rest_base_url', $config['rest_base_url']);
        $container->setParameter('synaq_zasa.auth_propagation_time', $config['auth_propagation_time']);
        $container->setParameter('synaq_zasa.ignore_delegated_auth', $config['ignore_delegated_auth']);
    }
}
