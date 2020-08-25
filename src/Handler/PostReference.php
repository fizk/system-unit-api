<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{ReferenceAware, Reference};

class PostReference implements RequestHandlerInterface, ReferenceAware
{
    private Reference $referenceService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->referenceService->post(
            $request->getAttribute('unit_id'),
            $request->getParsedBody()
        );

        return (new EmptyResponse(
            201,
            ['Location' => "/units/{$request->getAttribute('unit_id')}/references/{$response}"]
        ));
    }

    public function setReferenceService(Reference $service): self
    {
        $this->referenceService = $service;
        return $this;
    }
}
