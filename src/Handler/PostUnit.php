<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, UnitInterface};

class PostUnit implements RequestHandlerInterface, UnitAware
{
    private UnitInterface $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->unitService->post($request->getParsedBody());
        return $response
            ? (new EmptyResponse(201, [
                'Access-Control-Allow-Origin' => '*',
                'Location' => "/units/{$response}"
            ]))
            : new EmptyResponse(400);
    }

    public function setUnitService(UnitInterface $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
