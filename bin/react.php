<?php

use Symfony\Component\HttpFoundation\Request;

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

$app = function ($request, $response) use ($kernel) {
    $sfRequest = Request::create(
        $request->getPath(),
        $request->getMethod()
    );
    $sfResponse = $kernel->handle($sfRequest);

    $response->writeHead($sfResponse->getStatusCode());
    $response->end($sfResponse->getContent());

    $kernel->terminate($sfRequest, $sfResponse);
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket);

$http->on('request', $app);

$socket->listen(1337);
$loop->run();
