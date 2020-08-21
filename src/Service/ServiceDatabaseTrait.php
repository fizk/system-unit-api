<?php

namespace Unit\Service;

use MongoDB\Database;
use MongoDB\Model\BSONDocument;

trait ServiceDatabaseTrait
{
    private Database $driver;

    public function setDriver(Database $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    public function getDriver(): Database
    {
        return $this->driver;
    }

    protected function serialize(BSONDocument $item): array
    {
        return array_merge($item->getArrayCopy(), [
            '_id' => (string)$item->getArrayCopy()['_id'],
            '__ref' => array_map(function ($item) {
                return array_merge($item->getArrayCopy(), [
                    '__unit' => (string) $item->getArrayCopy()['__unit'],
                    '_id' => (string) $item->getArrayCopy()['_id'],
                    ]);
            }, (array) $item->getArrayCopy()['__ref']),
        ]);
    }
}
