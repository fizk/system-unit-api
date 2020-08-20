<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

class Index implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'status' => 'ok',
            'endpoints' => [
                '/units' => [
                    'params' => [],
                    'query' => [
                        'filter' => 'mime regex',
                        'ids' => 'comma seperated list of IDs',
                    ],
                    'get' => [
                        'request' => [],
                        'response' => [
                            200 => 'array of @Unit'
                        ],
                    ],
                    'post' => [
                        'request' => [],
                        'response' => [
                            201 => 'created',
                            400 => 'error',
                            'headers' => [
                                'Location' => '@string'
                            ]
                        ],
                    ],
                ],
                '/units/{id}' => [
                    'params' => [
                        'id' => '@string'
                    ],
                    'get' => [
                        'request' => [],
                        'response' => [
                            200 => '@Unit',
                            404 => 'unit not found',
                        ],
                    ],
                    'put' => [
                        'request' => [
                            '__mime' => '@string',
                            '...' => 'data fields'
                        ],
                        'response' => [
                            204 => 'update',
                            201 => 'create',
                            400 => 'error',
                        ],
                    ],
                    'patch' => [
                        'request' => [
                            '...' => 'data fields'
                        ],
                        'response' => [
                            204 => 'update',
                            400 => 'error',
                        ],
                    ],
                    'delete' => [
                        'request' => [],
                        'response' => [
                            204 => 'delete',
                            404 => 'unit not found',
                        ],
                    ],
                ],
            ],
            'models' => [
                'Unit' => [
                    '_id' => '@string',
                    '__ref' => '@array',
                    '__mime' => '@string',
                    '...' => 'data fields'
                ]
            ]
        ]);
    }
}
