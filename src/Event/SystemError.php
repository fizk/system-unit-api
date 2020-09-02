<?php

namespace Unit\Event;

use Psr\Http\Message\RequestInterface;
use Throwable;

class SystemError
{
    private Throwable $exception;
    private string $method;
    private RequestInterface $request;

    public function __construct(RequestInterface $request, Throwable $exception, ?string $method = null)
    {
        $this->exception = $exception;
        $this->method = $method;
        $this->request = $request;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request
    }

    public function __toString(): string
    {
        return "{$this->method} - {$this->exception->getMessage()} ".
              "{$this->exception->getFile()}:{$this->exception->getLine()}" .
              "{$this->request->getUri()->__toString()}"
    }
}
