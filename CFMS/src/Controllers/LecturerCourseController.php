<?php
namespace Cfms\Controllers;

use Cfms\Services\LecturerCourseService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LecturerCourseController extends BaseController
{
    public function __construct(private LecturerCourseService $service) {}

    // Admin-only: Assign multiple courses to a lecturer
    public function assign(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $lecturerId = $data['lecturer_id'] ?? null;
        $courseIds = $data['course_ids'] ?? null;
        if (!$lecturerId || !is_array($courseIds) || empty($courseIds)) {
            return JsonResponse::withJson($response, ['error' => 'lecturer_id and course_ids[] are required'], 400);
        }
        $ids = $this->service->assignCourses($lecturerId, $courseIds);
        return JsonResponse::withJson($response, $ids, 201);
    }

    // Admin-only: Unassign multiple courses from a lecturer
    public function unassign(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $lecturerId = $data['lecturer_id'] ?? null;
        $courseIds = $data['course_ids'] ?? null;
        if (!$lecturerId || !is_array($courseIds) || empty($courseIds)) {
            return JsonResponse::withJson($response, ['error' => 'lecturer_id and course_ids[] are required'], 400);
        }
        $count = $this->service->unassignCourses($lecturerId, $courseIds);
        return JsonResponse::withJson($response, ['unassigned' => $count]);
    }

    // Get all courses for a lecturer (accessible to all authenticated users)
    public function getCourses(Request $request, Response $response, array $args): Response
    {
        $lecturerId = (int)($args['lecturer_id'] ?? 0);
        $courses = $this->service->getCoursesForLecturer($lecturerId);
        $data = array_map(fn($dto) => (array)$dto, $courses);
        return JsonResponse::withJson($response, $data);
    }
}
