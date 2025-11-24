<?php
declare(strict_types=1);

namespace Ksfraser\PluginSystem\EventDispatcher;

use Ksfraser\PluginSystem\Interfaces\PluginEventDispatcherInterface;

/**
 * FA Event Dispatcher Adapter
 *
 * Adapter to make FA's EventManager compatible with PluginEventDispatcherInterface
 */
class FAEventDispatcherAdapter implements PluginEventDispatcherInterface
{
    /**
     * Dispatch an event
     */
    public function dispatch(object $event): void
    {
        \FA\Services\EventManager::dispatchEvent($event);
    }

    /**
     * Add an event listener
     */
    public function on(string $eventName, callable $listener): void
    {
        \FA\Services\EventManager::on($eventName, $listener);
    }
}