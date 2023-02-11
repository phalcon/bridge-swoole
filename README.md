# Phalcon 🌉 Swoole

Bridge to run Phalcon with Swoole.

## Installation

```bash
composer require phalcon/bridge-swoole
```

## Quick example

See comments inside code for more details.

```php
<?php

declare(strict_types=1);

use Phalcon\Mvc\Micro;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$http = new Server('0.0.0.0', 9501);
$http->on('start', function () {
    echo "Swoole http server is started at http://127.0.0.1:9501\n";
});

$http->on('request', function (Request $request, Response $response) {
    $app = new Micro();
    $app->setService('request', new \Phalcon\Bridge\Swoole\Request($request), true);
    $app->setService('response', new \Phalcon\Bridge\Swoole\Response($response), true);
    /**
     * We need to define our response handler, because
     * default Micro response handler will send output
     * into client, meanwhile we just want it to pass
     * to the Swoole's response end() method.
     */
    $app->setResponseHandler(function () use ($app) {
        return $app->response->getContent();
    });

    /**
     * Without fallback 404 handler it will crush.
     */
    $app->notFound(function () use ($app) {
        $app
            ->response
            ->setStatusCode(404, 'Not Found')
            ->setContent('Not found');
    });

    /**
     * Define GET endpoint.
     */
    $app->get('/', function() use ($app) {
        $app
            ->response
            ->setContent('Hello World');
    });

    /**
     * Handle in Phalcon the request and pick response content.
     * Then pass to Swoole and end response. 
     */
    $content = $app->handle($request->server['request_uri']);
    $response->end($content);
});

$http->start();
```
