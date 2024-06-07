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

use Phalcon\Di\Di;
use Phalcon\Mvc\Micro;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$di = new Di();
$di->setShared('router', new Router(false));

$app = new Micro($di);

/**
 * Define example GET endpoint with text response.
 */
$app->get('/', function() {
    return 'Hello World';
});

/**
 * Define example redirect.
 */
$app->get('/redirect', function () {
    // Redirect is handled by Swoole's Request.
    return ['redirect' => 'https://github.com'];
});

/**
 * Define example json response.
 */
$app->get('/json', function () {
    // Correct headers will be added from Swoole's Response.
    return ['json' => true];
});

$http = new Server('0.0.0.0', 9501);
$http->on('start', function () {
    echo "Swoole http server is started at http://127.0.0.1:9501\n";
});

$http->on('request', function (Request $request, Response $response) use ($app) {
    $app->setService('request', new \Phalcon\Bridge\Swoole\Request($request));
    $app->setService('response', new \Phalcon\Bridge\Swoole\Response($response));

    /**
     * Without fallback 404 handler it will crush.
     */
    $app->notFound(function () use ($response) {
        $response->setStatusCode(404, 'Not Found');
    });

    /**
     * Handle in Phalcon the request and pick response content.
     * Then pass to Swoole and end response. 
     */
    $content = $app->handle($request->server['request_uri']);
    if (!empty($content['redirect'])) {
        $response->redirect($content['redirect'], 301);
        return;
    }

    if (isset($content['content'])) {
        if (is_array($content['content'])) {
            $response->setHeader('Content-Type', 'application/json');
            $content = json_encode($content['content']);
        } else {
            $content = (string)$content['content'];
        }
    }

    $response->end($content);
});

$http->start();
```
