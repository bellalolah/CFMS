<?php
// In your main routes file...

use Cfms\Controllers\CriterionController;

use Dell\Cfms\Middlewares\JwtAuthMiddleware;


return function ($app) {

    $app->group('/criteria', function ($group) {

        $group->post('', CriterionController::class . ':create');
        $group->get('', CriterionController::class . ':getAll');

    })->add(JwtAuthMiddleware::class);
};