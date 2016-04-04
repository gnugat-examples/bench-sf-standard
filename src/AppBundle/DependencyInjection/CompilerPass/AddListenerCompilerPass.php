<?php

namespace AppBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddListenerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $eventDispatcher = $container->findDefinition('event_dispatcher');

        $eventListeners = $container->findTaggedServiceIds('kernel.event_listener');
        foreach ($eventListeners as $id => $events) {
            foreach ($events as $event) {
                $eventDispatcher->addMethodCall('addListener', array(
                    $event['event'],
                    array(new Reference($id), $event['method']),
                    isset($event['priority']) ? $event['priority'] : 0,
                ));
            }
        }

        $eventSubscribers = $container->findTaggedServiceIds('kernel.event_subscriber');
        foreach ($eventSubscribers as $id => $attributes) {
            $eventDispatcher->addMethodCall('addSubscriber', array(new Reference($id)));
        }
    }
}
