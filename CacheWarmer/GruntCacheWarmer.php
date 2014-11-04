<?php

namespace Exozet\GruntBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;

class GruntCacheWarmer implements CacheWarmerInterface
{
    protected $kernel;
    protected $logger;

    function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    function isOptional()
    {
        return true;
    }

    function warmUp($cacheDir)
    {
        $container = $this->kernel->getContainer();
        $webRootDirectory = realpath($this->kernel->getRootDir() . '/../web') . '/';

        $binaryEnvVars = $container->getparameter('exozet_grunt.binary_env_vars');
        $binaryEnvVarsString = $container->getparameter('exozet_grunt.binary_env_vars_string');
        $npmBinaryPath = $container->getParameter('exozet_grunt.npm_binary_path');
        $bowerBinaryPath = $container->getParameter('exozet_grunt.bower_binary_path');
        $gruntBinaryPath = $container->getParameter('exozet_grunt.grunt_binary_path');
        $gruntTask = $container->getParameter('exozet_grunt.grunt_task');
        $environment = $this->kernel->getEnvironment();

        if (in_array($environment, $container->getParameter('exozet_grunt.environments')))
        {
            $this->logger->debug('GruntCacheWarmer: Launching npm install');
            $process = new Process($npmBinaryPath . ' install');
            $process->setWorkingDirectory($webRootDirectory);
            $process->run();
            if (!$process->isSuccessful())
            {
                throw new \Exception('GruntBundle cannot execute node: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
            }

            $this->logger->debug('GruntCacheWarmer: Launching bower install');

            $process = new Process($bowerBinaryPath . ' install --silent');
            $process->setWorkingDirectory($webRootDirectory);
            $process->run();
            if (!$process->isSuccessful())
            {
                throw new \Exception('GruntBundle cannot execute bower: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
            }

            $this->logger->debug('GruntCacheWarmer: Launching grunt task ' . $gruntTask);

            $command = trim(
                implode(
                    ' ',
                    array(
                        $gruntEnvVars,
                        $gruntBinaryPath,
                        $gruntTask
                    )
                )
            );

            $process = new Process($command);
            $process->setWorkingDirectory($webRootDirectory);
            $process->run();

            if (!$process->isSuccessful())
            {
                throw new \Exception('GruntBundle cannot execute grunt: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
            }

            $this->logger->debug('GruntCacheWarmer: done');
        }
    }
}
