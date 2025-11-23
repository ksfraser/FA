<?php
declare(strict_types=1);

namespace FA\Contracts;

/**
 * PSR-14 Event Dispatcher Interface
 * Defines how events are dispatched to listeners
 */
interface EventDispatcherInterface
{
    /**
     * Dispatch an event to all registered listeners
     *
     * @param object $event The event to dispatch
     * @return object The event after processing
     */
    public function dispatch(object $event): object;
}