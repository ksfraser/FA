<?php
declare(strict_types=1);

namespace Ksfraser\PluginSystem\Interfaces;

/**
 * Event Dispatcher Interface
 *
 * PSR-14 compliant event dispatcher interface for plugin system
 */
interface PluginEventDispatcherInterface
{
    /**
     * Dispatch an event
     *
     * @param object $event The event to dispatch
     */
    public function dispatch(object $event): void;

    /**
     * Add an event listener
     *
     * @param string $eventName The event name
     * @param callable $listener The listener callable
     */
    public function on(string $eventName, callable $listener): void;
}