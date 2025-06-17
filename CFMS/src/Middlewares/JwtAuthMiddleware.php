<?php
// File: src/Middleware/JwtAuthMiddleware.php

namespace Dell\Cfms\Middlewares;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JwtAuthMiddleware
{
    private string $jwtSecretKey;

    public function __construct()
    {
        // Make sure this is the same secret key used in your AuthController!
        $this->jwtSecretKey = getSecretKey();
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            return $this->createErrorResponse($request, 'Authentication token not provided.');
        }


        // The header should be in the format "Bearer <token>"
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // Try to decode the token. This will throw an exception if it'Utils invalid.
            $decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS256'));

            // Add the decoded token data (e.g., user ID) to the request as an attribute
            // so that the controller can access it.
            $userData = is_object($decoded->data) ? (array)$decoded->data : $decoded->data;
            $request = $request->withAttribute('user', $userData);

        } catch (Exception $e) {
            return $this->createErrorResponse($request, 'Invalid authentication token: ' . $e->getMessage());
        }

        // If the token is valid, pass the request along to the actual route handler.
        return $handler->handle($request);
    }

    private function createErrorResponse(Request $request, string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => true, 'message' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401); // 401 Unauthorized
    }
}