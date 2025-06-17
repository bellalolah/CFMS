<?php
// File: routes/auth.php

// 1. Import all the classes you need to build the dependency chain
use Cfms\Controllers\AuthController;
use Slim\App; // You already have this
use Slim\Routing\RouteCollectorProxy; // Often needed for group type-hinting

return function (App $app) {
    $app->group('/auth', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(AuthController::class);
        $group->post('/register', [$controller, 'register']);
        $group->post('/login', [$controller, 'login']);
    });
};