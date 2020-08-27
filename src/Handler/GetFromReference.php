<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, ReferenceInterface};

class GetFromReference implements RequestHandlerInterface, ReferenceAware
{
    private ReferenceInterface $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->referenceService->fetch(
            $request->getAttribute('ref_id'),
            key_exists('filter', $request->getQueryParams()) ? $request->getQueryParams()['filter'] : null
        );
        return new JsonResponse($response, 200);
    }

    public function setReferenceService(ReferenceInterface $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
