<?php

use Cfms\Controllers\FeedbackSubmissionController;
use Cfms\Controllers\QuestionnaireController;
use Cfms\Controllers\StudentProfileController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;


return function ($app) {
    $app->group('/students-questionnaires', function (RouteCollectorProxy $group) use ($app) {
       
        $qController = $app->getContainer()->get(QuestionnaireController::class);
        $fController = $app->getContainer()->get(FeedbackSubmissionController::class);
        $group->get('/{id}/questionnaires', [$qController, 'getQuestionnaires']);
        $group->get('/{id}/pending-questionnaires',  [$qController, 'getPendingForStudent']);
        $group->get('/{id}/submissions',  [$fController, 'getHistory']);
    })->add(JwtAuthMiddleware::class);
};



