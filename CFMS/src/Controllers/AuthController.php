<?php

namespace Cfms\Controllers;

use Cfms\Services\AuthService;
use Cfms\Utils\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends BaseController
{
    // The constructor now requires an AuthService instance.
    // We use the same clean "constructor property promotion" syntax.
    public function __construct(private AuthService $authService)
    {
        // The body is empty. PHP automatically assigns $this->authService.
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // This line now works perfectly because $this->authService is guaranteed to be set.
        $result = $this->authService->authenticate($data);

        return JsonResponse::withJson($response, $result, $result['success'] ? 200 : 401);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $result = $this->authService->registerUser($data);

        return JsonResponse::withJson($response, $result, $result['success'] ? 200 : 401);
    }
}