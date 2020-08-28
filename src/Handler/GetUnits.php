<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Unit\Service\{UnitAware, UnitInterface};

class GetUnits implements RequestHandlerInterface, UnitAware
{
    private UnitInterface $unitService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        if (key_exists('ids', $queryParams)) {
            $idArray = array_map(function ($id) {
                return trim($id);
            }, explode(',', $queryParams['ids']));
            $response = $this->unitService->fetchDiscrete($idArray);

            return new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
        }

        if (key_exists('filter', $queryParams)) {
            $response = $this->unitService->fetch($queryParams['filter']);

            return new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
        }

        $response = $this->unitService->fetch();
        return new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
    }

    public function setUnitService(UnitInterface $service): self
    {
        $this->unitService = $service;
        return $this;
    }
}
