<?php

use Cfms\KPI\Controllers\LecturerKPIController;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/kpis', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(LecturerKPIController::class);
        $group->get('/lecturers/{lecturerId}/recent-feedbacks', [$controller, 'getRecentFeedbacks']);
        $group->get('/lecturers/{lecturerId}', [$controller, 'dashboardOverview']);
        $group->get('/lecturers/{lecturerId}/performance', [$controller, 'lecturerPerformance']);
        $group->get('/lecturers/{lecturerId}/lecturers-courses-by-session/{sessionId}', [$controller, 'getLecturerCoursesBySession']);
        $group->get('/lecturers/{lecturerId}/recent-text-feedbacks', [$controller, 'getRecentTextFeedbacks']);
    });
};