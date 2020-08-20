<?php

namespace Unit\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

interface EventDispatcherAware
{
    public function getEventDispatcher(): EventDispatcherInterface;
    public function setEventDispatcher(EventDispatcherInterface $eventDispatch): self;
}
