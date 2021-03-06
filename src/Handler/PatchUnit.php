<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, UnitInterface};

class PatchUnit implements RequestHandlerInterface, UnitAware
{
    private UnitInterface $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->patch(
            $request->getAttribute('unit_id'),
            $request->getParsedBody()
        );
        return $response
            ? new EmptyResponse(204, ['Access-Control-Allow-Origin' => '*'])
            : new EmptyResponse(400, ['Access-Control-Allow-Origin' => '*']);
    }

    public function setUnitService(UnitInterface $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
