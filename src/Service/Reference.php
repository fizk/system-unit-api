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
                array_merge(['__unit' => new ObjectId($id)], $filter ? ['__mime' => ['$regex' => "^{$filter}$"]] : [])
            ]
        ]);

        return array_map(function ($item) {
            return $this->serialize($item);
        }, $result->toArray());
    }

    public function put(string $id, array $data): int
    {
        if (!key_exists('__mime', $data)) {
            throw new InvalidArgumentException('Field "__mime" missing', 400);
        }

        if (!key_exists('__unit', $data)) {
            throw new InvalidArgumentException('Field "__unit" missing', 400);
        }

        if (!preg_match('/^([a-z]*)\/([a-z]*)(\+[a-z]*)?$/', $data['__mime'])) {
            throw new InvalidArgumentException("Invalid mime type \"${$data['__mime']}\"", 400);
        }

        $result = $this->getDriver()->selectCollection('unit')->updateOne(
            ['__ref._id' => new ObjectId($id)],
            [
                '$set' => ['__ref.$[filter]' => array_merge(
                    $data,
                    ['_id' => new ObjectId($id), '__unit' => new ObjectId($data['__unit'])]
                )]
            ],
            ['arrayFilters' => [[ "filter._id" => new ObjectId($id) ]]]
        );

        return $result->isAcknowledged()
            ? $result->getModifiedCount()
            : -1 ;
    }

    public function post(string $id, array $data): string
    {
        if (!key_exists('__mime', $data)) {
            throw new InvalidArgumentException('Field "__mime" missing', 400);
        }

        if (!key_exists('__unit', $data)) {
            throw new InvalidArgumentException('Field "__unit" missing', 400);
        }

        if (!preg_match('/^([a-z]*)\/([a-z]*)(\+[a-z]*)?$/', $data['__mime'])) {
            throw new InvalidArgumentException("Invalid mime type \"${$data['__mime']}\"", 400);
        }

        $identity = new ObjectId();

        $this->getDriver()->selectCollection('unit')->findOneAndUpdate([
            '_id' => new ObjectId($id)
        ], [
            '$addToSet' => [
                '__ref' => array_merge($data, ['_id' => $identity, '__unit' => new ObjectId($data['__unit'])])
            ]
        ]);

        return (string) $identity;
    }
}
