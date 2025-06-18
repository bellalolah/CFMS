<?php

namespace Cfms\Controllers;

use Cfms\Services\CourseService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CourseController extends BaseController
{

    public function __construct(private CourseService $courseService)
    {

    }

    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $id = $this->courseService->createCourse($data);
        $course = $this->courseService->getCourseById($id);
        // Ensure response is a CourseDto or error
        if ($course) {
            return JsonResponse::withJson($response, (array)$course, 201);
        }
        return JsonResponse::withJson($response, ['error' => 'Failed to create course'], 400);
    }

    public function getAll(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $courses = $this->courseService->getAllCourses();
        $data = array_map(fn($dto) => (array)$dto, $courses);
        return JsonResponse::withJson($response, $data);
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $course = $this->courseService->getCourseById((int)$args['id']);
        return JsonResponse::withJson($response, $course ? (array)$course : ['error' => 'Course not found'], $course ? 200 : 404);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $success = $this->courseService->updateCourse((int)$args['id'], $data);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $success = $this->courseService->deleteCourse((int)$args['id']);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function getForStudent(Request $request, Response $response, array $args): Response
    {
        // 1. Get the target student ID from the URL path.
        $studentId = (int)($args['id'] ?? 0);

        // 2. Authorization: A student can only view their own courses (or an admin can view anyone's).
        $user = (array)$request->getAttribute('user');
        if (($user['role_id'] ?? null) != 1 && ($user['id'] ?? null) != $studentId) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: You can only view your own courses.'], 403);
        }

        // 3. Get pagination parameters from the query string.
        $params = $request->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $perPage = (int)($params['per_page'] ?? 15);

        // 4. Call the service to get the data.
        $result = $this->courseService->getCoursesForStudent($studentId, $page, $perPage);

        if ($result === null) {
            return JsonResponse::withJson($response, ['error' => 'Student profile not found.'], 404);
        }

        // 5. Format the data for the JSON response.
        $result['data'] = array_map(fn($dto) => $dto->toArray(), $result['data']);

        return JsonResponse::withJson($response, $result);
    }
}
