<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, ReferenceInterface};

class DeleteReference implements RequestHandlerInterface, ReferenceAware
{
    private ReferenceInterface $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    public function setReferenceService(ReferenceInterface $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
