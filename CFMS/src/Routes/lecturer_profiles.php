<?php

use Cfms\Controllers\LecturerProfileController;
use Cfms\Repositories\user_profile\LecturerProfileRepository;
use Cfms\Services\LecturerProfileService;

return function ($app) {
    $controller = $app->getContainer()->get(LecturerProfileController::class);
    $app->post('/lecturer-profiles/{user_id}', [$controller, 'create']);
    $app->get('/lecturer-profiles/{user_id}', [$controller, 'get']);
    $app->put('/lecturer-profiles/{user_id}', [$controller, 'update']);
};
