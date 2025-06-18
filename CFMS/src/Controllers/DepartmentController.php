<?php

namespace Cfms\Controllers;

use Cfms\Services\DepartmentService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DepartmentController extends BaseController
{

    public function __construct(private DepartmentService $departmentService)
    {
    }

    public function getAll(Request $request, Response $response): Response
    {
        $departments = $this->departmentService->getAllDepartments();
        return JsonResponse::withJson($response, $departments);
    }

    public function getAllInfo(Request $request, Response $response): Response
    {
        $departments = $this->departmentService->getAllDepartmentInfo();
        $data = array_map(fn($dto) => (array)$dto, $departments);
        return JsonResponse::withJson($response, $data);
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        $department = $this->departmentService->getDepartmentById((int)$args['id']);
        return JsonResponse::withJson($response, $department);
    }

    public function getByName(Request $request, Response $response, array $args): Response
    {
        $departments = $this->departmentService->getDepartmentByName($args['name']);
        return JsonResponse::withJson($response, $departments);
    }

    public function getByFacultyId(Request $request, Response $response, array $args): Response
    {
        $departments = $this->departmentService->getDepartmentsByFacultyId((int)$args['faculty_id']);
        return JsonResponse::withJson($response, $departments);
    }

    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        // Ensure $user is always an array
        if (is_object($user)) {
            $user = (array)$user;
        }
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        if (is_object($data)) {
            $data = (array)$data;
        }
        $id = $this->departmentService->createDepartment($data);
        return JsonResponse::withJson($response, ['id' => $id], 201);
    }

    public function createMany(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        if (is_object($user)) {
            $user = (array)$user;
        }
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $departments = $request->getParsedBody();
        if (is_object($departments)) {
            $departments = (array)$departments;
        }
        $ids = $this->departmentService->createDepartments($departments);
        // Fetch the created departments' names and ids
        $createdDepartments = [];
        foreach ($ids as $id) {
            $department = $this->departmentService->getDepartmentById($id);
            if ($department) {
                $createdDepartments[] = [
                    'id' => $department->id ?? (is_array($department) ? $department['id'] : null),
                    'name' => $department->name ?? (is_array($department) ? $department['name'] : null)
                ];
            }
        }
        return JsonResponse::withJson($response, $createdDepartments, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $success = $this->departmentService->updateDepartment((int)$args['id'], $data);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $success = $this->departmentService->deleteDepartment((int)$args['id']);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function getAllWithCourses(Request $request, Response $response): Response
    {
        // 1. Call the new service method
        $departmentsWithCourses = $this->departmentService->getDepartmentsWithCourses();

        // 2. Convert the array of DTOs into an array of arrays for the JSON response
        $data = array_map(fn($dto) => $dto->toArray(), $departmentsWithCourses);

        return JsonResponse::withJson($response, $data);
    }
}