<?php

namespace Unit\Tests\Service;

use Unit\Service\UnitInterface;

abstract class AbstractUnit implements UnitInterface {
    public function get(string $id): ?array
    {
        return [];
    }

    public function fetch(?string $filter = null): array
    {
        return [];
    }

    public function fetchDiscrete(array $ids = []): array
    {
        return [];
    }

    public function put(string $id, array $data): int
    {
        return 0;
    }

    public function patch(string $id, array $data): int
    {
        return 0;
    }

    public function post(array $data): string
    {
        return '0';
    }

    public function delete(string $id): int
    {
        return 0;
    }
}
