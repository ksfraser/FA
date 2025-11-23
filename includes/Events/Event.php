<?php
declare(strict_types=1);

namespace FA\Events;

use FA\Contracts\EventInterface;

/**
 * Base Event class implementing PSR-14 EventInterface
 * All FA events should extend this class
 */
abstract class Event implements EventInterface
{
    private bool $propagationStopped = false;

    /**
     * Get the event name (defaults to class name)
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Check if event propagation should stop
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}