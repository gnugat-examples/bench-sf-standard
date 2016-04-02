#!/usr/bin/env php
<?php

use Icicle\Http\Message\{BasicResponse, Request, Response};
use Icicle\Http\Server\{RequestHandler, Server};
use Icicle\Loop;
use Icicle\Socket\Socket;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Symfony\Component\HttpKernel\Kernel;

$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

$server = new Server(new class($kernel) implements RequestHandler {
    private $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function onRequest(Request $request, Socket $socket)
    {
        $sfRequest = SfRequest::create(
            $request->getUri()->getPath(),
            $request->getMethod()
        );
        $sfResponse = $this->kernel->handle($sfRequest);
        $response = new BasicResponse(
            $sfResponse->getStatusCode(),
            $sfResponse->headers->all()
        );
        yield from $response->getBody()->end($sfResponse->getContent());
        $this->kernel->terminate($sfRequest, $sfResponse);

        return $response;
    }

    public function onError(int $code, Socket $socket)
    {
        return new BasicResponse($code);
    }
});

$server->listen(getenv('PORT') ?: 8080);

Loop\run();
