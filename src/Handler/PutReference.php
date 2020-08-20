<?php

namespace Unit\Handler;

use InvalidArgumentException;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, Reference};

class PutReference implements RequestHandlerInterface, ReferenceAware
{
    private Reference $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->referenceService->put(
            $request->getAttribute('id'),
            $request->getParsedBody()
        );

        switch ($response) {
            case -1:
                return new EmptyResponse(400);
            case 0:
                return new EmptyResponse(204);
            case 1:
                return new EmptyResponse(201);
        }
    }

    public function setReferenceService(Reference $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
