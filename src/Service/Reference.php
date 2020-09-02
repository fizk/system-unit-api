<?php

namespace Unit\Service;

use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use Unit\Event\EventDispatcherAware;
use Unit\Event\UnitUpdateEvent;
use Unit\Event\UnitViewEvent;
use Unit\Service\DatabaseAware;
use Unit\Service\ServiceDatabaseTrait;
use Unit\Service\ReferenceInterface;

class Reference implements ReferenceInterface, DatabaseAware, EventDispatcherAware
{
    use ServiceDatabaseTrait;
    use ServiceEventTrait;

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
        count($response) && $this->getEventDispatcher()->dispatch(new UnitViewEvent([$unitId]));
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

        $units = array_map(function ($item) {
            return $this->serialize($item);
        }, $result->toArray());

        $unitIds = array_map(function ($unit) {
            return $unit['_id'];
        }, $units);

        $this->getEventDispatcher()->dispatch(new UnitViewEvent($unitIds));

        return $units;
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

        $result = $this->getDriver()->selectCollection('unit')->findOneAndUpdate(
            ['__ref._id' => new ObjectId($id)],
            [
                '$set' => ['__ref.$[filter]' => array_merge(
                    $data,
                    ['_id' => new ObjectId($id), '__unit' => new ObjectId($data['__unit'])]
                )]
            ],
            ['arrayFilters' => [[ "filter._id" => new ObjectId($id) ]]]
        );
        $result && $this->getEventDispatcher()
            ->dispatch(new UnitUpdateEvent((string) $result->getArrayCopy()['_id']));

        return $result
            ? 1
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

        $response = $this->getDriver()->selectCollection('unit')->findOneAndUpdate([
            '_id' => new ObjectId($id)
        ], [
            '$addToSet' => [
                '__ref' => array_merge($data, ['_id' => $identity, '__unit' => new ObjectId($data['__unit'])])
            ]
        ]);

        $response && $this->getEventDispatcher()->dispatch(new UnitUpdateEvent($id));

        return (string) $identity;
    }
}
