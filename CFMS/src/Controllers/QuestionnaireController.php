<?php
namespace Cfms\Controllers;

use Cfms\Services\QuestionnaireService;
use Cfms\Utils\JsonResponse;
use Dell\Cfms\Exceptions\AuthorizationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class QuestionnaireController
{
    public function __construct(private QuestionnaireService $service) {}

    /**
     * Handles the creation of a new questionnaire with its questions.
     */
    // In Cfms\Controllers\QuestionnaireController.php

    public function create(Request $request, Response $response): Response
    {
        // 1. Get the authenticated user from the request attribute.
        $user = $request->getAttribute('user');
        if (!$user) {
            // This case should be handled by your middleware, but it's a good fallback.
            return JsonResponse::withJson($response, ['error' => 'Authentication required.'], 401);
        }
        // Ensure user is an array for consistent access
        $user = (array)$user;

        error_log("Loging from questionnaire controller: " . json_encode($user));
        $input = $request->getParsedBody();
        if (!is_array($input)) {
            $input = (array)$input;
        }

        try {
            // 2. Pass both the input AND the user to the service method.
            $dto = $this->service->create($input,$user);

            if ($dto) {
                return JsonResponse::withJson($response, $dto->toArray(), 201);
            }

            return JsonResponse::withJson($response, ['error' => 'Failed to create questionnaire.'], 500);

        } catch ( AuthorizationException $e) {
            // Catch our specific authorization error
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 403); // 403 Forbidden
        } catch (\InvalidArgumentException $e) {
            // Catch validation errors
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400); // 400 Bad Request
        } catch (\Exception $e) {
            // Catch any other unexpected server errors
            return JsonResponse::withJson($response, ['error' => 'An unexpected server error occurred.'], 500);
        }
    }
    /**
     * Handles retrieving a single questionnaire with all its details.
     */
    public function getById(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        if ($id <= 0) {
            return JsonResponse::withJson($response, ['error' => 'Invalid ID specified'], 400);
        }

        $dto = $this->service->getWithDetails($id);

        if ($dto) {
            return JsonResponse::withJson($response, $dto->toArray());
        }

        return JsonResponse::withJson($response, ['error' => 'Questionnaire not found'], 404);
    }

    // In Cfms\Controllers\QuestionnaireController.php

    public function getAll(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $perPage = (int)($params['per_page'] ?? 15);

        $result = $this->service->getPaginated($page, $perPage);

        // The DTOs need to be converted to arrays for the final JSON
        $result['data'] = array_map(fn($dto) => $dto->toArray(), $result['data']);

        return JsonResponse::withJson($response, $result);
    }


    // In Cfms\Controllers\QuestionnaireController.php
    public function updateQuestionnaire(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $user = (array)$request->getAttribute('user');
        $input = (array)$request->getParsedBody();

        try {
            $dto = $this->service->update($id, $input, $user);
            if ($dto) {
                return JsonResponse::withJson($response, $dto->toArray());
            }
            return JsonResponse::withJson($response, ['error' => 'Failed to update questionnaire.'], 500);
        } catch (AuthorizationException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400);
        }
    }

    // In Cfms\Controllers\QuestionnaireController.php
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $user = (array)$request->getAttribute('user');
        $input = (array)$request->getParsedBody();
        $newStatus = $input['status'] ?? '';


        try {
            $success = $this->service->updateStatus($id, $newStatus, $user);
            if ($success) {
                return JsonResponse::withJson($response, ['success' => true, 'message' => 'Status updated successfully.']);
            }
            return JsonResponse::withJson($response, ['error' => 'Failed to update status.'], 500);
        } catch (AuthorizationException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400);
        }
    }

    // In Cfms\Controllers\QuestionnaireController.php

    public function getPendingForStudent(Request $request, Response $response, array $args): Response
    {
        // 1. Get the target student ID from the URL path.
        $studentId = (int)($args['id'] ?? 0);

        // 2. Authorization: A user can only view their own pending list (or an admin can view anyone's).
        $user = (array)$request->getAttribute('user');
        if (($user['role_id'] ?? null) != 1 && ($user['id'] ?? null) != $studentId) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: You can only view your own pending questionnaires.'], 403);
        }

        // 3. Get pagination parameters from the query string.
        $params = $request->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $perPage = (int)($params['per_page'] ?? 15);

        // 4. Call the service to get the data. We pass the whole $user object
        // because the service needs it to find the student's profile/department.
        $result = $this->service->getPendingForStudent($user, $page, $perPage);

        // 5. Format the data for the JSON response.
        $result['data'] = array_map(fn($dto) => $dto->toArray(), $result['data']);

        return JsonResponse::withJson($response, $result);
    }

    // In your QuestionnaireController.php
    public function getQuestionnaireWithGroupedCriteriaAndPerformance(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);

        try {
            // Use the new service method
            $questionnaireDto = $this->service->getWithGroupedCriteriaAndPerformance($id);

            if (!$questionnaireDto) {
                // Return a 404 Not Found response
                //getQuestionnaireWithGroupedCriteriaAndPerformance
                return JsonResponse::withJson($response, ['error' => 'Questionnaire not found'], 404);
            }

            // The toArray() method in the new DTOs will handle the conversion
            return JsonResponse::withJson($response, $questionnaireDto->toArray());

        } catch (\Exception $e) {
            // Log the error and return a 500 server error
            error_log($e->getMessage());
            return JsonResponse::withJson($response,['error' => 'An unexpected error occurred'], 500);
        }
    }
}