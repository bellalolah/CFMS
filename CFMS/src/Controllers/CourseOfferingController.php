<?php

namespace Cfms\Controllers;

use Cfms\Services\CourseOfferingService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CourseOfferingController
{
    private CourseOfferingService $courseOfferingService;

    public function __construct(CourseOfferingService $courseOfferingService)
    {
        $this->courseOfferingService = $courseOfferingService;
    }

    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $id = $this->courseOfferingService->createCourseOffering($data);
        $offering = $this->courseOfferingService->getCourseOfferingDetailsById($id);
        if ($offering) {
            return JsonResponse::withJson($response, $offering->toArray(), 201);
        }
        return JsonResponse::withJson($response, ['error' => 'Failed to create course offering'], 400);
    }

    public function createBulk(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        if (!is_array($data) || empty($data)) {
            return JsonResponse::withJson($response, ['error' => 'Invalid or empty payload'], 400);
        }
        $results = $this->courseOfferingService->createBulkCourseOfferings($data);
        return JsonResponse::withJson($response, $results, 201);
    }

    public function getAllByLecturer(Request $request, Response $response, array $args): Response
    {
        $lecturerId = (int)($args['lecturer_id'] ?? 0);
        $offerings = $this->courseOfferingService->getAllByLecturer($lecturerId);
        $data = array_map(fn($o) => $o->toArray(), $offerings);
        return JsonResponse::withJson($response, $data);
    }

    public function getAllBySession(Request $request, Response $response, array $args): Response
    {
        $sessionId = (int)($args['session_id'] ?? 0);
        $offerings = $this->courseOfferingService->getAllBySession($sessionId);
        $data = array_map(fn($o) => $o->toArray(), $offerings);
        return JsonResponse::withJson($response, $data);
    }
}
