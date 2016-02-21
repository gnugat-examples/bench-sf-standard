#!/usr/bin/env php
<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

set_time_limit(0);

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__.'/../app/autoload.php';

// "optional" dependencies that weren't included
$toIgnore = array(
    'Doctrine\Bundle\DoctrineCacheBundle\Acl\Model\AclCache',
);

// Preloading all classes
foreach ($loader->getClassMap() as $fqcn => $path) {
    $isTest = (1 === preg_match('/Test/', $fqcn));
    $isFixture = (1 === preg_match('/Fixture/', $fqcn));
    $isIgnored = (true === in_array($fqcn, $toIgnore, true));
    if ($isIgnored || $isTest || $isFixture) {
        // Skipping test classes as PHPUnit will be missing, resulting in Fatal error
        continue;
    }
    if (false === class_exists($fqcn) && false === interface_exists($fqcn) && false === trait_exists($fqcn)) {
        $loader->loadClass($fqcn);
    }
}

$kernel = new AppKernel('prod', false);

// Preloading all services
$kernel->boot();
$container = $kernel->getContainer();
foreach ($container->getServiceIds() as $serviceId) {
    $container->get($serviceId);
}

$application = new Application($kernel);
$application->run(new ArrayInput(array(
    'speedfony:run',
    '--port' => 5000,
    '--env' => 'prod',
    '--no-debug' => true,
)));
