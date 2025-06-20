<?php

use Dell\Cfms\KPI\Controllers\AdminKPIController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/kpis', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(AdminKPIController::class);
        $group->get('/admin', [$controller, 'dashboardOverview']);
        $group->get('/admin/lecturer-performance', [$controller, 'lecturerPerformance']);
    });
};