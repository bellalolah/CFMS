<?php
namespace Cfms\Controllers;

use Cfms\Dto\SessionDto;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Cfms\Services\SessionService;

class SessionController
{
    public function __construct(private SessionService $sessionService) {}

    public function activate(Request $request, Response $response, $args): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Forbidden: Admins only']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        $sessionId = $args['session_id'] ?? null;
        if (!$sessionId) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'session_id is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $result = $this->sessionService->activateSession($sessionId);
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Session activated successfully']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Failed to activate session']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response, $args): Response
    {

        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['success' => false, 'message' => 'Forbidden: Admins only'], 403);
        }

        $data = (array)$request->getParsedBody();
        $semestersData = $data['semesters'] ?? [];
        unset($data['semesters']);
        $sessionData = $data;

        // 3. Convert boolean to integer for the database (This is correct)
        if (isset($sessionData['is_active'])) {
            $sessionData['is_active'] = (int)$sessionData['is_active'];
        }


        $result = $this->sessionService->createSessionWithSemesters($sessionData, $semestersData);

        // 5. Handle the response
        if ($result) {
            return JsonResponse::withJson($response, $result, 201);
        }

        // The failure case is correct
        return JsonResponse::withJson($response, ['success' => false, 'message' => 'Failed to create session and semesters'], 400);
    }
    public function update(Request $request, Response $response, $args): Response
    {
        $data = (array)$request->getParsedBody();
        $sessionId = $args['session_id'] ?? null;
        $semesterId = $args['semester_id'] ?? null;
        $sessionData = $data['session'] ?? [];
        $semesterData = $data['semester'] ?? [];
        $result = $this->sessionService->updateSessionWithSemester($sessionId, $sessionData, $semesterId, $semesterData);
        $response->getBody()->write(json_encode(['success' => true, 'data' => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, $args): Response
    {
        $sessionId = $args['session_id'] ?? null;
        $semesterId = $args['semester_id'] ?? null;
        $result = $this->sessionService->deleteSessionWithSemester($sessionId, $semesterId);
        $response->getBody()->write(json_encode(['success' => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getCurrent(Request $request, Response $response, $args): Response
    {
        $session = $this->sessionService->getCurrentSession();
        if ($session) {
            $response->getBody()->write(json_encode($session));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'No active session found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    public function createSemester(Request $request, Response $response, $args): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Forbidden: Admins only']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        $sessionId = $args['session_id'] ?? null;
        if (!$sessionId) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'session_id is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $data = (array)$request->getParsedBody();
        if (empty($data)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Semester data is required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $data['session_id'] = $sessionId;
        $result = $this->sessionService->createSemester($data);
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'semester_id' => $result]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Failed to create semester']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    public function getByDate(Request $request, Response $response, $args): Response
    {
        $params = $request->getQueryParams();
        $from = $params['from'] ?? null;
        $to = $params['to'] ?? null;
        if (!$from || !$to) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'from and to query parameters are required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $sessions = $this->sessionService->getSessionsByDateRange($from, $to);
        $dtos = array_map(fn($s) => new SessionDto($s), $sessions);
        $response->getBody()->write(json_encode($dtos));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
