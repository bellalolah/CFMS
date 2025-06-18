<?php
use Cfms\Controllers\FeedbackController;
use Cfms\Controllers\QuestionnaireController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;

return function ($app) {
    $app->group('/questionnaires', function ($group) use ($app) {
        $feedBackController = $app->getContainer()->get(FeedbackController::class);
        $questionnaireController = $app->getContainer()->get(QuestionnaireController::class);
        $group->get('', [$questionnaireController, 'getAll']);
        $group->post('', [$questionnaireController, 'create']);
        $group->get('/{id:\d+}', [$questionnaireController, 'getById']);
        $group->put('/{id:\d+}',[$questionnaireController, 'updateQuestionnaire']);
        $group->post('/{id:\d+}/feedbacks',[$feedBackController, 'createFeedback']);
        $group->put('/{id:\d+}/status',[$questionnaireController, 'updateStatus']);
        $group->get('/{qId:\d+}/questions/{qtnId}/feedbacks',[$feedBackController, 'getForQuestion']);
    })->add(JwtAuthMiddleware::class);
};