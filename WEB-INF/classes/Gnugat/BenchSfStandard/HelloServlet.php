<?php

namespace Gnugat\BenchSfStandard;

use AppserverIo\Psr\Servlet\Http\HttpServlet;
use Symfony\Component\HttpFoundation\Request as SfRequest;

/**
 * @Route(name="helloWorld", urlPattern={"/hello.do", "/hello.do*"})
 */
class HelloServlet extends HttpServlet
{
    /**
     * @EnterpriseBean(name="SymfonyService")
     */
    protected $symfonyService;

    public function doGet($servletRequest, $servletResponse)
    {
        $sfRequest = SfRequest::create(
            $servletRequest->getPathInfo(),
            $servletRequest->getMethod()
        );
        $sfResponse = $this->symfonyService->handle($sfRequest);
        $servletResponse->setStatusCode($sfResponse->getStatusCode());
        $servletResponse->appendBodyStream(
            $sfResponse->getContent()
        );
        $this->symfonyService->terminate($sfRequest, $sfResponse);
    }
}
