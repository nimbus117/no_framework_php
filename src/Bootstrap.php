<?php declare(strict_types = 1);

namespace Example;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

$environment = 'development';

/**
 * Register the error handler
 */
$whoops = new \Whoops\Run;
if ($environment !== 'production') {
  $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
  $whoops->pushHandler(function($e){
    echo 'Todo: Friendly error page and send an email to the developer';
  });
}
$whoops->register();

/**
 * HTTP component - object-oriented interface
 */
$request = new \Http\HttpRequest($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
$response = new \Http\HttpResponse;

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/hello-world', function () {
        echo 'Hello World';
    });
    $r->addRoute('GET', '/another-route', function () {
        echo 'This works too';
    });
});


/**
 * router - dispatches to different handlers
 */
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPath());
switch ($routeInfo[0]) {
case \FastRoute\Dispatcher::NOT_FOUND:
  $response->setContent('404 - Page not found');
  $response->setStatusCode(404);
  break;
case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
  $response->setContent('405 - Method not allowed');
  $response->setStatusCode(405);
  break;
case \FastRoute\Dispatcher::FOUND:
  $handler = $routeInfo[1];
  $vars = $routeInfo[2];
  call_user_func($handler, $vars);
  break;
}

// $content = '<h1>Hello World</h1>';
// $response->setContent($content);
// $response->setContent('404 - Page not found');
// $response->setStatusCode(404);


/**
 * send the response data to the browser
 */
foreach ($response->getHeaders() as $header) {
  // false = don't overwrite existing headers
  header($header, false);
}

echo $response->getContent();
