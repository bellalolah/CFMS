<?php

use Cfms\Controllers\CourseDepartmentController;
use Cfms\Controllers\DepartmentController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/departments', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(DepartmentController::class);
        $group->get('', [$controller, 'getAllInfo']);
        $group->get("/with-courses",[$controller, 'getAllWithCourses']);
        $group->get('/{id}', [$controller, 'getById']);
        $group->get('/name/{name}', [$controller, 'getByName']);
        $group->get('/faculty/{faculty_id}', [$controller, 'getByFacultyId']);
        $group->post('', [$controller, 'create']);
        $group->post('/batch', [$controller, 'createMany']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'delete']);
        $courseDepartmentController = $app->getContainer()->get(CourseDepartmentController::class);
        $group->get('/{department_id}/courses', [$courseDepartmentController, 'getCoursesForDepartment']);
        $group->post('/courses/assign', [$courseDepartmentController, 'assign']);
        $group->post('/courses/remove', [$courseDepartmentController, 'remove']);
    })->add(JwtAuthMiddleware::class);
};
