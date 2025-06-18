<?php
namespace Cfms\Controllers;

use Cfms\Services\FeedbackSubmissionService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FeedbackSubmissionController
{
    public function __construct(private FeedbackSubmissionService $service) {}

    /**
     * Gets a paginated history of questionnaires submitted by a specific student.
     */
    public function getHistory(Request $request, Response $response, array $args): Response
    {
        // 1. Get the target student ID from the URL path.
        $studentId = (int)($args['id'] ?? 0);

        // 2. Authorization: A user can only view their own history (or an admin can view anyone's).
        $user = (array)$request->getAttribute('user');
        $loggedInUserId = $user['id'] ?? null;
        $loggedInUserRole = $user['role_id'] ?? null;

        if ($loggedInUserRole != 1 && $loggedInUserId != $studentId) {
            return JsonResponse::withJson($response, ['error' => 'Forbidden: You can only view your own submission history.'], 403);
        }

        // 3. Get pagination parameters from the query string.
        $params = $request->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $perPage = (int)($params['per_page'] ?? 15);

        // 4. Call the service to get the paginated history data.
        $result = $this->service->getHistoryForUser($studentId, $page, $perPage);

        // 5. The data is already an array of arrays from the service, so just return it.
        // If you create a DTO later, you would convert it to an array here.
        return JsonResponse::withJson($response, $result);
    }
}