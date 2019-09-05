<?php
declare(strict_types=1);

use CosmoCode\Formserver\Actions\FormAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/forms', function (Group $group) use ($container) {
        $group->get('', FormAction::class);
        $group->any('/{id}', FormAction::class);
    });
};
