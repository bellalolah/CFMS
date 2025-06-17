<?php

namespace Cfms\Utils;

use Psr\Http\Message\ResponseInterface;

class JsonResponse
{
    public static function withJson(ResponseInterface $response, array $data, int $statusCode = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
