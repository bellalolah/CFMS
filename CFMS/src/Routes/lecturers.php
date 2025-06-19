<?php

use Cfms\Controllers\LecturerController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/lecturers', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(LecturerController::class);
        $group->get('/{id}/questionnaires', [$controller, 'getQuestionnaires']);
        $group->get('/{id}/questionnaires/{questionnaire_id}', [$controller, 'getQuestionnaireDetails']);
    })->add(JwtAuthMiddleware::class);
};