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
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Unit API',
                'description' => 'The Unit API',
                'version' => '1.0.0'
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8081',
                    'description' => 'URL description'
                ]
            ],
            'paths' => [
                '/units' => [
                    'get' => [
                        'summary' => 'Get al Units',
                        'description' => 'Queries for all Unit in the database, '
                            .'additional filter can be provided to filter on MIME types',
                        'parameters' => [
                            [
                                'name' => 'filter',
                                'in' => 'query',
                                'description' => 'RegEx string',
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'ids',
                                'in' => 'query',
                                'description' => 'Comma sererated list of IDs to fetch',
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Returns an array of Units',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                '$ref' => '#/components/schemas/Unit'
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'post' => [
                        'summary' => 'Create new Unit',
                        'requestBody' => [
                            'description' => 'Requires the __mime type (and additional properties)',
                            'required' => true,
                            'content' => [
                                'application/x-www-form-urlencoded' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/UnitForm'
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            201 => [
                                'description' => 'Returns an empty response, with a HTTP Header Location property',
                                'headers' => [
                                    'Location' => [
                                        'description' => 'URL',
                                        'schema' => [
                                            'type' => 'string'
                                        ]
                                    ]
                                ]
                            ],
                            400 => [
                                'description' => 'Client side error',
                            ]
                        ]
                    ]
                ],
                '/units/{unit_id}' => [
                    'get' => [
                        'summary' => 'Get on Unit',
                        'description' => 'Queries for one Unit by unit_id',
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]

                            ]
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Returns one Unit',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/Unit'
                                        ]
                                    ]
                                ]
                            ],
                            404 => [
                                'description' => 'Unit not found',
                            ],
                        ]
                    ],
                    'put' => [
                        'summary' => 'Create or update',
                        'description' => 'Tries to update a Unit, but creates it if not found',
                        'requestBody' => [
                            'description' => 'Requires MIME type (and additional properties)',
                            'required' => true,
                            'content' => [
                                'application/x-www-form-urlencoded' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/UnitForm'
                                    ]
                                ]
                            ]
                        ],
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'responses' => [
                            204 => [
                                'description' => 'Updated',
                            ],
                            201 => [
                                'description' => 'Created',
                            ],
                            400 => [
                                'description' => 'Client error',
                            ],
                        ]
                    ],
                    'patch' => [
                        'summary' => 'Update Unit',
                        'description' => 'All fields are optional, only fields provided will be updated',
                        'requestBody' => [
                            'description' => 'All fields are optional',
                            'required' => true,
                            'content' => [
                                'application/x-www-form-urlencoded' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/UnitForm'
                                    ]
                                ]
                            ]
                        ],
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'responses' => [
                            204 => [
                                'description' => 'Updated',
                            ],
                            400 => [
                                'description' => 'Client error',
                            ],
                        ]
                    ],
                    'delete' => [
                        'summary' => 'Delete Unit',
                        'description' => 'Deletes on Unit by ID',
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'responses' => [
                            204 => [
                                'description' => 'Success',
                            ],
                            404 => [
                                'description' => 'Unit not found',
                            ],
                        ]
                    ],
                ],
                '/units/{unit_id}/references' => [
                    'post' => [
                        'summary' => 'Link Units together',
                        'description' => 'Parent Unit is unit_id, additional __unit param is required in the payload',
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]

                            ]
                        ],
                        'requestBody' => [
                            'description' => '__unit is the child Unit',
                            'required' => true,
                            'content' => [
                                'application/x-www-form-urlencoded' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ReferenceForm'
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            201 => [
                                'description' => 'Link created',
                                'headers' => [
                                    'Location' => [
                                        'description' => 'URL / location of the new reference',
                                        'schema' => [
                                            'type' => 'string'
                                        ]
                                    ]
                                ]
                            ],
                            400 => [
                                'description' => 'Client error',
                            ]
                        ]
                    ]
                ],
                '/units/{unit_id}/references/{ref_id}' => [
                    'get' => [
                        'summary' => 'Query for one reference',
                        'description' => 'Will return the reference object inside a Parent Unit',
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'ref_id',
                                'in' => 'path',
                                'description' => 'id of the Reference',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Reference found',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/Reference'
                                        ]
                                    ]
                                ]
                            ],
                            404 => [
                                'description' => 'Reference not found',
                            ],
                        ]
                    ],
                    'put' => [
                        'summary' => 'Update meta-data in Link / Reference',
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'ref_id',
                                'in' => 'path',
                                'description' => 'id of the Reference',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'requestBody' => [
                            'description' => '__unit and __mime are required',
                            'required' => true,
                            'content' => [
                                'application/x-www-form-urlencoded' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ReferenceForm'
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Created',
                            ],
                            400 => [
                                'description' => 'Client error',
                            ],
                        ]
                    ],
                    'delete' => [
                        'summary' => 'Delete one Reference / Link',
                        'description' => 'Currently not implemented',
                        'parameters' => [
                            [
                                'name' => 'unit_id',
                                'in' => 'path',
                                'description' => 'id of the Unit',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'ref_id',
                                'in' => 'path',
                                'description' => 'id of the Reference',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'responses' => [
                            403 => [
                                'description' => 'not allowed',
                            ],
                        ]
                    ],
                ],
                '/references/{ref_id}' => [
                    'get' => [
                        'summary' => 'Get Parent of a reference',
                        'description' => 'Find all Units that have a reference to `ref_id`.'.
                            ' Additnal filter can be provided',
                        'parameters' => [
                            [
                                'name' => 'ref_id',
                                'in' => 'path',
                                'description' => 'id of the Reference',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'filter',
                                'in' => 'query',
                                'description' => '__mime RegEx',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'description',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                '$ref' => '#/components/schemas/Unit'
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ]
                ],
            ],
            'components' => [
                'schemas' => [
                    'Unit' => [
                        'properties' => [
                            '_id' => [
                                'type' => 'string'
                            ],
                            '__mime' => [
                                'type' => 'string'
                            ],
                            '__ref' => [
                                'type' => 'array',
                                'items' => [
                                    '$ref' => '#/components/schemas/Reference'
                                ]
                            ],
                        ],
                        'additionalProperties' => true,
                    ],
                    'Reference' => [
                        'properties' => [
                            '_id' => [
                                'type' => 'string'
                            ],
                            '__unit' => [
                                'type' => 'string'
                            ],
                            '__mime' => [
                                'type' => 'string'
                            ],
                        ],
                        'additionalProperties' => true,
                    ],
                    'UnitForm' => [
                        'properties' => [
                            '__mime' => [
                                'type' => 'string'
                            ],
                        ],
                        'additionalProperties' => true,
                    ],
                    'ReferenceForm' => [
                        'properties' => [
                            '__unit' => [
                                'type' => 'string'
                            ],
                            '__mime' => [
                                'type' => 'string'
                            ],
                        ],
                        'additionalProperties' => true,
                    ],
                ]
            ]
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
