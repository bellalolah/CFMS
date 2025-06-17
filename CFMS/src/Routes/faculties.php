<?php

use Cfms\Controllers\FacultyController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/faculties', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(FacultyController::class);
        $group->get('', [$controller, 'getAllWithoutDates']);
        $group->get("/with-departments", [$controller, 'getAllWithDepartments']);
        $group->get('/{id}', [$controller, 'getById']);
        $group->get('/name/{name}', [$controller, 'getByName']);
        $group->post('', [$controller, 'create']);
        $group->post('/batch', [$controller, 'createMany']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'delete']);
    })->add(JwtAuthMiddleware::class);
};
