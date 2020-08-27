<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, ReferenceInterface};

class GetReference implements RequestHandlerInterface, ReferenceAware
{
    private ReferenceInterface $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->referenceService->get(
            $request->getAttribute('unit_id'),
            $request->getAttribute('ref_id')
        );

        return $response
            ? new JsonResponse($response, 200)
            : new EmptyResponse(404);
    }

    public function setReferenceService(ReferenceInterface $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
