<?php

// use Interop\Container\ContainerInterface;
use Psr\Container\ContainerInterface;
use Unit\Handler;
use Unit\Service;
use Unit\Event;

use MongoDB\Client;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    'factories' => [
        Handler\Index::class => function(ContainerInterface $container, $requestedName) {
            return new Handler\Index();
        },
        Handler\GetUnit::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\GetUnit())
                ->setUnitService($container->get(Service\Unit::class))
                ;
        },
        Handler\GetUnits::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\GetUnits())
                ->setUnitService($container->get(Service\Unit::class))
                ;
        },
        Handler\PostUnit::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\PostUnit())
                ->setUnitService($container->get(Service\Unit::class))
                ;
        },
        Handler\PutUnit::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\PutUnit())
                ->setUnitService($container->get(Service\Unit::class))
                ;
        },
        Handler\PatchUnit::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\PatchUnit())
                ->setUnitService($container->get(Service\Unit::class))
                ;
        },
        Handler\DeleteUnit::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\DeleteUnit())
                ->setUnitService($container->get(Service\Unit::class))
                ;
        },
        Handler\GetFromReference::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\GetFromReference())
                ->setReferenceService($container->get(Service\Reference::class))
                ;
        },
        Handler\PutReference::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\PutReference())
                ->setReferenceService($container->get(Service\Reference::class))
                ;
        },

        Service\Unit::class => function (ContainerInterface $container, $requestedName) {
            return (new Service\Unit())
                ->setDriver($container->get(Service\DatabaseAware::class))
                ;
        },
        Service\Reference::class => function (ContainerInterface $container, $requestedName) {
            return (new Service\Reference())
                ->setDriver($container->get(Service\DatabaseAware::class))
                ;
        },
        Service\DatabaseAware::class => function (ContainerInterface $container, $requestedName) {
            $db = getenv('DB_DATABASE') ? : 'user';
            $host = getenv('DB_HOST') ? : 'localhost';
            $port = getenv('DB_PORT') ? : 27017;
            $user = getenv('DB_USER') ? rawurlencode(getenv('DB_USER')) : null;
            $pwd = getenv('DB_PASSWORD') ? rawurlencode(getenv('DB_PASSWORD')) : null;

            return (new MongoDB\Client(
                $user && $pwd
                    ? "mongodb://{$user}:{$pwd}@{$host}:{$port}/{$db}"
                    : "mongodb://{$host}:{$port}/{$db}"
            ))->selectDatabase($db);
        },

        EventDispatcherInterface::class => function (ContainerInterface $container, $requestedName) {
            $logger = $container->get(LoggerInterface::class);
            $provider = new AttachableListenerProvider();
            $provider->listen(Event\ServiceError::class, function (Event\ServiceError $event) use ($logger) : void {
                $logger->error((string) $event);
            });
            $provider->listen(Event\EntryView::class, function (Event\EntryView $event) use ($logger) : void {
                $logger->info((string) $event);
            });
            $provider->listen(Event\SystemError::class, function (Event\SystemError $event) use ($logger) : void {
                $logger->error((string) $event);
            });

            return new EventDispatcher($provider);
        },
        LoggerInterface::class => function (ContainerInterface $container, $requestedName) {
            $log = new Logger('user-api');
            $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
            return $log;
        },
    ],
];