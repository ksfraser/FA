<?php
declare(strict_types=1);

namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\Services\EventManager;
use FA\Events\Event;

/**
 * Test EventManager functionality
 */
class EventManagerTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the singleton instance for each test
        $reflection = new \ReflectionClass(EventManager::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    public function testDispatchEvent()
    {
        $event = new class extends Event {
            public $handled = false;
        };

        $listener = function($receivedEvent) {
            $receivedEvent->handled = true;
        };

        EventManager::on(get_class($event), $listener);

        $result = EventManager::dispatchEvent($event);

        $this->assertTrue($event->handled);
        $this->assertSame($event, $result);
    }

    public function testEventPropagationStop()
    {
        $event = new class extends Event {
            public $callCount = 0;
        };

        $listener1 = function($receivedEvent) {
            $receivedEvent->callCount++;
            $receivedEvent->stopPropagation();
        };

        $listener2 = function($receivedEvent) {
            $receivedEvent->callCount++;
        };

        EventManager::on(get_class($event), $listener1, 10); // Higher priority
        EventManager::on(get_class($event), $listener2, 5);  // Lower priority

        EventManager::dispatchEvent($event);

        $this->assertEquals(1, $event->callCount); // Only first listener should be called
    }

    public function testRemoveListener()
    {
        $event = new class extends Event {
            public bool $handled = false;
        };

        $listener = function($receivedEvent) {
            $receivedEvent->handled = true;
        };

        EventManager::on(get_class($event), $listener);
        EventManager::off(get_class($event), $listener);

        EventManager::dispatchEvent($event);

        $this->assertFalse($event->handled);
    }

    public function testStaticAndInstanceMethods()
    {
        $event = new class extends Event {
            public $staticHandled = false;
            public $instanceHandled = false;
        };

        $staticListener = function($receivedEvent) {
            $receivedEvent->staticHandled = true;
        };

        $instanceListener = function($receivedEvent) {
            $receivedEvent->instanceHandled = true;
        };

        // Test static method
        EventManager::on(get_class($event), $staticListener);
        EventManager::dispatchEvent($event);
        $this->assertTrue($event->staticHandled);

        // Reset
        $event->staticHandled = false;
        $event->instanceHandled = false;

        // Test instance method
        $manager = EventManager::getInstance();
        $manager->addListener(get_class($event), $instanceListener);
        $manager->dispatch($event);
        $this->assertTrue($event->instanceHandled);
    }
}