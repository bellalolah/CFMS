<?php

namespace Cfms\Controllers;

use Cfms\Dto\CourseDto;
use Cfms\Dto\DepartmentInfoDto;
use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\DepartmentRepository;
use Cfms\Repositories\FacultyRepository;
use Cfms\Services\CourseDepartmentService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CourseDepartmentController extends BaseController
{
    private CourseDepartmentService $courseDepartmentService;

    public function __construct(CourseDepartmentService $courseDepartmentService)
    {
        $this->courseDepartmentService = $courseDepartmentService;
    }

    public function assign(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $courseId = $data['course_id'] ?? null;
        $departmentId = $data['department_id'] ?? null;
        if (!$courseId || !$departmentId) {
            return JsonResponse::withJson($response, ['error' => 'course_id and department_id are required'], 400);
        }
        $id = $this->courseDepartmentService->assignCourseToDepartment($courseId, $departmentId);
        return JsonResponse::withJson($response, ['id' => $id], 201);
    }

    public function remove(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $courseId = $data['course_id'] ?? null;
        $departmentId = $data['department_id'] ?? null;
        if (!$courseId || !$departmentId) {
            return JsonResponse::withJson($response, ['error' => 'course_id and department_id are required'], 400);
        }
        $success = $this->courseDepartmentService->removeCourseFromDepartment($courseId, $departmentId);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function getDepartmentsForCourse(Request $request, Response $response, array $args): Response
    {
        $courseId = (int)$args['course_id'];
        $departments = $this->courseDepartmentService->getDepartmentsForCourse($courseId);
        $departmentRepo = new DepartmentRepository();
        $facultyRepo = new FacultyRepository();
        $dtos = [];
        foreach ($departments as $dept) {
            $department = $departmentRepo->findDepartmentById($dept->department_id);
            if ($department) {
                $faculty = $facultyRepo->findFacultyById($department->faculty_id);
                $dtos[] = new DepartmentInfoDto((object)$department, (object)$faculty);
            }
        }
        return  JsonResponse::withJson($response, $dtos);
    }

    public function getCoursesForDepartment(Request $request, Response $response, array $args): Response
    {
        $departmentId = (int)$args['department_id'];
        $courses = $this->courseDepartmentService->getCoursesForDepartment($departmentId);
        $courseRepo = new CourseRepository();
        $dtos = [];
        foreach ($courses as $c) {
            $course = $courseRepo->getCourseById($c->course_id);
            if ($course) {
                $dtos[] = new CourseDto($course);
            }
        }
        return JsonResponse::withJson($response, $dtos);
    }
}
