<?php

use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';


$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

$i = 0;

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
