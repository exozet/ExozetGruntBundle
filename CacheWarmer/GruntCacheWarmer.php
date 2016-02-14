<?php

namespace Exozet\GruntBundle\CacheWarmer;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class GruntCacheWarmer implements CacheWarmerInterface
{
    protected $kernel;
    protected $container;
    protected $logger;
    protected $rootDirectory;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->container = $this->kernel->getContainer();
        $this->rootDirectory = realpath($this->kernel->getRootDir().'/..').'/';
    }

    public function isOptional()
    {
        return true;
    }

    public function warmUp($cacheDir)
    {
        if (in_array($this->kernel->getEnvironment(), $this->container->getParameter('exozet_grunt.environments'))) {
            $this->launchNpmInstall();
            $this->launchBowerInstall();
            $this->executeGruntTask();

            $this->logger->debug('['.get_class($this).'] finished');
        }
    }

    protected function launchNpmInstall()
    {
        $this->logger->debug('['.get_class($this).'] '.__FUNCTION__);

        $npmCommand = trim(
            implode(
                ' ',
                [
                    $this->container->getParameter('exozet_grunt.binary_env_vars_string'),
                    $this->container->getParameter('exozet_grunt.npm_binary_path'),
                    'install',
                ]
            )
        );

        $this->executeCommand($npmCommand);
    }

    protected function launchBowerInstall()
    {
        $this->logger->debug('['.get_class($this).'] '.__FUNCTION__);

        $bowerCommand = trim(
            implode(
                ' ',
                [
                    $this->container->getParameter('exozet_grunt.binary_env_vars_string'),
                    $this->container->getParameter('exozet_grunt.bower_binary_path'),
                    'install --silent',
                ]
            )
        );

        $this->executeCommand($bowerCommand);
    }

    protected function executeGruntTask()
    {
        $this->logger->debug('['.get_class($this).'] '.__FUNCTION__);

        $gruntCommand = trim(
            implode(
                ' ',
                [
                    $this->container->getParameter('exozet_grunt.binary_env_vars_string'),
                    $this->container->getParameter('exozet_grunt.grunt_binary_path'),
                    $this->container->getParameter('exozet_grunt.grunt_task'),
                ]
            )
        );

        $this->executeCommand($gruntCommand);
    }

    protected function executeCommand($command)
    {
        $this->logger->debug('['.get_class($this).'] '.$command);

        $process = new Process($command);
        $process->setWorkingDirectory($this->rootDirectory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(
                implode(
                    ' ',
                    [
                        get_class($this),
                        'cannot execute command:',
                        $process->getOutput().PHP_EOL.PHP_EOL.$process->getErrorOutput(),
                    ]
                )
            );
        }
    }
}
