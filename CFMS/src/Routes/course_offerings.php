<?php
use Cfms\Controllers\CourseOfferingController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Cfms\Services\CourseOfferingService;

/*
 *
 * [
  { "course_id": 1, "semester_id": 2, "lecturer_id": 3 },
  { "course_id": 2, "semester_id": 2, "lecturer_id": 3 }
]
 * */
return function ($app) {
    $controller = $app->getContainer()->get(CourseOfferingController::class);
    $app->group('/course-offerings', function ($group) use ($app, $controller) {
        $group->post('', [$controller, 'create']);
        $group->post('/batch', [$controller, 'createBulk']);
        $group->delete('/batch', [$controller, 'unassignBulk']);
        // ADD THIS NEW ROUTE for deleting by primary key IDs
        $group->delete('', [$controller, 'unassignBulkByIds']);
        $group->get('', [$controller, 'getAll']);
        $group->get('/{id}', [$controller, 'getById']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'delete']);
        $group->get('/lecturer/{lecturer_id}', [$controller, 'getAllByLecturer']);
        $group->get('/session/{session_id}', [$controller, 'getAllBySession']);
    })->add(JwtAuthMiddleware::class);
};
