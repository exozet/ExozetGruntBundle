<?php

namespace Exozet\GruntBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('exozet_grunt');

        $rootNode
            ->children()
                ->arrayNode('environments')
                    ->info('The environments where the bundle should be executed')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->defaultValue(array('dev'))
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('npm_binary_path')
                    ->info('The binary path where npm is located')
                    ->example('/usr/bin/npm')
                    ->defaultValue('npm')
                ->end()
                ->scalarNode('bower_binary_path')
                    ->info('The binary path where bower is located')
                    ->example('/usr/bin/bower')
                    ->defaultValue('bower')
                ->end()
                ->scalarNode('grunt_binary_path')
                    ->info('The binary path where grunt is located')
                    ->example('/usr/bin/grunt')
                    ->defaultValue('bower')
                ->end()
                ->arrayNode('grunt_env_vars')
                    ->info('Use environment vars for grunt with key/value pairs')
                    ->example('LANG:    en_US.UTF-8 for LANG="en_US.UTF-8"')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('grunt_task')
                    ->info('The grunt task which should be executed')
                    ->example('dev')
                    ->defaultValue('dev')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
