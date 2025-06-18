<?php

use Cfms\Controllers\FacultyController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/faculties', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(FacultyController::class);
        $group->get('', [$controller, 'getAllWithoutDates'])
            ->add(JwtAuthMiddleware::class);
        $group->get("/with-departments", [$controller, 'getAllWithDepartments']);
        $group->get('/{id}', [$controller, 'getById'])
            ->add(JwtAuthMiddleware::class);
        $group->get('/name/{name}', [$controller, 'getByName'])
            ->add(JwtAuthMiddleware::class);
        $group->post('', [$controller, 'create'])
            ->add(JwtAuthMiddleware::class);
        $group->post('/batch', [$controller, 'createMany'])
            ->add(JwtAuthMiddleware::class);
        $group->put('/{id}', [$controller, 'update'])
            ->add(JwtAuthMiddleware::class);
        $group->delete('/{id}', [$controller, 'delete'])
            ->add(JwtAuthMiddleware::class);
    });
};
