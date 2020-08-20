<?php

namespace Unit\Service;

use PHPUnit\Framework\TestCase;
use MongoDB\Database;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class ReferenceTest extends TestCase
{
    private ?Database $client;

    public function testFetchSuccess()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            [
                '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                '__ref' => [
                    ['_id' => new ObjectId('5f3cd6ef1950f736948b9ca4'),],
                    ['_id' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'),],
                ]
            ],
            [
                '_id' => new ObjectId('5f3c53af4fb5eebf643c891e'),
                '__ref' => [
                    ['_id' => new ObjectId('5f3cd70c9d9993c259164460'),],
                    ['_id' => new ObjectId('5f3cd712835544bd681b4e47'),],
                ]
            ],
        ]);

        $service = (new Reference())->setDriver($this->client);
        $expected = [
            ['_id' => '5f3c539b711e4cc306ac2b87', '__ref' => [
                ['_id' => '5f3cd6ef1950f736948b9ca4',],
                ['_id' => '5f3cd6fb2dcf6de98bc4124f',],
            ]]
        ];
        $actual = $service->fetch('5f3cd6ef1950f736948b9ca4');

        $this->assertEquals($expected, $actual);
    }

    public function testFetchFilter()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            [
                '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                '__ref' => [
                    ['_id' => new ObjectId('5f3cd6ef1950f736948b9ca4'), '__mime' => 'album/album'],
                    ['_id' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'), '__mime' => 'album/album'],
                ]
            ],
            [
                '_id' => new ObjectId('5f3c53af4fb5eebf643c891e'),
                '__ref' => [
                    ['_id' => new ObjectId('5f3cd6ef1950f736948b9ca4'), '__mime' => 'member/member'],
                    ['_id' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'), '__mime' => 'album/album'],
                ]
            ],
        ]);

        $service = (new Reference())->setDriver($this->client);
        $expected = [
            ['_id' => '5f3c53af4fb5eebf643c891e', '__ref' => [
                ['_id' => '5f3cd6ef1950f736948b9ca4', '__mime' => 'member/member'],
                ['_id' => '5f3cd6fb2dcf6de98bc4124f', '__mime' => 'album/album'],
            ]]
        ];
        $actual = $service->fetch('5f3cd6ef1950f736948b9ca4', 'member/.*');

        $this->assertEquals($expected, $actual);
    }

    public function testPut()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' => 'value', '__ref' => []],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' => 'value', '__ref' => []],
        ]);

        $service = (new Reference())->setDriver($this->client);
        $service->put(
            '5f3c539b711e4cc306ac2b87',
            ['__mime' => 'some/mime', '_id' => '5f3c53af4fb5eebf643c891e']
        );

        $result = $this->client
            ->selectCollection('unit')
            ->findOne(['_id' => new ObjectId('5f3c539b711e4cc306ac2b87')])
            ->getArrayCopy()['__ref']
            ->getArrayCopy()[0]
            ->getArrayCopy();


        $this->assertEquals('5f3c53af4fb5eebf643c891e', (string)$result['_id']);
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
