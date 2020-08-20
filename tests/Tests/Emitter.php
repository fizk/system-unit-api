<?php

namespace Unit\Tests;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Psr\Http\Message\ResponseInterface;

class Emitter implements EmitterInterface
{
    private ?ResponseInterface $response = null;

    public function emit(ResponseInterface $response): bool
    {
        $this->response = $response;
        return true;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
