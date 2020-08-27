<?php

namespace Unit\Service;

use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use Unit\Service\ServiceDatabaseTrait;
use Unit\Service\DatabaseAware;
use Unit\Service\UnitInterface;

class Unit implements DatabaseAware, UnitInterface
{
    use ServiceDatabaseTrait;

    /**
     * @return array|null data
     */
    public function get(string $id): ?array
    {
        $result = $this->getDriver()->selectCollection('unit')->findOne([
            '_id' => new ObjectId($id)
        ]);

        return $result
            ? $this->serialize($result)
            : null;
    }

    public function fetch(?string $filter = null): array
    {
        $request = $filter
            ? ['__mime' => ['$regex' => "^{$filter}$"]]
            : [];

        return array_map(function ($item) {
            return $this->serialize($item);
        }, $this->getDriver()->selectCollection('unit')->find($request)->toArray());
    }

    public function fetchDiscrete(array $ids = []): array
    {
        $request = ['$or' => array_map(function ($id) {
            return ['_id' => new ObjectId($id)];
        }, $ids)];

        $cursor = $this->getDriver()
            ->selectCollection('unit')
            ->find($request)
            ->toArray();

        return array_map(function ($item) use ($cursor) {
            foreach ($cursor as $i) {
                if ((string)$i['_id'] === $item) {
                    return $this->serialize($i);
                }
            }
            return null;
        }, $ids);
    }

    /**
     * @return int  1 created
     * @return int  0 updated
     * @return int -1 error
     * @throws \InvalidArgumentException MIME missing
     */
    public function put(string $id, array $data): int
    {
        if (!key_exists('__mime', $data)) {
            throw new InvalidArgumentException('Field "__mime" missing', 400);
        }

        if (!preg_match('/^([a-z]*)\/([a-z]*)(\+[a-z]*)?$/', $data['__mime'])) {
            throw new InvalidArgumentException("Invalid mime type \"${$data['__mime']}\"", 400);
        }

        $result = $this->getDriver()->selectCollection('unit')->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => !key_exists('__ref', $data) ? array_merge($data, ['__ref' => []]) : $data],
            ['upsert' => true]
        );

        return $result->isAcknowledged()
            ? $result->getUpsertedCount()
            : -1;
    }

    /**
     * @return int  1 update
     * @return int  0 no update
     * @return int -1 error
     */
    public function patch(string $id, array $data): int
    {
        $result = $this->getDriver()->selectCollection('unit')->updateOne([
            '_id' => new ObjectId($id)
        ], [
            '$set' => $data
        ]);

        return $result->isAcknowledged()
            ? $result->getModifiedCount()
            : -1;
    }

    /**
     * @return string ID
     * @throws \InvalidArgumentException MIME missing
     */
    public function post(array $data): string
    {
        if (!key_exists('__mime', $data)) {
            throw new InvalidArgumentException('Field "__mime" missing', 400);
        }

        if (!preg_match('/^([a-z]*)\/([a-z]*)(\+[a-z]*)?$/', $data['__mime'])) {
            throw new InvalidArgumentException("Invalid mime type \"${$data['__mime']}\"", 400);
        }

        $response = $this->getDriver()->selectCollection('unit')->insertOne(
            array_merge($data, ['__ref' => []])
        );
        return (string) $response->getInsertedId();
    }

    /**
     * @return int  1 deleted
     * @return int  0 not deleted
     * @return int -1 error
     * @todo delete references as well.
     */
    public function delete(string $id): int
    {
        $result = $this->getDriver()->selectCollection('unit')->deleteOne([
            '_id' => new ObjectId($id)
        ]);

        return $result->isAcknowledged()
            ? $result->getDeletedCount()
            : -1;
    }
}
