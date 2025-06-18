<?php
namespace Cfms\Controllers;

use Cfms\Services\FeedbackService;
use Cfms\Utils\JsonResponse;
use Dell\Cfms\Exceptions\AuthorizationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FeedbackController
{
    public function __construct(private FeedbackService $service) {}

    public function createFeedback(Request $request, Response $response, array $args): Response
    {
        //  Get the questionnaire ID from the URL path
        $questionnaireId = (int)($args['id'] ?? 0);

        // Get the authenticated user from the request
        $user = (array)$request->getAttribute('user');

        // Get the array of answers from the request body
        $answers = $request->getParsedBody();

        if (!is_array($answers)) {
            $answers = (array)$answers;
        }

        try {
            // Call the service to submit the feedback
            $success = $this->service->submitFeedback($questionnaireId, $answers, $user);

            if ($success) {
                return JsonResponse::withJson($response, ['success' => true, 'message' => 'Feedback submitted successfully.'], 201);
            }

            return JsonResponse::withJson($response, ['error' => 'Failed to submit feedback.'], 500);

        } catch (AuthorizationException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400);
        }
    }

    // In Cfms\Controllers\FeedbackController.php

    public function getForQuestion(Request $request, Response $response, array $args): Response
    {
        // Get IDs from the URL path
        $questionnaireId = (int)($args['qId'] ?? 0);
        $questionId = (int)($args['qtnId'] ?? 0);

        // Get pagination parameters from the query string
        $params = $request->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $perPage = (int)($params['per_page'] ?? 25);

        // Get the authenticated user
        $user = (array)$request->getAttribute('user');

        try {
            //  Call the service
            $result = $this->service->getFeedbacksForQuestion($questionnaireId, $questionId, $user, $page, $perPage);

            // Convert DTOs to arrays for the final JSON response
            $result['data'] = array_map(fn($dto) => $dto->toArray(), $result['data']);

            return JsonResponse::withJson($response, $result);

        } catch ( AuthorizationException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return JsonResponse::withJson($response, ['error' => $e->getMessage()], 400);
        }
    }

}