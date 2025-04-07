<?php
declare(strict_types=1);

use CosmoCode\Formserver\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

// Add Routing Middleware
$app->addRoutingMiddleware();

// define minimal custom error handler
$customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    $settings = $app->getContainer()->get('settings');

    if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
        // custom error page
        $errorPage = __DIR__ . '/../' . $settings['errorPageNotFound'];
        if (is_file($errorPage)) {
            $errorMessage = file_get_contents($errorPage);
        } else {
            $errorMessage = '<h1>' . $settings['errorMessageNotFound'] . '</h1>';
        }
        $response->getBody()->write($errorMessage);
        $response = $response->withStatus(404);
    } else {
        // custom error page
        $errorPage = __DIR__ . '/../' . $settings['errorPageGeneral'];
        if (is_file($errorPage)) {
            $errorMessage = file_get_contents($errorPage);
        } else {
            $errorMessage = '<h1>' . $settings['errorMessageGeneral'] . '</h1>';
        }
        $response->getBody()->write($errorMessage);
        $response = $response->withStatus(500);
    }

    return $response;
};

 // This middleware should be added last.
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
if (!$displayErrorDetails) {
    $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
}

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
