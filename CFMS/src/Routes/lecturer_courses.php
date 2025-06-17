<?php
use Cfms\Controllers\LecturerCourseController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/lecturer-courses', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(LecturerCourseController::class);
        $group->post('/assign', [$controller, 'assign']);
        $group->post('/unassign', [$controller, 'unassign']);
        $group->get('/{lecturer_id}', [$controller, 'getCourses']);
    })->add(JwtAuthMiddleware::class);
};
