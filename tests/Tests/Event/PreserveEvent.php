<?php

namespace Unit\Tests\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

class PreserveEvent implements EventDispatcherInterface {

    private ?object $lastEvent = null;

    public function dispatch(object $event) {
        $this->lastEvent = $event;
    }

    public function getLastEvent(): ?object
    {
        return $this->lastEvent;
    }
}
