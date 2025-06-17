<?php
// File: src/Middleware/JsonErrorRenderer.php

namespace Cfms\Middlewares;

use Throwable;

class JsonErrorRenderer
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $payload = ['error' => true];
        $payload['message'] = $this->getFriendlyMessage($exception);
        // Optionally, log the real error details for debugging (not shown to user)
        error_log($exception);
        // Optionally, add a unique error code for tracking (not internal details)
        $payload['code'] = $this->getErrorCode($exception);
        return json_encode($payload, JSON_PRETTY_PRINT);
    }

    private function getFriendlyMessage(Throwable $exception): string
    {
        if ($exception instanceof \Cfms\Core\DatabaseConnectionException) {
            return 'Unable to connect to the database. Please try again later.';
        }
        if ($exception instanceof \PDOException) {
            return 'A database error occurred. Please try again later.';
        }
        if ($exception instanceof \DomainException) {
            return $exception->getMessage();
        }
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            return 'The requested resource was not found.';
        }
        if ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
            return 'The requested HTTP method is not allowed for this resource.';
        }
        if ($exception instanceof \InvalidArgumentException) {
            return 'Invalid input provided.';
        }
        if ($exception instanceof \RuntimeException) {
            return 'A server error occurred. Please try again later.';
        }
        // Add more custom cases as needed
        return 'An unexpected error occurred. Please try again later.';
    }

    private function getErrorCode(Throwable $exception): string
    {
        if ($exception instanceof \Cfms\Core\DatabaseConnectionException) {
            return 'DB_CONNECTION_ERROR';
        }
        if ($exception instanceof \PDOException) {
            return 'DB_ERROR';
        }
        if ($exception instanceof \DomainException) {
            return 'DOMAIN_ERROR';
        }
        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            return 'NOT_FOUND';
        }
        if ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
            return 'METHOD_NOT_ALLOWED';
        }
        if ($exception instanceof \InvalidArgumentException) {
            return 'INVALID_INPUT';
        }
        if ($exception instanceof \RuntimeException) {
            return 'RUNTIME_ERROR';
        }
        // Add more custom codes as needed
        return 'GENERIC_ERROR';
    }
}