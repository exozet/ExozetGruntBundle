<?php

namespace Exozet\GruntBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
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

        $configKeys = [
            'environments',
            'binary_env_vars',
            'npm_binary_path',
            'bower_binary_path',
            'grunt_binary_path',
            'grunt_task',
        ];

        foreach ($configKeys as $configKey) {
            $container->setParameter(
                self::CONFIG_PREFIX.'.'.$configKey,
                $config[$configKey]
            );
        }

        if (!empty($config['binary_env_vars'])) {
            $binaryEnvVarsString = implode(
                ' ',
                array_map(
                    function ($v, $k) { return sprintf('%s="%s"', $k, $v); },
                    $config['binary_env_vars'],
                    array_keys($config['binary_env_vars'])
                )
            );
        } else {
            $binaryEnvVarsString = '';
        }

        $container->setParameter(
            self::CONFIG_PREFIX.'.binary_env_vars_string',
            $binaryEnvVarsString
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
