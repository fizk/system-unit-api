<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, Reference};

class GetFromReference implements RequestHandlerInterface, ReferenceAware
{
    private Reference $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->referenceService->fetch($request->getAttribute('id'));
        return $response
            ? new JsonResponse($response, 200)
            : new EmptyResponse(404);
    }

    public function setReferenceService(Reference $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
