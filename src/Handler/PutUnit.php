<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, Unit};

class PutUnit implements RequestHandlerInterface, UnitAware
{
    private Unit $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->put(
            $request->getAttribute('unit_id'),
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

    public function setUnitService(Unit $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
