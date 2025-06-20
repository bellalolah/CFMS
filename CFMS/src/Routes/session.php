<?php
// Example: POST /session
// Payload: { "name": "2024/2025", "start_date": "2024-09-01", "end_date": "2025-06-30", "status": "open", "is_active": true }
//GET /sessions/by-date?from=2024-01-01&to=2025-01-01
use Cfms\Controllers\SessionController;
use Dell\Cfms\Middlewares\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/sessions', function (RouteCollectorProxy $group) use ($app) {
        $controller = $app->getContainer()->get(SessionController::class);
        $group->post('', [$controller, 'create']);
        $group->get('/active', [$controller, 'getActiveSessionSemester']);
        $group->get('/current', [$controller, 'getCurrent']);
        $group->post('/{session_id}/activate', [$controller, 'activate']);
        $group->post('/{session_id}/semester', [$controller, 'createSemester']);
        $group->get('/by-date', [$controller, 'getByDate']);

    });
};
