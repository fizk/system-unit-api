<?php

namespace Unit\Service;

use PHPUnit\Framework\TestCase;
use MongoDB\Database;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Unit\Event\UnitCreateEvent;
use Unit\Event\UnitDeleteEvent;
use Unit\Event\UnitUpdateEvent;
use Unit\Event\UnitViewEvent;
use Unit\Tests\Event\PreserveEvent;

class UnitTest extends TestCase
{
    private ?Database $client;

    public function testGetSuccess()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' => 'value', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
            ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $expected = [
            '_id' => '5f3c539b711e4cc306ac2b87',
            'field' => 'value',
            '__ref' => []
        ];
        $actual = $service->get('5f3c539b711e4cc306ac2b87');

        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(UnitViewEvent::class, $events->getLastEvent());
    }

    public function testGetNotFound()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
            ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $expected = null;
        $actual = $service->get('5f3c539b711e4cc306ac2123');

        $this->assertEquals($expected, $actual);
        $this->assertNull($events->getLastEvent());
    }

    public function testPutCreate()
    {
        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $expected = 1;
        $actual = $service->put(
            '5f3c539b711e4cc306ac2123',
            ['filed' => 'value', '__mime' => 'some/mime']
        );

        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(UnitCreateEvent::class, $events->getLastEvent());
    }

    public function testPutMimeMissing()
    {
        $service = (new Unit())->setDriver($this->client);
        $this->expectException(\InvalidArgumentException::class);
        $service->put(
            '5f3c539b711e4cc306ac2123',
            ['some' => 'value']
        );
    }

    public function testPutUpdate()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' => 'value', '__mime' =>'some/mime', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $expected = 0;
        $actual = $service->put('5f3c539b711e4cc306ac2123', ['filed' =>'new value', '__mime' => 'some/mime']);

        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(UnitUpdateEvent::class, $events->getLastEvent());
    }

    public function testPutUpdateNoChange()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $expected = 0;
        $actual = $service->put('5f3c539b711e4cc306ac2123', ['filed' =>'value', '__mime' => 'some/mime']);

        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(UnitUpdateEvent::class, $events->getLastEvent());
    }

    public function testPatchAddField()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ]);
        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['new_filed' => 'new-value']);

        $result = $this->client->selectCollection('unit')
            ->findOne(['_id' => new ObjectId('5f3c539b711e4cc306ac2123')]);

        $this->assertArrayHasKey('field', $result->getArrayCopy());
        $this->assertArrayHasKey('new_filed', $result->getArrayCopy());
        $this->assertEquals('new-value', $result->getArrayCopy()['new_filed']);

        $this->assertEquals(1, $writeResult);
        $this->assertInstanceOf(UnitUpdateEvent::class, $events->getLastEvent());
    }

    public function testPatchUpdateField()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ]);
        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['field' => 'new value']);

        $result = $this->client->selectCollection('unit')
            ->findOne(['_id' => new ObjectId('5f3c539b711e4cc306ac2123')]);

        $this->assertEquals('new value', $result->getArrayCopy()['field']);
        $this->assertEquals(1, $writeResult);
        $this->assertInstanceOf(UnitUpdateEvent::class, $events->getLastEvent());
    }

    public function testPatchNoUpdate()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ]);
        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['field' => 'value']);
        $this->assertEquals(0, $writeResult);
        $this->assertNull($events->getLastEvent());
    }

    public function testPatchUnitNotFound()
    {
        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['field' => 'value']);

        $expected = [];
        $actual = $this->client->selectCollection('unit')->find([])->toArray();

        $this->assertEquals($expected, $actual);
        $this->assertEquals(0, $writeResult);
        $this->assertNull($events->getLastEvent());
    }

    public function testPost()
    {
        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $expected = 1;
        $id = $service->post(['filed' => 'value', '__mime' => 'some/mime']);
        $actual = $this->client->selectCollection('unit')->find([])->toArray();

        $this->assertCount($expected, $actual);
        $this->assertMatchesRegularExpression('/^[a-f\d]{24}$/i', $id);
        $this->assertInstanceOf(UnitCreateEvent::class, $events->getLastEvent());
    }

    public function testPostMissingMime()
    {
        $this->expectException(\InvalidArgumentException::class);
        $service = (new Unit())->setDriver($this->client);
        $service->post(['filed' => 'value']);
    }

    public function testDelete()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);
        $result = $service->delete('5f3c539b711e4cc306ac2b87');

        $this->assertCount(1, $this->client->selectCollection('unit')->find());
        $this->assertEquals(1, $result);
        $this->assertInstanceOf(UnitDeleteEvent::class, $events->getLastEvent());
    }

    public function testDeleteNotFound()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);
        $result = $service->delete('5f3c539b711e4cc306ac2b10');

        $this->assertCount(2, $this->client->selectCollection('unit')->find());
        $this->assertEquals(0, $result);
        $this->assertNull($events->getLastEvent());
    }

    public function testFetch()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);
        $result = $service->fetch();

        $this->assertCount(2, $this->client->selectCollection('unit')->find());
        $this->assertInstanceOf(UnitViewEvent::class, $events->getLastEvent());
    }

    public function testFetchFilter()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), '__mime' =>'album/album', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c8916'), '__mime' =>'album/album+single', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c8915'), '__mime' =>'artist/artist', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c8914'), '__mime' =>'artist/producer', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c8913'), '__mime' =>'artist/producer+mixing', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $this->assertCount(2, $service->fetch('album.*'));
        $this->assertCount(1, $service->fetch('album/album'));
        $this->assertCount(1, $service->fetch('album/.*single'));
        $this->assertCount(1, $service->fetch('album.*single'));
        $this->assertCount(2, $service->fetch('album/.*'));

        $this->assertCount(3, $service->fetch('artist/.*'));
        $this->assertCount(2, $service->fetch('artist/producer.*'));
        $this->assertCount(1, $service->fetch('artist/.*mixing'));

        $this->assertInstanceOf(UnitViewEvent::class, $events->getLastEvent());
    }

    public function testFetchDiscrete()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), '__mime' =>'album/album', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8916'), '__mime' =>'album/album+single', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8915'), '__mime' =>'artist/artist', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8914'), '__mime' =>'artist/producer', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8913'), '__mime' =>'artist/producer+mixing', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $actual = $service->fetchDiscrete(['5f3c539b711e4cc306ac2b87', '5f3c53af4fb5eebf643c8914']);
        $expected = [
            ['_id' => '5f3c539b711e4cc306ac2b87', '__mime' =>'album/album', '__ref' => []],
            ['_id' => '5f3c53af4fb5eebf643c8914', '__mime' =>'artist/producer', '__ref' => []],
        ];
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(UnitViewEvent::class, $events->getLastEvent());
    }

    public function testFetchDiscreteWithAGaps()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), '__mime' =>'album/album', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8916'), '__mime' =>'album/album+single', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8915'), '__mime' =>'artist/artist', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8914'), '__mime' =>'artist/producer', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c8913'), '__mime' =>'artist/producer+mixing', '__ref' => []],
        ]);

        $events = new PreserveEvent();
        $service = (new Unit())
            ->setDriver($this->client)
            ->setEventDispatcher($events);

        $actual = $service->fetchDiscrete([
            '5f3c539b711e4cc306ac2b87',
            '5f3c539b711e4cc306ac2b80', //< -- does not exist
            '5f3c53af4fb5eebf643c8914'
            ]);
        $expected = [
            ['_id' => '5f3c539b711e4cc306ac2b87', '__mime' =>'album/album', '__ref' => []],
            null,
            ['_id' => '5f3c53af4fb5eebf643c8914', '__mime' =>'artist/producer', '__ref' => []],
        ];
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(UnitViewEvent::class, $events->getLastEvent());
    }

    protected function setUp(): void
    {
        $db = getenv('DB_DATABASE') ?: 'user';
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 27017;
        $user = getenv('DB_USER') ? rawurlencode(getenv('DB_USER')) : null;
        $pwd = getenv('DB_PASSWORD') ? rawurlencode(getenv('DB_PASSWORD')) : null;

        $this->client = (new Client(
            $user && $pwd
                ? "mongodb://{$user}:{$pwd}@{$host}:{$port}/{$db}"
                : "mongodb://{$host}:{$port}/{$db}"
        ))->selectDatabase($db);
    }

    protected function tearDown(): void
    {
        $this->client->dropCollection('unit');
    }
}
