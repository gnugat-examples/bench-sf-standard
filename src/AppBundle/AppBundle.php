<?php

namespace AppBundle;

use AppBundle\DependencyInjection\CompilerPass\AddListenerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddListenerCompilerPass());
    }
}
