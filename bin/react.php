#!/usr/bin/env php
<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/autoload.php';


$kernel = new AppKernel('prod', false);

// Preloading all services
$kernel->boot();
$container = $kernel->getContainer();
foreach ($container->getServiceIds() as $serviceId) {
    $container->get($serviceId);
}

$app = function ($request, $response) use ($kernel) {
    $headers = $request->getHeaders();
    $enableProfiling = isset($headers['X-Blackfire-Query']);
    if ($enableProfiling) {
        $blackfire = new \Blackfire\Client();
        $probe = $blackfire->createProbe();
    }

    $sfRequest = Request::create(
        $request->getPath(),
        $request->getMethod()
    );
    $sfResponse = $kernel->handle($sfRequest);

    $response->writeHead($sfResponse->getStatusCode());
    $response->end($sfResponse->getContent());

    $kernel->terminate($sfRequest, $sfResponse);

    if ($enableProfiling) {
        $blackfire->endProbe($probe);
    }
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket);

$http->on('request', $app);

$socket->listen(getenv('PORT') ?: 8080);
$loop->run();
