<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!is_file($loaderFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('No autoload.php in vendor/. Please run "composer install --dev"!');
}

$loader = require $loaderFile;
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
