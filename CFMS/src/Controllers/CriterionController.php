<?php
namespace Cfms\Controllers;

use Cfms\Services\CriterionService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CriterionController
{
    public function __construct(private CriterionService $service) {}

    public function getAll(Request $request, Response $response): Response
    {
        $criteria = $this->service->getAll();

        // Convert the model objects to arrays for the JSON response
        $data = array_map(fn($c) => (array)$c, $criteria);

        return JsonResponse::withJson($response, $data);
    }

    // In Cfms\Controllers\CriterionController.php

    public function create(Request $request, Response $response): Response
    {
        // 1. ADMIN-ONLY SECURITY CHECK
        $user = $request->getAttribute('user');
        if (is_object($user)) $user = (array)$user;

        // Assuming role_id 1 is Admin
        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }

        // 2. Get the data from the request body
        $input = $request->getParsedBody();
        if (!is_array($input)) {
            $input = (array)$input;
        }

        // 3. Call the service to create the criterion
        try {
            $criterion = $this->service->create($input);

            if ($criterion) {
                // Return the new criterion object as an array with a 201 status
                return JsonResponse::withJson($response, (array)$criterion, 201);
            }

            return JsonResponse::withJson($response, ['error' => 'Failed to create criterion.'], 500);

        } catch (\InvalidArgumentException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $id = $request->getAttribute('id');

        if (!$user || ($user['role_id'] ?? null) != 1) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: Admins only'], 403);
        }
        $input = $request->getParsedBody();

        if (!is_array($input)) {
            $input = (array)$input;
        }

        try {
            $criterion = $this->service->update($id,$input);
            if ($criterion) {
                return JsonResponse::withJson($response, (array)$criterion, 201);
            }
            return JsonResponse::withJson($response, ['error' => 'Failed to update criterion.'], 500);
        }
        catch (\InvalidArgumentException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400);
        }
    }
}