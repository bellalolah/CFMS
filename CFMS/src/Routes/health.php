<?php
// In a route file like routes/hello.php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // The {name} part is a named placeholder for our path parameter.
    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {

        $name = $args['name']; // The key 'name' matches the placeholder {name}

        $payload = ['message' => "Hello, " . $name];

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/', function (Request $request, Response $response, array $args) {

        $payload = ['message' => "Hello"];

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    });
};