<?php

namespace Unit\Service;

interface UnitInterface
{
    public function get(string $id): ?array;

    public function fetch(?string $filter = null): array;

    public function fetchDiscrete(array $ids = []): array;

    public function put(string $id, array $data): int;

    public function patch(string $id, array $data): int;

    public function post(array $data): string;

    public function delete(string $id): int;
}
