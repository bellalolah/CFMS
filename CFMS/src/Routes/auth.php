<?php

use Cfms\Controllers\AuthController;
use Cfms\Controllers\UserController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/auth', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(AuthController::class);
        $uc = $app->getContainer()->get(UserController::class);
        $group->post('/register', [$controller, 'register']);
        $group->post('/students', [$uc, 'createStudent']);
        $group->post('/login', [$controller, 'login']);
    });
};