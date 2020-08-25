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
                    'get' => [
                        'query' => [
                            'filter' => 'mime regex',
                            'ids' => 'comma seperated list of IDs',
                        ],
                        'request' => [],
                        'response' => [
                            200 => '@Unit[]'
                        ],
                    ],
                    'post' => [
                        'request' => [],
                        'response' => [
                            201 => 'created',
                            400 => 'error',
                            'headers' => [
                                'Location' => '@url'
                            ]
                        ],
                    ],
                ],
                '/units/{unit_id}' => [
                    'params' => [
                        'unit_id' => '@string'
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
                '/units/{unit_id}/references' => [
                    'params' => [
                        'unit_id' => '@string'
                    ],
                    'post' => [
                        'request' => [
                            '__unit' => '@string',
                            '__mime' => '@string',
                            '...' => 'data fields'
                        ],
                        'response' => [
                            'headers' => [
                                'Location' => '@url'
                            ],
                            201 => 'created',
                            400 => 'error',
                        ],
                    ]
                ],
                '/units/{unit_id}/references/{ref_id}' => [
                    'params' => [
                        'unit_id' => '@string',
                        'ref_id' => '@string',
                    ],
                    'put' => [
                        'request' => [
                            '__unit' => '@string',
                            '__mime' => '@string',
                            '...' => 'data fields'
                        ],
                        'response' => [
                            200 => 'success',
                            400 => 'error',
                        ],
                    ],
                    'get' => [
                        'request' => [],
                        'response' => [
                            200 => '@Reference',
                            404 => 'unit not found',
                        ],
                    ],
                    'delete' => [
                        'request' => [],
                        'response' => [
                            405 => 'not allowed',
                        ],
                    ]
                ],
                '/references/{ref_id}' => [
                    'params' => [
                        'ref_id' => '@string'
                    ],
                    'get' => [
                        'query' => [
                            'filter' => 'mime regex',
                        ],
                        'request' => [],
                        'response' => [
                            200 => '@Unit[]'
                        ],
                    ]
                ]
            ],
            'models' => [
                'Unit' => [
                    '_id' => '@string',
                    '__ref' => '@Reference[]',
                    '__mime' => '@string',
                    '...' => 'data fields'
                ],
                'Reference' => [
                    '_id' => '@string',
                    '__unit' => '@string',
                    '__mime' => '@string',
                    '...' => 'data fields'
                ]
            ]
        ]);
    }
}
