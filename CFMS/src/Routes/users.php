<?php

use Cfms\Controllers\StudentProfileController;
use Cfms\Controllers\UserController;
use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\LecturerCourseRepository;
use Cfms\Repositories\user_profile\LecturerProfileRepository;
use Cfms\Repositories\user_profile\StudentProfileRepository;
use Cfms\Repositories\UserRepository;
use Cfms\Services\StudentProfileService;
use Cfms\Services\UserService;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/users', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(UserController::class);
        $sProfileController = $app->getContainer()->get(StudentProfileController::class);
        $group->get('', [$controller, 'getPaginatedUsers']);
        $group->post('/register', [$controller, 'register']);
        $group->get('/info', [$controller, 'getPaginatedUsers']);
        $group->post('/lecturers', [$controller, 'createLecturer']);
        $group->get('/lecturers', [$controller, 'getLecturers']);
        $group->get('/lecturers-with-courses', [$controller, 'getLecturersWithCourses']);
        $group->delete('/lecturers/{lecturer_id}', [$controller, 'deleteLecturer']);
      /*  $group->post('/{user_id}/profile', [$sProfileController, 'create']);*/
        $group->get('/{user_id}/profile', [$controller, 'getUserWithProfile']);
        $group->get('/{user_id}', [$controller, 'getUserInfo']);
    })->add(JwtAuthMiddleware::class);
};
