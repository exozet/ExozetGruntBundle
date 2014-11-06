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
        }
    }

    protected function launchNpmInstall()
    {
        $binaryEnvVarsString = $this->container->getParameter('exozet_grunt.binary_env_vars_string');
        $npmBinaryPath = $this->container->getParameter('exozet_grunt.npm_binary_path');

        $this->logger->debug('[' . get_class($this) . '] Launching npm install');

        $npmCommand = trim(
            implode(
                ' ',
                array(
                    $binaryEnvVarsString,
                    $npmBinaryPath,
                    'install'
                )
            )
        );

        $this->logger->debug('[' . get_class($this) . '] ' . $npmCommand);

        $process = new Process($npmCommand);
        $process->setWorkingDirectory($this->rootDirectory);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception(get_class($this) . ' cannot execute node: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
        }
    }

    protected function launchBowerInstall()
    {
        $binaryEnvVarsString = $this->container->getParameter('exozet_grunt.binary_env_vars_string');
        $bowerBinaryPath = $this->container->getParameter('exozet_grunt.bower_binary_path');

        $this->logger->debug('[' . get_class($this) . '] Launching bower install');

        $bowerCommand = trim(
            implode(
                ' ',
                array(
                    $binaryEnvVarsString,
                    $bowerBinaryPath,
                    'install --silent'
                )
            )
        );

        $this->logger->debug('[' . get_class($this) . '] ' . $bowerCommand);

        $process = new Process($bowerCommand);
        $process->setWorkingDirectory($this->rootDirectory);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception(get_class($this) . ' cannot execute bower: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
        }
    }

    protected function executeGruntTask()
    {
        $binaryEnvVarsString = $this->container->getParameter('exozet_grunt.binary_env_vars_string');
        $gruntBinaryPath = $this->container->getParameter('exozet_grunt.grunt_binary_path');
        $gruntTask = $this->container->getParameter('exozet_grunt.grunt_task');

        $this->logger->debug('[' . get_class($this) . '] Launching grunt task ' . $gruntTask);

        $gruntCommand = trim(
            implode(
                ' ',
                array(
                    $binaryEnvVarsString,
                    $gruntBinaryPath,
                    $gruntTask
                )
            )
        );

        $this->logger->debug('[' . get_class($this) . '] ' . $gruntCommand);

        $process = new Process($gruntCommand);
        $process->setWorkingDirectory($this->rootDirectory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(get_class($this) . ' cannot execute grunt: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
        }

        $this->logger->debug('[' . get_class($this) . '] finished');
    }
}
