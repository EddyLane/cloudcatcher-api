<?php

namespace Fridge\FirebaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FridgeFirebaseExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        if (!isset($configs[0]['firebase_base_url'])) {
            throw new \InvalidArgumentException(
                'The "firebase_base_url" option must be set for the "firebase_bundle" bundle'
            );
        }

        $container->setParameter(
            'fridge_firebase.firebase_base_url',
            $configs[0]['firebase_base_url']
        );

        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
