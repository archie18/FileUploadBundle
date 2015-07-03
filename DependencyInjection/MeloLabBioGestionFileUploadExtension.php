<?php

namespace MeloLab\BioGestion\FileUploadBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MeloLabBioGestionFileUploadExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Store params in container
        $container->setParameter('melolab_biogestion_fileupload.max_file_size', $config['max_file_size']);
        $container->setParameter('melolab_biogestion_fileupload.accepted_file_types', $config['accepted_file_types']);
        $container->setParameter('melolab_biogestion_fileupload.mappings', $config['mappings']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
//        $loader->load('config.yml');
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAlias() {
        return 'melolab_biogestion_fileupload';
    }
}
