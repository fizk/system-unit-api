<?php

namespace Unit\Response;

use Laminas\Diactoros\Response\JsonResponse;
use Throwable;

class ErrorJsonResponse extends JsonResponse
{
    public function __construct(Throwable $error, int $status = 500, array $headers = [])
    {
        parent::__construct([
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'path' => "{$error->getFile()}:{$error->getLine()}",
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTrace()
        ], $status, $headers);
    }
}
