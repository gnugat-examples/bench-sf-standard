<?php

namespace Gnugat\BenchSfStandard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Stateless
 */
class SymfonyService
{
    private $kernel;

    public function __construct()
    {
        $this->kernel = new \AppKernel('prod', false);
    }

    public function handle(Request $request)
    {
        return $this->kernel->handle($request);
    }

    public function terminate(Request $request, Response $response)
    {
        $this->kernel->terminate($request, $response);
    }
}
