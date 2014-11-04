<?php

namespace Exozet\GruntBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Process\Process;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ExozetGruntExtension extends Extension
{
    const CONFIG_PREFIX = 'exozet_grunt';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $config_keys = array(
            'environments',
            'npm_binary_path',
            'bower_binary_path',
            'grunt_binary_path',
            'grunt_env_vars',
            'grunt_task'
        );

        foreach ($config_keys as $config_key) {
            $container->setParameter(
                self::CONFIG_PREFIX . '.' . $config_key,
                $config[$config_key]
            );
        }

        if (!empty($config['grunt_env_vars'])) {
            $grunt_env_vars_string = implode(
                ' ',
                array_map(
                    function ($v, $k) { return sprintf('%s="%s"', $k, $v); },
                    $config['grunt_env_vars'],
                    array_keys($config['grunt_env_vars'])
                )
            );
        } else {
            $grunt_env_vars_string = '';
        }

        $container->setParameter(
            self::CONFIG_PREFIX . '.grunt_env_vars_string',
            $grunt_env_vars_string
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
