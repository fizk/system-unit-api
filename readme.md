## Unit API

A very simple API that can store some **units**.

This application uses a _router_ that can dispatch [PSR-15: HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/) _handlers_ that then, returns [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) responses.

Each handlers is constructed through a [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/) _service manager_.

The _service manager_ works as dependency injection mechanism that injects _services_ into the _handlers_. The _Container interface_ is not exposed to the _handlers_.

Handlers get a configured instances of services that act as a gateway to the persistent storage (MongoDB).

Some _handlers_ are also injected with a [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14/) which sends events when certain things happen. Currently, the event listeners are just logging events using the [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)

Data is passed between _serive_ and _handler_ via POPO (Plain Old PHP Object)
