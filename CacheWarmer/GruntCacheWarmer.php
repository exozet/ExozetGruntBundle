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
        $npmBinaryPath = $container->getParameter('grunt.npm_binary_path');
        $bowerBinaryPath = $container->getParameter('grunt.bower_binary_path');
        $gruntBinaryPath = $container->getParameter('grunt.grunt_binary_path');
        $gruntTask = $container->getParameter('grunt.grunt_task');
        $environment = $this->kernel->getEnvironment();

        if (in_array($environment, $container->getParameter('grunt.environments')))
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

            $process = new Process($gruntBinaryPath . ' ' . $gruntTask);
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
