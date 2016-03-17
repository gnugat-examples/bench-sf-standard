#!/usr/bin/env php
<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

set_time_limit(0);

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
require __DIR__.'/../app/autoload.php';

$kernel = new AppKernel('prod', false);

// Preloading all services
$kernel->boot();
$container = $kernel->getContainer();
foreach ($container->getServiceIds() as $serviceId) {
    $container->get($serviceId);
}

$application = new Application($kernel);
$application->setDefaultCommand('speedfony:run');
$application->run(new ArgvInput());
