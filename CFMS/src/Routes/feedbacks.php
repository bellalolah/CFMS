<?php

use Cfms\Controllers\FeedbackController;
use Cfms\Controllers\FeedbackSubmissionController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/feedbacks', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(FeedbackController::class);
        $fController = $app->getContainer()->get(FeedbackSubmissionController::class);
        $group->get('/students/{id:\d+}/submissions',  [$fController, 'getHistory']);
    })->add(JwtAuthMiddleware::class);
};

