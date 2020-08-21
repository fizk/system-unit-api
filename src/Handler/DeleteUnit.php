<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, Unit};

class DeleteUnit implements RequestHandlerInterface, UnitAware
{
    private Unit $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->delete($request->getAttribute('unit_id'));
        return $response
            ? new EmptyResponse(204)
            : new EmptyResponse(404);
    }

    public function setUnitService(Unit $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
