<?php

namespace Cfms\Controllers;

use Cfms\Services\StudentProfileService;
use Cfms\Services\UserService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{

    public function __construct(private UserService $userService){}

    // Student self-registration (no admin required)
    public function register(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        $result = $this->userService->registerUser($input);
        $status = $result['success'] ? 201 : 400;
        return JsonResponse::withJson($response, $result, $status);
    }

    // Get user with their profile (student or lecturer)
    public function getUserWithProfile(Request $request, Response $response, array $args): Response
    {
        $userId = (int)($args['user_id'] ?? 0);
        $result = $this->userService->getUserWithProfile($userId);
        $status = $result ? 200 : 404;
        // Convert DTO to array for JSON response
        $data = $result ? (array)$result : ['error' => 'User not found'];
        return JsonResponse::withJson($response, $data, $status);
    }

    // Get user info only (no profile)
    public function getUserInfo(Request $request, Response $response, array $args): Response
    {
        $userId = (int)($args['user_id'] ?? 0);
        $result = $this->userService->getUserInfo($userId);
        $status = $result ? 200 : 404;
        // Convert DTO to array for JSON response
        $data = $result ? (array)$result : ['error' => 'User not found'];
        return JsonResponse::withJson($response, $data, $status);
    }

    // Get paginated users
    public function getPaginatedUsers(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $perPage = isset($params['per_page']) ? (int)$params['per_page'] : 10;
        $result = $this->userService->getPaginatedUsers($page, $perPage);
        // Convert DTOs to arrays for JSON response
        $result['data'] = array_map(fn($dto) => (array)$dto, $result['data']);
        return JsonResponse::withJson($response, $result, 200);
    }

    // Admin-only: Create a lecturer
    public function createLecturer(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $input = $request->getParsedBody();
        if (is_object($input)) $input = (array)$input;
        $input['role_id'] = 2; // Force role to lecturer (2 is lecturer)
        $result = $this->userService->registerUser($input);
        if ($result['success']) {
            $lecturer = $this->userService->getUserInfo($result['user_id']);
            return JsonResponse::withJson($response, (array)$lecturer, 201);
        }
        return JsonResponse::withJson($response, $result, 400);
    }

    // Get all lecturers
    public function getLecturers(Request $request, Response $response): Response
    {
        $lecturers = $this->userService->getLecturers();
        $data = array_map(fn($dto) => (array)$dto, $lecturers);
        return JsonResponse::withJson($response, $data);
    }

    // Get all lecturers with their profile and courses
    public function getLecturersWithCourses(Request $request, Response $response): Response
    {
        $lecturers = $this->userService->getLecturersWithCourses();
        $data = array_map(fn($dto) => (array)$dto, $lecturers);
        return JsonResponse::withJson($response, $data);
    }

    // Admin-only: Delete a lecturer
    public function deleteLecturer(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $lecturerId = (int)($args['lecturer_id'] ?? 0);
        if (!$lecturerId) {
            return JsonResponse::withJson($response, ['error' => 'Lecturer ID required'], 400);
        }
        $lecturer = $this->userService->getUserInfo($lecturerId);
        if (!$lecturer || $lecturer->role_id != 2) {
            return JsonResponse::withJson($response, ['error' => 'Lecturer not found'], 404);
        }
        $success = $this->userService->deleteUser($lecturerId);
        return JsonResponse::withJson($response, ['success' => $success], $success ? 200 : 500);
    }

}
