<?php

namespace Unit\Service;

use PHPUnit\Framework\TestCase;
use MongoDB\Database;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

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

        $service = (new Unit())->setDriver($this->client);

        $expected = [
            '_id' => '5f3c539b711e4cc306ac2b87',
            'field' => 'value',
            '__ref' => []
        ];
        $actual = $service->get('5f3c539b711e4cc306ac2b87');

        $this->assertEquals($expected, $actual);
    }

    public function testGetNotFound()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
            ]);

        $service = (new Unit())->setDriver($this->client);

        $expected = null;
        $actual = $service->get('5f3c539b711e4cc306ac2123');

        $this->assertEquals($expected, $actual);
    }

    public function testPutCreate()
    {
        $service = (new Unit())->setDriver($this->client);

        $expected = 1;
        $actual = $service->put(
            '5f3c539b711e4cc306ac2123',
            ['filed' => 'value', '__mime' => 'some/mime']
        );

        $this->assertEquals($expected, $actual);
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

        $service = (new Unit())->setDriver($this->client);

        $expected = 0;
        $actual = $service->put('5f3c539b711e4cc306ac2123', ['filed' =>'new value', '__mime' => 'some/mime']);

        $this->assertEquals($expected, $actual);
    }

    public function testPutUpdateNoChange()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
        ]);

        $service = (new Unit())->setDriver($this->client);

        $expected = 0;
        $actual = $service->put('5f3c539b711e4cc306ac2123', ['filed' =>'value', '__mime' => 'some/mime']);

        $this->assertEquals($expected, $actual);
    }

    public function testPatchAddField()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ]);
        $service = (new Unit())->setDriver($this->client);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['new_filed' => 'new-value']);

        $result = $this->client->selectCollection('unit')
            ->findOne(['_id' => new ObjectId('5f3c539b711e4cc306ac2123')]);

        $this->assertArrayHasKey('field', $result->getArrayCopy());
        $this->assertArrayHasKey('new_filed', $result->getArrayCopy());
        $this->assertEquals('new-value', $result->getArrayCopy()['new_filed']);

        $this->assertEquals(1, $writeResult);
    }

    public function testPatchUpdateField()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ]);
        $service = (new Unit())->setDriver($this->client);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['field' => 'new value']);

        $result = $this->client->selectCollection('unit')
            ->findOne(['_id' => new ObjectId('5f3c539b711e4cc306ac2123')]);

        $this->assertEquals('new value', $result->getArrayCopy()['field']);
        $this->assertEquals(1, $writeResult);
    }

    public function testPatchNoUpdate()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                ['_id' => new ObjectId('5f3c539b711e4cc306ac2123'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
                ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__mime' =>'some/mime', '__ref' => []],
            ]);
        $service = (new Unit())->setDriver($this->client);
        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['field' => 'value']);
        $this->assertEquals(0, $writeResult);
    }

    public function testPatchUnitNotFound()
    {
        $service = (new Unit())->setDriver($this->client);

        $writeResult = $service->patch('5f3c539b711e4cc306ac2123', ['field' => 'value']);

        $expected = [];
        $actual = $this->client->selectCollection('unit')->find([])->toArray();

        $this->assertEquals($expected, $actual);
        $this->assertEquals(0, $writeResult);
    }

    public function testPost()
    {
        $service = (new Unit())->setDriver($this->client);

        $expected = 1;
        $id = $service->post(['filed' => 'value', '__mime' => 'some/mime']);
        $actual = $this->client->selectCollection('unit')->find([])->toArray();

        $this->assertCount($expected, $actual);
        $this->assertMatchesRegularExpression('/^[a-f\d]{24}$/i', $id);
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

        $service = (new Unit())->setDriver($this->client);
        $result = $service->delete('5f3c539b711e4cc306ac2b87');

        $this->assertCount(1, $this->client->selectCollection('unit')->find());
        $this->assertEquals(1, $result);
    }

    public function testDeleteNotFound()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
        ]);

        $service = (new Unit())->setDriver($this->client);
        $result = $service->delete('5f3c539b711e4cc306ac2b10');

        $this->assertCount(2, $this->client->selectCollection('unit')->find());
        $this->assertEquals(0, $result);
    }

    public function testFetch()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' =>'value', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' =>'value', '__ref' => []],
        ]);

        $service = (new Unit())->setDriver($this->client);
        $result = $service->fetch();

        $this->assertCount(2, $this->client->selectCollection('unit')->find());
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

        $service = (new Unit())->setDriver($this->client);

        $this->assertCount(2, $service->fetch('album.*'));
        $this->assertCount(1, $service->fetch('album/album'));
        $this->assertCount(1, $service->fetch('album/.*single'));
        $this->assertCount(1, $service->fetch('album.*single'));
        $this->assertCount(2, $service->fetch('album/.*'));

        $this->assertCount(3, $service->fetch('artist/.*'));
        $this->assertCount(2, $service->fetch('artist/producer.*'));
        $this->assertCount(1, $service->fetch('artist/.*mixing'));
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

        $service = (new Unit())->setDriver($this->client);

        $actual = $service->fetchDiscrete(['5f3c539b711e4cc306ac2b87', '5f3c53af4fb5eebf643c8914']);
        $expected = [
            ['_id' => '5f3c539b711e4cc306ac2b87', '__mime' =>'album/album', '__ref' => []],
            ['_id' => '5f3c53af4fb5eebf643c8914', '__mime' =>'artist/producer', '__ref' => []],
        ];
        $this->assertEquals($expected, $actual);
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

        $service = (new Unit())->setDriver($this->client);

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
