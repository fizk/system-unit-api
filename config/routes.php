<?php

return [
    '/' => [
        'GET' => Unit\Handler\Index::class
    ],
    '/units' => [
        'POST' => Unit\Handler\PostUnit::class,
        'GET' => Unit\Handler\GetUnits::class,
    ],
    '/units/{unit_id}' => [
        'GET' => Unit\Handler\GetUnit::class,
        'PUT' => Unit\Handler\PutUnit::class,
        'DELETE' => Unit\Handler\DeleteUnit::class,
        'PATCH' => Unit\Handler\PatchUnit::class,
    ],
    '/units/{unit_id}/references' => [
        'POST' => Unit\Handler\PostReference::class,
    ],
    '/units/{unit_id}/references/{ref_id}' => [
        'PUT' => Unit\Handler\PutReference::class,
        'DELETE' => Unit\Handler\DeleteReference::class,
    ],
    '/references/{ref_id}' => [
        'GET' => Unit\Handler\GetFromReference::class,

    ]
];
