<?php

namespace Unit\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\ServiceManager\ServiceManager;
use Unit\Application;
use Unit\Tests\Emitter;

class IndexTest extends TestCase {

    public function testSuccesss(): void
    {
        $container = new ServiceManager(require_once './config/service.php');
        $routes = require_once './config/routes.php';
        $emitter = new Emitter();
        $request = ServerRequestFactory::fromGlobals([
            'SCRIPT_URL' => '/',
            'REQUEST_METHOD' => 'GET'
        ],);

        (new Application($container,$emitter,$routes))->run($request);

        $response = $emitter->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
