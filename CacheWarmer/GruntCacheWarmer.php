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
        $rootDirectory = realpath($this->kernel->getRootDir() . '/..') . '/';

        $binaryEnvVars = $container->getparameter('exozet_grunt.binary_env_vars');
        $binaryEnvVarsString = $container->getparameter('exozet_grunt.binary_env_vars_string');
        $npmBinaryPath = $container->getParameter('exozet_grunt.npm_binary_path');
        $bowerBinaryPath = $container->getParameter('exozet_grunt.bower_binary_path');
        $gruntBinaryPath = $container->getParameter('exozet_grunt.grunt_binary_path');
        $gruntTask = $container->getParameter('exozet_grunt.grunt_task');

        if (in_array($this->kernel->getEnvironment(), $container->getParameter('exozet_grunt.environments')))
        {
            $this->logger->debug('GruntCacheWarmer: Launching npm install');

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

            $this->logger->debug('GruntCacheWarmer: ' . $npmCommand);

            $process = new Process($npmCommand);
            $process->setWorkingDirectory($rootDirectory);
            $process->run();
            if (!$process->isSuccessful())
            {
                throw new \Exception('GruntBundle cannot execute node: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
            }

            $this->logger->debug('GruntCacheWarmer: Launching bower install');

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

            $this->logger->debug('GruntCacheWarmer: ' . $bowerCommand);

            $process = new Process($bowerCommand);
            $process->setWorkingDirectory($rootDirectory);
            $process->run();
            if (!$process->isSuccessful())
            {
                throw new \Exception('GruntBundle cannot execute bower: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
            }

            $this->logger->debug('GruntCacheWarmer: Launching grunt task ' . $gruntTask);

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

            $this->logger->debug('GruntCacheWarmer: ' . $gruntCommand);

            $process = new Process($gruntCommand);
            $process->setWorkingDirectory($rootDirectory);
            $process->run();

            if (!$process->isSuccessful())
            {
                throw new \Exception('GruntBundle cannot execute grunt: ' . $process->getOutput() . PHP_EOL . PHP_EOL . $process->getErrorOutput());
            }

            $this->logger->debug('GruntCacheWarmer: done');
        }
    }
}
