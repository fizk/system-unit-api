<?php

return [
    '/' => [
        'GET' => Unit\Handler\Index::class
    ],
    '/units' => [
        'POST' => Unit\Handler\PostUnit::class,
        'GET' => Unit\Handler\GetUnits::class,
    ],
    '/units/{id}' => [
        'GET' => Unit\Handler\GetUnit::class,
        'PUT' => Unit\Handler\PutUnit::class,
        'DELETE' => Unit\Handler\DeleteUnit::class,
        'PATCH' => Unit\Handler\PatchUnit::class,
    ],
    '/references/{id}' => [
        'GET' => Unit\Handler\GetFromReference::class,
        'PUT' => Unit\Handler\PutReference::class,
    ]
];
