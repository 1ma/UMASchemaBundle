<?php

namespace UMA\SchemaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class UMASchemaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->processPaths(
            $this->processConfiguration(new Configuration(), $configs), $container
        );

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    private function processPaths(array $config, ContainerBuilder $container)
    {
        array_walk($config['paths'], function(&$path) {
            $realPath = realpath($path);

            if (false === $realPath || !is_dir($realPath)) {
                throw new \InvalidArgumentException("path '$path' listed in the uma_schema.paths configuration does not exist, cannot be read or is not a directory");
            }

            $path = $realPath;
        });

        $container->setDefinition(
            'uma_schema.file_locator',
            new Definition(FileLocator::class, [$config['paths']])
        );
    }
}
