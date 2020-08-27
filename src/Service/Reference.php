<?php

namespace Unit\Service;

use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use Unit\Service\DatabaseAware;
use Unit\Service\ServiceDatabaseTrait;
use Unit\Service\ReferenceInterface;

class Reference implements DatabaseAware, ReferenceInterface
{
    use ServiceDatabaseTrait;

    public function get(string $unitId, string $refId): ?array
    {

        $result = $this->getDriver()->selectCollection('unit')->aggregate([
            ['$match' => ['_id' => new ObjectId($unitId)]],
            ['$project' => [
                'shapes' => ['$filter' => [
                    'input' => '$__ref',
                    'as' => 'shape',
                    'cond' => ['$eq' => ['$$shape._id', new ObjectId($refId)]]
                ]],
                '_id' => 0
            ]]
        ]);

        $response = $result->toArray();
        return count($response)
            ? $this->serializeReference($response[0]->getArrayCopy()['shapes']->getArrayCopy()[0])
            : null ;
    }

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
