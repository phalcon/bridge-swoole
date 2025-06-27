# Phalcon 🌉 Swoole

Bridge to run Phalcon with Swoole.

## Installation

```bash
composer require phalcon/bridge-swoole
```

## Quick example

See comments inside code for more details.

```php
require_once __DIR__ . '/../vendor/autoload.php';

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Enum;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;


$http = new Server('0.0.0.0', 8080, SWOOLE_PROCESS);
$http->on('start', function () {
    echo "Swoole http server is started at http://127.0.0.1:8080\n";
});

$http->on('request', function (Request $request, Response $response) {
    $content = '';
    try {
        // Create a completely fresh app instance for each request
        $app = new Micro();

        // Setting up the database connection
        $app['db'] = function () {
            return new Mysql([
                'host' => 'tfb-database',
                'dbname' => 'hello_world',
                'username' => 'benchmarkdbuser',
                'password' => 'benchmarkdbpass',
                'options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                    PDO::ATTR_PERSISTENT => true,
                ],
            ]);
        };

        // Setting up the view component
        $app['view'] = function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/../app/views/');
            $view->registerEngines([
                ".volt" => function ($view) {
                    $volt = new Volt($view);
                    $volt->setOptions([
                        "path" => __DIR__ . "/../app/compiled-templates/",
                        "extension" => ".c",
                        "separator" => '_',
                    ]);
                    return $volt;
                }
            ]);
            return $view;
        };

        $app->notFound(function () use ($app) {
            $app->response->setStatusCode(404, 'Not Found');
        });
        // Re-register all routes for each request
        $app->map('/plaintext', function () use ($app) {
            $app->response->setContentType('text/plain', 'UTF-8');
            return "Hello, World!";
        });

        $app->map('/json', function () use ($app) {
            $app->response->setContentType('application/json', 'UTF-8');
            return json_encode(['message' => 'Hello, World!']);
        });

        $app->map('/db', function () use ($app) {
            $db = $app['db'];
            $world = $db->fetchOne('SELECT * FROM world WHERE id = ' . mt_rand(1, 10000), Enum::FETCH_ASSOC);
            $app->response->setContentType('application/json', 'UTF-8');
            return json_encode($world);
        });

        $app->map('/queries', function () use ($app) {
            $db = $app['db'];
            $queries = $app->request->getQuery('queries', "int", 1);
            $queries = min(max(intval($queries), 1), 500);
            $worlds = [];
            for ($i = 0; $i < $queries; ++$i) {
                $worlds[] = $db->fetchOne('SELECT * FROM world WHERE id = ' . mt_rand(1, 10000), Enum::FETCH_ASSOC);
            }
            $app->response->setContentType('application/json', 'UTF-8');
            return json_encode($worlds);
        });

        $app->map('/fortunes', function () use ($app) {
            $fortunes = $app['db']->query('SELECT * FROM fortune')->fetchAll();
            $fortunes[] = [
                'id' => 0,
                'message' => 'Additional fortune added at request time.'
            ];
            usort($fortunes, function ($left, $right) {
                return $left['message'] <=> $right['message'];
            });
            $app->response->setContentType('text/html', 'UTF-8');
            return $app['view']->getRender('bench', 'fortunes', [
                'fortunes' => $fortunes,
            ]);
        });

        // Set up bridge objects
        $phalconResponse = new \Phalcon\Bridge\Swoole\Response($response);
        $phalconRequest = new \Phalcon\Bridge\Swoole\Request($request);

        $app->setService('request', $phalconRequest);
        $app->setService('response', $phalconResponse);

        // Handle the request
        $content = $app->handle($request->server['request_uri']);

        // Get headers and status
        $headers = $app->response->getHeaders();
        $statusCode = $app->response->getStatusCode();

        foreach ($headers->toArray() as $header => $value) {
            $response->setHeader($header, $value);
        }

        $response->setStatusCode($statusCode ?? 200);
        $response->end($content);
    } catch (Exception $e) {
        echo "Exception: ", $e->getMessage();
        $response->setStatusCode(500);
        $response->end($content);
    }
});
$http->start();
```
