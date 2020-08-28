<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, UnitInterface};

class PutUnit implements RequestHandlerInterface, UnitAware
{
    private UnitInterface $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->put(
            $request->getAttribute('unit_id'),
            $request->getParsedBody()
        );

        switch ($response) {
            case -1:
                return new EmptyResponse(400, ['Access-Control-Allow-Origin' => '*']);
            case 0:
                return new EmptyResponse(204, ['Access-Control-Allow-Origin' => '*']);
            case 1:
                return new EmptyResponse(201, ['Access-Control-Allow-Origin' => '*']);
        }
    }

    public function setUnitService(UnitInterface $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
