<?php

namespace Exozet\GruntBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;

class GruntCacheWarmer implements CacheWarmerInterface
{
    protected $kernel;
    protected $container;
    protected $logger;
    protected $rootDirectory;

    function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->container = $this->kernel->getContainer();
        $this->rootDirectory = realpath($this->kernel->getRootDir() . '/..') . '/';
    }

    function isOptional()
    {
        return true;
    }

    function warmUp($cacheDir)
    {
        if (in_array($this->kernel->getEnvironment(), $this->container->getParameter('exozet_grunt.environments'))) {
            $this->launchNpmInstall();
            $this->launchBowerInstall();
            $this->executeGruntTask();

            $this->logger->debug('[' . get_class($this) . '] finished');
        }
    }

    protected function launchNpmInstall()
    {
        $this->logger->debug('[' . get_class($this) . '] Launching npm install');

        $npmCommand = trim(
            implode(
                ' ',
                array(
                    $this->container->getParameter('exozet_grunt.binary_env_vars_string'),
                    $this->container->getParameter('exozet_grunt.npm_binary_path'),
                    'install'
                )
            )
        );

        $this->executeCommand($npmCommand);
    }

    protected function launchBowerInstall()
    {
        $this->logger->debug('[' . get_class($this) . '] Launching bower install');

        $bowerCommand = trim(
            implode(
                ' ',
                array(
                    $this->container->getParameter('exozet_grunt.binary_env_vars_string'),
                    $this->container->getParameter('exozet_grunt.bower_binary_path'),
                    'install --silent'
                )
            )
        );

        $this->executeCommand($bowerCommand);
    }

    protected function executeGruntTask()
    {
        $this->logger->debug('[' . get_class($this) . '] Launching grunt task');

        $gruntCommand = trim(
            implode(
                ' ',
                array(
                    $this->container->getParameter('exozet_grunt.binary_env_vars_string'),
                    $this->container->getParameter('exozet_grunt.grunt_binary_path'),
                    $this->container->getParameter('exozet_grunt.grunt_task')
                )
            )
        );

        $this->executeCommand($gruntCommand);
    }

    protected function executeCommand($command)
    {
        $this->logger->debug('[' . get_class($this) . '] ' . $command);

        $process = new Process($command);
        $process->setWorkingDirectory($this->rootDirectory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(get_class($this) . ' cannot execute command: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
        }
    }
}
