<?php

use Cfms\Controllers\CourseController;
use Cfms\Controllers\CourseDepartmentController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/courses', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(CourseController::class);
        $group->post('', [$controller, 'create']);
        $group->get('', [$controller, 'getAll']);
        $group->get('/{id}', [$controller, 'getById']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'delete']);
        $courseDepartmentController = $app->getContainer()->get(CourseDepartmentController::class);
        $group->get('/{course_id}/departments', [$courseDepartmentController, 'getDepartmentsForCourse']);
    })->add(JwtAuthMiddleware::class);
};
