<?php

namespace Cfms\Controllers;

use Cfms\Services\FacultyService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FacultyController extends BaseController
{
    private FacultyService $facultyService;

    public function __construct(FacultyService $facultyService)
    {
        $this->facultyService = $facultyService;
    }

    public function getAll(Request $request, Response $response): Response
    {
        $faculties = $this->facultyService->getAllFaculty();
        return JsonResponse::withJson($response, $faculties);
    }

    public function getAllWithoutDates(Request $request, Response $response): Response
    {
        $faculties = $this->facultyService->getAllFacultyWithoutDates();
        return JsonResponse::withJson($response, $faculties);
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        $faculty = $this->facultyService->getFacultyById((int)$args['id']);
        return JsonResponse::withJson($response, $faculty);
    }

    public function getByName(Request $request, Response $response, array $args): Response
    {
        $faculties = $this->facultyService->getFacultyByName($args['name']);
        return JsonResponse::withJson($response, $faculties);
    }

    public function create(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $role = is_array($user) ? ($user['role_id'] ?? null) : ($user->role ?? null);
        if (!$user || $role != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $data = $request->getParsedBody();
        $id = $this->facultyService->createFaculty($data);
        return JsonResponse::withJson($response, ['id' => $id], 201);
    }

    public function createMany(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $role = is_array($user) ? ($user['role_id'] ?? null) : ($user->role ?? null);
        if (!$user || $role != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $faculties = $request->getParsedBody();
        // Ensure $faculties is an array of associative arrays
        if (!is_array($faculties) || empty($faculties) || !is_array($faculties[0])) {
            return JsonResponse::withJson($response, ['error' => 'Invalid input format. Expecting an array of objects.'], 400);
        }
        $ids = $this->facultyService->createFaculties($faculties);
        // Fetch the created faculties' names and ids
        $createdFaculties = [];
        foreach ($ids as $id) {
            $faculty = $this->facultyService->getFacultyById($id);
            if ($faculty) {
                $createdFaculties[] = [
                    'id' => $faculty->id ?? (is_array($faculty) ? $faculty['id'] : null),
                    'name' => $faculty->name ?? (is_array($faculty) ? $faculty['name'] : null)
                ];
            }
        }
        return JsonResponse::withJson($response, $createdFaculties, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $success = $this->facultyService->updateFaculty((int)$args['id'], $data);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $success = $this->facultyService->deleteFaculty((int)$args['id']);
        return JsonResponse::withJson($response, ['success' => $success]);
    }

    public function getAllInfo(Request $request, Response $response): Response
    {
        $faculties = $this->facultyService->getAllFacultyInfo();
        $data = array_map(fn($dto) => (array)$dto, $faculties);
        return JsonResponse::withJson($response, $data);
    }

    // In Cfms\Controllers\FacultyController.php

    public function getAllWithDepartments(Request $request, Response $response): Response
    {
        // 1. Call the new service method
        $facultiesWithDepartments = $this->facultyService->getFacultiesWithDepartments();

        // 2. Convert the array of DTOs into a plain array for the JSON response
        $data = array_map(fn($dto) => $dto->toArray(), $facultiesWithDepartments);

        return JsonResponse::withJson($response, $data);
    }
}