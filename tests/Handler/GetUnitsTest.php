<?php

namespace Unit\Handler;

use Psr\Log\LoggerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Uri;
use Laminas\ServiceManager\ServiceManager;
use Psr\Log\AbstractLogger;
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
        $this->container->setFactory(LoggerInterface::class, function () {
            return new class extends AbstractLogger
            {
                public function log($level, $message, array $context = array())
                {}
            };
        });

        $request = (new ServerRequest())
            ->withQueryParams(['ids' => ' 5f45a2f0d07e5b4ea70e3a03  , 5f45a2f0d07e5b4ea70e3a02 '])
            ->withMethod('GET')
            ->withUri(new Uri('/units'));

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
