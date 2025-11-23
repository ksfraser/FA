<?php
declare(strict_types=1);

namespace FA\Contracts;

/**
 * PSR-14 Listener Provider Interface
 * Provides listeners for specific events
 */
interface ListenerProviderInterface
{
    /**
     * Get all listeners for a given event
     *
     * @param object $event The event to get listeners for
     * @return iterable<callable> List of listener callables
     */
    public function getListenersForEvent(object $event): iterable;
}