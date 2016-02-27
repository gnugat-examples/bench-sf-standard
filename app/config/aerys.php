<?php

use Aerys\Request;
use Aerys\Response;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../../vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$kernel = new AppKernel('prod', false);
$kernel->boot();

// Preloading all services
$container = $kernel->getContainer();
foreach ($container->getServiceIds() as $serviceId) {
    $container->get($serviceId);
}

const AERYS_OPTIONS = [
    'user' => 'gnucat',
];

$docroot = Aerys\root(__DIR__.'/../../web');

(new Aerys\Host)
    ->name('bench-sf-standard.example.com')
    ->expose("*", 5000)
    ->use(function(Request $req, Response $resp) use ($kernel) {
        $sfRequest = SfRequest::create(
            $req->getUri(),
            $req->getMethod()
        );
        $sfResponse = $kernel->handle($sfRequest);

        $resp->setStatus($sfResponse->getStatusCode());
        $resp->send($sfResponse->getContent());

        $kernel->terminate($sfRequest, $sfResponse);
    })
    ->use($docroot)
;
