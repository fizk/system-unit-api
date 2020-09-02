<?php

namespace Unit\Service;

use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use Unit\Event\EventDispatcherAware;
use Unit\Event\UnitCreateEvent;
use Unit\Event\UnitDeleteEvent;
use Unit\Event\UnitUpdateEvent;
use Unit\Event\UnitViewEvent;
use Unit\Service\ServiceDatabaseTrait;
use Unit\Service\DatabaseAware;
use Unit\Service\UnitInterface;

class Unit implements UnitInterface, DatabaseAware, EventDispatcherAware
{
    use ServiceDatabaseTrait;
    use ServiceEventTrait;

    /**
     * @return array|null data
     */
    public function get(string $id): ?array
    {
        $result = $this->getDriver()->selectCollection('unit')->findOne([
            '_id' => new ObjectId($id)
        ]);

        $result && $this->getEventDispatcher()->dispatch(new UnitViewEvent([$id]));

        return $result
            ? $this->serialize($result)
            : null;
    }

    public function fetch(?string $filter = null): array
    {
        $request = $filter
            ? ['__mime' => ['$regex' => "^{$filter}$"]]
            : [];

        $this->getEventDispatcher()->dispatch(new UnitViewEvent([], $filter));

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

        $this->getEventDispatcher()->dispatch(new UnitViewEvent($ids));

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

        ($result->isAcknowledged() && $result->getUpsertedCount() !== 0) &&
            $this->getEventDispatcher()->dispatch(new UnitCreateEvent($id));
        ($result->isAcknowledged() && $result->getModifiedCount() !== 0) &&
            $this->getEventDispatcher()->dispatch(new UnitUpdateEvent($id));

        if ($result->isAcknowledged()) {
            if ($result->getUpsertedCount()) {
                return 1;
            }
            if ($result->getModifiedCount()) {
                return 0;
            }
            return -1;
        }
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

        ($result->isAcknowledged() && $result->getModifiedCount()) &&
            $this->getEventDispatcher()->dispatch(new UnitUpdateEvent($id));

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

        $this->getEventDispatcher()
            ->dispatch(new UnitCreateEvent($response->getInsertedId()));

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

        ($result->isAcknowledged() && $result->getDeletedCount()) &&
            $this->getEventDispatcher()->dispatch(new UnitDeleteEvent($id));

        return $result->isAcknowledged()
            ? $result->getDeletedCount()
            : -1;
    }
}
