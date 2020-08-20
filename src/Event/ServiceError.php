<?php

namespace Unit\Event;

use Throwable;

class ServiceError
{
    private ?Throwable $exception;
    private string $method;

    public function __construct(?Throwable $exception = null, ?string $method = null)
    {
        $this->exception = $exception;
        $this->method = $method;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function __toString(): string
    {
        return $this->exception
            ? "{$this->method} - {$this->exception->getMessage()}"
            : "{$this->method} - Unknown error";
    }
}
