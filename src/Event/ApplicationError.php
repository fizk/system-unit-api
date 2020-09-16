<?php

namespace Unit\Event;

use Psr\Http\Message\RequestInterface;
use Throwable;

class ApplicationError
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
        return $this->request;
    }

    public function __toString(): string
    {
        return json_encode([
            'url' => $this->request->getUri()->__toString(),
            'method' => $this->request->getMethod(),
            'status' => $this->response->getStatusCode(),
            'headers' => $this->request->getHeaders(),
            'error' => $this->method,
            'message' => $this->exception->getMessage(),
            'code' => $this->exception->getCode(),
            'path' => "{$this->exception->getFile()}:{$this->exception->getLine()}",
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine(),
            'trace' => $this->exception->getTrace(),
        ]);
    }
}
