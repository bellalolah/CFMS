<?php
// In your main routes file...

use Cfms\Controllers\CriterionController;

use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;


return function ($app) {

    $app->group('/criteria', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(CriterionController::class);
        $group->post('', [$controller, 'create']);
        $group->get('',  [$controller, 'getAll']);
        $group->put('/{id}', [$controller, 'update']);
    })->add(JwtAuthMiddleware::class);
};