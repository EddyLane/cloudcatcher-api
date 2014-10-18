<?php

namespace Fridge\ApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FridgeApiExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        if (!isset($configs[0]['google_feed_url'])) {
            throw new \InvalidArgumentException(
                'The "google_feed_url" option must be set for the "fridge_api" bundle'
            );
        }

        if (!isset($configs[0]['itunes_search_url'])) {
            throw new \InvalidArgumentException(
                'The "itunes_search_url" option must be set for the "fridge_api" bundle'
            );
        }

        $container->setParameter(
            'fridge_api.google_feed_url',
            $configs[0]['google_feed_url']
        );

        $container->setParameter(
            'fridge_api.itunes_search_url',
            $configs[0]['itunes_search_url']
        );

        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
