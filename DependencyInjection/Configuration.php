<?php

namespace MeloLab\BioGestion\FileUploadBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('melolab_biogestion_fileupload');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        
        $rootNode
            ->children()
                ->scalarNode('max_file_size')
                    ->defaultValue('5M')
                    ->info('Max file size (global setting), e.g. 5M or 500K')
                ->end()
                ->scalarNode('accepted_file_types')
                    ->defaultValue('/(\.|\/)(pdf)$/i')
                    ->info('Acceptable file extensions regular expression (global seeting), e.g. /(\.|\/)(pdf)$/i')
                ->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('entity')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Fully qualified entity class name')
                            ->end()
                            ->scalarNode('file_field')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('File field (variable name)')
                            ->end()
                            ->scalarNode('file_getter')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Getter of the file variable')
                            ->end()
                            ->scalarNode('filename_getter')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Getter of the filename variable')
                            ->end()
                            ->scalarNode('vich_mapping')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Vich Uploader mapping')
                            ->end()
                            ->scalarNode('max_file_size')
                                ->info('Max file size in MB, overrides the global setting')
                            ->end()
                            ->scalarNode('accepted_file_types')
                                ->info('Acceptable file extensions regular expression, overrides the global setting')
                            ->end()
                            ->scalarNode('form_type')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->info('Form type which includes a file field')
                            ->end()
                            ->scalarNode('repository_method')
                                ->info('Repository method to find the entity')
                                ->defaultValue('findOneById')
                            ->end()
                            ->booleanNode('allow_anonymous_downloads')
                                ->info('Set true to allow access to file downloads by anonymous (not logged in) users')
                                ->defaultFalse()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        

        return $treeBuilder;
    }
}
