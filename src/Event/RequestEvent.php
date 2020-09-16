<?php

namespace Unit\Event;

use Psr\Http\Message\{RequestInterface, ResponseInterface};

class RequestEvent
{
    private RequestInterface $request;
    private ResponseInterface $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function __toString(): string
    {
        return json_encode([
            'url' => $this->request->getUri()->__toString(),
            'method' => $this->request->getMethod(),
            'status' => $this->response->getStatusCode(),
            'headers' => $this->request->getHeaders()
        ]);
    }
}
