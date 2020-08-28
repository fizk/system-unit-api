<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, ReferenceInterface};

class PutReference implements RequestHandlerInterface, ReferenceAware
{
    private ReferenceInterface $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->referenceService->put(
            $request->getAttribute('ref_id'),
            array_merge(
                $request->getParsedBody(),
                ['__unit' => $request->getAttribute('unit_id')]
            )
        );

        switch ($response) {
            case -1:
                return new EmptyResponse(400, ['Access-Control-Allow-Origin' => '*']);
            case 0:
                return new EmptyResponse(200, ['Access-Control-Allow-Origin' => '*']);
            case 1:
                return new EmptyResponse(204, ['Access-Control-Allow-Origin' => '*']);
        }
    }

    public function setReferenceService(ReferenceInterface $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
