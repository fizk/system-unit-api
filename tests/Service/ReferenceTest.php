<?php

namespace Unit\Service;

use PHPUnit\Framework\TestCase;
use MongoDB\Database;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class ReferenceTest extends TestCase
{
    private ?Database $client;

    public function testGet()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                [
                    '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                    '__ref' => [
                        [
                            '__unit' => new ObjectId('5f3cd6ef1950f736948b9ca4'),
                            '_id' => new ObjectId('5f3ef8b2412bd93b4c41835f'),
                            'name' => 'name 1'
                        ],
                        [
                            '__unit' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'),
                            '_id' => new ObjectId('5f44ddaa3ac918ce758d047d'),
                            'name' => 'name 2'
                        ],
                    ]
                ],
            ]);

        $service = (new Reference())->setDriver($this->client);
        $expected = [
            '__unit' => '5f3cd6ef1950f736948b9ca4',
            '_id' => '5f3ef8b2412bd93b4c41835f',
            'name' => 'name 1'
        ];
        $actual = $service->get('5f3c539b711e4cc306ac2b87', '5f3ef8b2412bd93b4c41835f');

        $this->assertEquals($expected, $actual);
    }

    public function testFetchSuccess()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            [
                '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                '__ref' => [
                    ['__unit' => new ObjectId('5f3cd6ef1950f736948b9ca4'), '_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                    ['__unit' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'), '_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                ]
            ],
            [
                '_id' => new ObjectId('5f3c53af4fb5eebf643c891e'),
                '__ref' => [
                    ['__unit' => new ObjectId('5f3cd70c9d9993c259164460'), '_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                    ['__unit' => new ObjectId('5f3cd712835544bd681b4e47'), '_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                ]
            ],
        ]);

        $service = (new Reference())->setDriver($this->client);
        $expected = [
            ['_id' => '5f3c539b711e4cc306ac2b87', '__ref' => [
                ['__unit' => '5f3cd6ef1950f736948b9ca4', '_id' => '5f3ef8b2412bd93b4c41835f'],
                ['__unit' => '5f3cd6fb2dcf6de98bc4124f', '_id' => '5f3ef8b2412bd93b4c41835f'],
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
                    ['__unit' => new ObjectId('5f3cd6ef1950f736948b9ca4'), '__mime' => 'album/album','_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                    ['__unit' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'), '__mime' => 'album/album','_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                ]
            ],
            [
                '_id' => new ObjectId('5f3c53af4fb5eebf643c891e'),
                '__ref' => [
                    ['__unit' => new ObjectId('5f3cd6ef1950f736948b9ca4'), '__mime' => 'member/member','_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                    ['__unit' => new ObjectId('5f3cd6fb2dcf6de98bc4124f'), '__mime' => 'album/album','_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')],
                ]
            ],
        ]);

        $service = (new Reference())->setDriver($this->client);
        $expected = [
            ['_id' => '5f3c53af4fb5eebf643c891e', '__ref' => [
                ['__unit' => '5f3cd6ef1950f736948b9ca4', '__mime' => 'member/member', '_id' => '5f3ef8b2412bd93b4c41835f'],
                ['__unit' => '5f3cd6fb2dcf6de98bc4124f', '__mime' =>'album/album', '_id' => '5f3ef8b2412bd93b4c41835f'],
            ]]
        ];
        $actual = $service->fetch('5f3cd6ef1950f736948b9ca4', 'member/.*');

        $this->assertEquals($expected, $actual);
    }

    public function testPost()
    {
        $this->client->selectCollection('unit')
            ->insertMany([
                [
                    '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                    '__ref' => [
                        [
                            '__unit' => new ObjectId('5f3cd6ef1950f736948b9ca4'),
                            '_id' => new ObjectId('5f3ef8b2412bd93b4c41835f')
                        ],
                    ]
                ],
            ]);

        $service = (new Reference())->setDriver($this->client);
        $expected = [
            ['_id' => '5f3c539b711e4cc306ac2b87', '__ref' => [
                ['__unit' => '5f3cd6ef1950f736948b9ca4', '_id' => '5f3ef8b2412bd93b4c41835f'],
            ]]
        ];
        $actual = $service->post('5f3c539b711e4cc306ac2b87', ['__mime' => 'some/mime', '__unit' => '5f3efa510bee956dce5ab829']);

        $references = $this->client->selectCollection('unit')->find([])->toArray()[0]->getArrayCopy()['__ref']->getArrayCopy();

        $this->assertCount(2, $references);
        $this->assertEquals($actual, (string) $references[1]->getArrayCopy()['_id']);
        $this->assertEquals('5f3efa510bee956dce5ab829', (string) $references[1]->getArrayCopy()['__unit']);
    }

    public function testPut()
    {
        $this->client->selectCollection('unit')
        ->insertMany([
            ['_id' => new ObjectId('5f3c539b711e4cc306ac2b87'), 'field' => 'value', '__ref' => [
                [
                    '__unit' => new ObjectId('5f3f145abe6339dce846f657'),
                    '_id' => new ObjectId('5f3f14607771513a502f9e2f'),
                    '__mime' => 'some/one'
                ],
                [
                    '__unit' => new ObjectId('5f3f1466e65f43bc3e5b4180'),
                    '_id' => new ObjectId('5f3f1439ad8de72bd908a8a9'),
                    '__mime' => 'some/two',
                    'will-be' => 'removed',
                ],
                [
                    '__unit' => new ObjectId('5f3f146c6b5d16562ffad8b4'),
                    '_id' => new ObjectId('5f3f1472ace60966bc54a5a8'),
                    '__mime' => 'some/three'
                ],
            ]],
            ['_id' => new ObjectId('5f3c53af4fb5eebf643c891e'), 'field' => 'value', '__ref' => []],
        ]);

        $service = (new Reference())->setDriver($this->client);
        $service->put(
            '5f3f1439ad8de72bd908a8a9',
            [
                '__mime' => 'some/mime',
                '__unit' => '5f3f1466e65f43bc3e5b4180',
                'a-new' => 'value'
            ]
        );

        $result = $this->client
            ->selectCollection('unit')
            ->findOne(['_id' => new ObjectId('5f3c539b711e4cc306ac2b87')])
            ->getArrayCopy()['__ref']
            ->getArrayCopy();

        $this->assertEquals('5f3f145abe6339dce846f657', (string)$result[0]->getArrayCopy()['__unit']);
        $this->assertEquals('some/one', (string)$result[0]->getArrayCopy()['__mime']);
        $this->assertEquals('5f3f14607771513a502f9e2f', (string)$result[0]->getArrayCopy()['_id']);

        $this->assertEquals('5f3f1466e65f43bc3e5b4180', (string)$result[1]->getArrayCopy()['__unit']);
        $this->assertEquals('some/mime', (string)$result[1]->getArrayCopy()['__mime']);
        $this->assertEquals('value', (string)$result[1]->getArrayCopy()['a-new']);
        $this->assertEquals('5f3f1439ad8de72bd908a8a9', (string)$result[1]->getArrayCopy()['_id']);
        $this->assertArrayNotHasKey('will-be', $result[1]->getArrayCopy());

        $this->assertEquals('5f3f146c6b5d16562ffad8b4', (string)$result[2]->getArrayCopy()['__unit']);
        $this->assertEquals('some/three', (string)$result[2]->getArrayCopy()['__mime']);
        $this->assertEquals('5f3f1472ace60966bc54a5a8', (string)$result[2]->getArrayCopy()['_id']);
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
