<?php

namespace Unit\Service;

use Unit\Service\DatabaseAware;
use MongoDB\BSON\ObjectId;
use Unit\Service\ServiceDatabaseTrait;
use InvalidArgumentException;

class Reference implements DatabaseAware
{
    use ServiceDatabaseTrait;

    public function fetch(string $id, ?string $filter = null): array
    {
        $result = $this->getDriver()->selectCollection('unit')->find([
            '__ref' => ['$elemMatch' =>
                array_merge(['_id' => new ObjectId($id)], $filter ? ['__mime' => ['$regex' => "^{$filter}$"]] : [])
            ]
        ]);

        return array_map(function ($item) {
            return $this->serialize($item);
        }, $result->toArray());
    }

    public function put(string $id, array $data)
    {
        if (!key_exists('__mime', $data)) {
            throw new InvalidArgumentException('Field "__mime" missing', 400);
        }

        if (!key_exists('_id', $data)) {
            throw new InvalidArgumentException('Field "_id" missing', 400);
        }

        if (!preg_match('/^([a-z]*)\/([a-z]*)(\+[a-z]*)?$/', $data['__mime'])) {
            throw new InvalidArgumentException("Invalid mime type \"${$data['__mime']}\"", 400);
        }

        $this->getDriver()->selectCollection('unit')->findOneAndUpdate([
            '_id' => new ObjectId($id)
        ], [
            '$addToSet' => ['__ref' => array_merge($data, ['_id' => new ObjectId($data['_id'])])]
        ]);
    }
}
