<?php

namespace Unit\Event;

class UnitViewEvent
{
    private array $id = [];
    private ?string $condition = null;

    public function __construct(array $id, ?string $condition = null)
    {
        $this->id = $id;
        $this->condition = $condition;
    }

    public function __toString(): string
    {
        return implode(', ', $this->id) . $this->condition;
    }
}
