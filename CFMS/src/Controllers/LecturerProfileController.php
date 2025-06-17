<?php

namespace Cfms\Controllers;

use Cfms\Services\LecturerProfileService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LecturerProfileController
{

    public function __construct(private LecturerProfileService $service)
    {

    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)($args['user_id'] ?? 0);
        $input = $request->getParsedBody();
        $result = $this->service->completeLecturerProfile($userId, $input);
        return JsonResponse::withJson($response, $result, $result['success'] ? 201 : 400);
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)($args['user_id'] ?? 0);
        $profile = $this->service->getProfile($userId);
        if ($profile) {
            return JsonResponse::withJson($response, (array)$profile, 200);
        }
        return JsonResponse::withJson($response, ['success' => false, 'message' => 'Profile not found'], 404);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)($args['user_id'] ?? 0);
        $input = $request->getParsedBody();
        $result = $this->service->updateProfile($userId, $input);
        return JsonResponse::withJson($response, $result, $result['success'] ? 200 : 400);
    }
}

