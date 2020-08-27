<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\ServiceManager\ServiceManager;
use Unit\Application;
use Unit\Tests\Emitter;
use Unit\Tests\Service\AbstractUnit;

class GetUnitsTest extends TestCase {

    private ?ServiceManager $container;
    private ?Emitter $emitter;
    private ?array $routes;

    public function testSuccesss(): void
    {
        $this->container->setFactory(\Unit\Service\UnitInterface::class, function () {
            return new class extends AbstractUnit {
                public function fetchDiscrete(array $ids = []): array
                {
                    return $ids;
                }
            };
        });

        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/units',
            'REQUEST_METHOD' => 'GET'
        ], [
            'ids' => ' 5f45a2f0d07e5b4ea70e3a03  , 5f45a2f0d07e5b4ea70e3a02 '
        ]);

        (new Application($this->container, $this->emitter, $this->routes))->run($request);

        /** @var $response JsonResponse */
        $response = $this->emitter->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['5f45a2f0d07e5b4ea70e3a03', '5f45a2f0d07e5b4ea70e3a02'], $response->getPayload());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ServiceManager(require_once './config/service.php');
        $this->container->setAllowOverride(true);

        $this->routes = require_once './config/routes.php';
        $this->emitter = new Emitter();
    }

    protected function tearDown(): void
    {
        $this->container = null;
        $this->routes = null;
        $this->emitter = null;
    }

}
