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

    /**
     * Retrieves the full details for a single questionnaire.
     *
     * This method fetches a specific questionnaire by its ID, including all its
     * questions, criteria, and any associated course offering details.
     *
     * Authorization Rules:
     * 1. An Admin (role 1) can view any questionnaire.
     * 2. A Lecturer (role 2) can only view a questionnaire if the route's lecturer ID
     *    matches their own logged-in ID.
     * 3. An additional check ensures the requested questionnaire actually belongs
     *    to the lecturer specified in the route.
     */
    public function getQuestionnaireDetails(Request $request, Response $response, array $args): Response
    {
        // 1. Get IDs from the URL path.
        // The lecturer ID is used for authorization.
        // The questionnaire ID is the target resource.
        $lecturerId = (int)($args['id'] ?? 0);
        $questionnaireId = (int)($args['questionnaire_id'] ?? 0);

        // 2. Get the currently logged-in user's data.
        $user = (array)$request->getAttribute('user');
        $loggedInUserId = $user['id'] ?? null;
        $loggedInUserRole = $user['role_id'] ?? null;

        // 3. --- AUTHORIZATION CHECK ---
        // The user is NOT authorized if they are NOT an admin (role 1)
        // AND their ID does NOT match the lecturer ID from the URL.
        if ($loggedInUserRole != 1 && $loggedInUserId != $lecturerId) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: You are not authorized to view these details.'], 403);
        }

        // 4. Fetch the detailed questionnaire data using the existing service method.
        $detailsDto = $this->questionnaireService->getWithDetails($questionnaireId);

        // 5. Handle the "Not Found" case.
        if (!$detailsDto) {
            return JsonResponse::withJson($response, ['error' => 'Questionnaire not found.'], 404);
        }

        // 6. --- OWNERSHIP VERIFICATION (for non-admins) ---
        // This prevents a lecturer from accessing another lecturer's questionnaire
        // by guessing the questionnaire ID.
        if ($loggedInUserRole != 1) {
            $isCreator = ($detailsDto->created_by_user_id === $lecturerId);
            $isCourseLecturer = (isset($detailsDto->course_offering['lecturer']['id']) && $detailsDto->course_offering['lecturer']['id'] === $lecturerId);

            if (!$isCreator && !$isCourseLecturer) {
                return JsonResponse::withJson($response, ['error' => 'This questionnaire does not belong to the specified lecturer.'], 403);
            }
        }

        // 7. Return the successful response.
        // The DTO's toArray() method prepares the data perfectly for JSON.
        return JsonResponse::withJson($response, $detailsDto->toArray(), 200);
    }
}

