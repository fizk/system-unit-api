<?php

namespace Unit\Handler;

use InvalidArgumentException;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, Unit};

class PatchUnit implements RequestHandlerInterface, UnitAware
{
    private Unit $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->patch($request->getAttribute('id'), $request->getParsedBody());
        return $response
            ? new EmptyResponse(204)
            : new EmptyResponse(400);
    }

    public function setUnitService(Unit $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
