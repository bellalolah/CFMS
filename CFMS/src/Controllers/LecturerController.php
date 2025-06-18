<?php

namespace Cfms\Controllers;

use Cfms\Services\LecturerProfileService;
use Cfms\Services\QuestionnaireService;
use Cfms\Utils\JsonResponse;
use Dell\Cfms\Exceptions\AuthorizationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LecturerController
{

    public function __construct(private LecturerProfileService $service, private QuestionnaireService $questionnaireService)
    {

    }

    public function create(Request $request, Response $response, array $args):Response
    {
        $userId = (int)($args['user_id'] ?? 0);
        $input = $request->getParsedBody();
        $result = $this->service->completeLecturerProfile($userId, $input);
        return JsonResponse::withJson($response, $result, $result['success'] ? 201 : 400);
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $userId = (int)($args['user_id'] ?? 0);
        $profile = $this->service->getProfile($userId);
        if ($profile) {
            return JsonResponse::withJson($response, (array)$profile, 200);
        }
        return JsonResponse::withJson($response, ['success' => false, 'message' => 'Profile not found'], 404);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = (int)($args['user_id'] ?? 0);
        $input = $request->getParsedBody();
        $result = $this->service->updateProfile($userId, $input);
        return JsonResponse::withJson($response, $result, $result['success'] ? 200 : 400);
    }


    public function getQuestionnaires(Request $request, Response $response, array $args): Response
    {
        // 1. Get the target lecturer ID from the URL path.
        $lecturerId = (int)($args['id'] ?? 0);

        // 2. Get the currently logged-in user's data.
        $user = (array)$request->getAttribute('user');

        // --- START: Corrected Authorization Logic ---

        $loggedInUserRole = $user['role_id'] ?? null;
        $loggedInUserId = $user['id'] ?? null;

        // The user is NOT authorized if they are NOT an admin (role 1)
        // AND their ID does NOT match the ID from the URL.
        if ($loggedInUserRole != 1 && $loggedInUserId != $lecturerId) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: You can only view your own questionnaires.'], 403);
        }

        // --- END: Corrected Authorization Logic ---

        // 3. Get pagination parameters.
        $params = $request->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $perPage = (int)($params['per_page'] ?? 15);

        // 4. Call the service to get the data.
        $result = $this->questionnaireService->getByLecturerPaginated($lecturerId, $page, $perPage);

        // 5. Format the data for the JSON response.
        $result['data'] = array_map(fn($dto) => $dto->toArray(), $result['data']);

        return JsonResponse::withJson($response, $result);
    }
}

