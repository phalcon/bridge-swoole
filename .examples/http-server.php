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
    //var_dump($request); exit;
    $app = new Micro();
    $app['request'] = function () use ($request) {
        return new \Phalcon\Bridge\Swoole\Request($request);
    };
    $app['response'] = function () use ($response) {
        return new \Phalcon\Bridge\Swoole\Response($response);
    };

    $app->get('/', function() use ($app) {
        $app
            ->response
            ->setExpires(new DateTime())
            ->setContent('Hello World!');
    });

    $app->get('/co', function() use ($app) {
        $result = [];
        Co::join([
            go(function () use (&$result) {
                $result['google'] = md5(file_get_contents("https://www.google.com/"));
            }),
            go(function () use (&$result) {
                $result['taobao'] = md5(file_get_contents("https://www.taobao.com/"));
            })
        ]);

        $app->response->setJsonContent($result);
    });
    $app->notFound(function () use ($app) {
        $app
            ->response
            ->setStatusCode(404, 'Not Found')
            ->sendHeaders()
            ->setContent('Not found');
    });

    $handler = $app->handle($request->server['request_uri']);
    $content = $handler === null ? $app->response->getContent() : $handler;

    $response->end($content);
});

$http->start();
