<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, Reference};

class DeleteReference implements RequestHandlerInterface, ReferenceAware
{
    private Reference $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    public function setReferenceService(Reference $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
