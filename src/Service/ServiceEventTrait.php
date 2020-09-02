<?php

namespace Unit\Service;

use Psr\EventDispatcher\EventDispatcherInterface;

trait ServiceEventTrait
{
    private ?EventDispatcherInterface $eventDispatch = null;

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatch ?: new class implements EventDispatcherInterface
        {
            public function dispatch(object $event)
            {
            }
        };
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatch): self
    {
        $this->eventDispatch = $eventDispatch;
        return $this;
    }
}
