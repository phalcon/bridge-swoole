<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Phalcon\Mvc\Micro;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$http = new Server('0.0.0.0', 9501);
$http->set(['hook_flags' => SWOOLE_HOOK_ALL]);

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
     * to the Swoole response end() method.
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

    $app->get('/', function() use ($app) {
        $app
            ->response
            ->setContent('Hello World');
    });

    $content = $app->handle($request->server['request_uri']);
    $response->end($content);
});

$http->start();
