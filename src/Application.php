<?php

namespace Unit;

use Psr\EventDispatcher\EventDispatcherInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Highway\{Route, RouteCollection};
use Unit\Event\SystemError;
use Throwable;
use Exception;
use Error;

class Application
{
    private ServiceLocatorInterface $container;
    private EmitterInterface $emitter;
    private array $routes;

    public function __construct(ServiceLocatorInterface $container, EmitterInterface $emitter, array $router)
    {
        $this->container = $container;
        $this->emitter = $emitter;
        $this->routes = $router;
    }

    public function run(ServerRequestInterface $request)
    {
        try {
            $collection = new RouteCollection();
            foreach ($this->routes as $route => $verbs) {
                foreach ($verbs as $verb => $handler) {
                    $collection->addRoute(new Route($verb, $route, $this->container->get($handler)));
                }
            }

            $this->emitter->emit($collection->find($request)->dispatch($request));
        } catch (Exception $e) {
            $this->container->get(EventDispatcherInterface::class)->dispatch(new SystemError($e, 'EXCEPTION'));
            $this->emitter->emit(new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'path' => "{$e->getFile()}:{$e->getLine()}",
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ],
                $e->getCode() > 199 ? $e->getCode() : 400
            ));
        } catch (Error $e) {
            $this->container->get(EventDispatcherInterface::class)->dispatch(new SystemError($e, 'ERROR'));
            $this->emitter->emit(new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'path' => "{$e->getFile()}:{$e->getLine()}",
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ],
                $e->getCode() > 199 ? $e->getCode() : 500
            ));
        } catch (Throwable $e) {
            $this->container->get(EventDispatcherInterface::class)->dispatch(new SystemError($e, 'SYSTEM'));
            $this->emitter->emit(new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'path' => "{$e->getFile()}:{$e->getLine()}",
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ],
                500
            ));
        }
    }
}
