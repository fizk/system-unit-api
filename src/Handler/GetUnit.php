<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, Unit};

class GetUnit implements RequestHandlerInterface, UnitAware
{
    private Unit $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->get($request->getAttribute('id'));
        return $response
            ? new JsonResponse($response, 200)
            : new EmptyResponse(404) ;
    }

    public function setUnitService(Unit $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
