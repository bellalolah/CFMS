<?php

use Cfms\Controllers\StudentProfileController;
use Cfms\Repositories\user_profile\StudentProfileRepository;
use Cfms\Services\StudentProfileService;

return function ($app) {
    $studentProfileRepository = new StudentProfileRepository();
    $studentProfileService = new  StudentProfileService($studentProfileRepository);
    $controller = $app->getContainer()->get(StudentProfileController::class);
    $app->post('/student-profiles/{user_id}', [$controller, 'create']);
    $app->get('/student-profiles/{user_id}', [$controller, 'get']);
    $app->put('/student-profiles/{user_id}', [$controller, 'update']);
};
