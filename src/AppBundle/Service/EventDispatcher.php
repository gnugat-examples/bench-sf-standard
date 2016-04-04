<?php

namespace AppBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcher as SfEventDispatcher;

/**
 * Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass
 * requires an instance of Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher.
 *
 * Since we want to have instead a simple Symfony\Component\EventDispatcher\EventDispatcher
 * and we cannot unregister compiler passes, we'll use this class to by pass it.
 */
class EventDispatcher extends SfEventDispatcher
{
    public function addListenerService($eventName, $callback, $priority = 0)
    {
    }

    public function addSubscriberService($serviceId, $class)
    {
    }
}
