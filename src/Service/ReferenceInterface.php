<?php

namespace Unit\Service;

interface ReferenceInterface
{
    public function get(string $unitId, string $refId): ?array;

    public function fetch(string $id, ?string $filter = null): array;

    public function put(string $id, array $data): int;

    public function post(string $id, array $data): string;
}
