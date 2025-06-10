<?php

// Set error reporting level
error_reporting(E_ALL);

// Handle normal PHP errors
set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "type" => "PHP Error",
        "message" => $message,
        "file" => $file,
        "line" => $line
    ]);
    exit;
});

// Handle fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "type" => "Fatal Error",
            "message" => $error['message'],
            "file" => $error['file'],
            "line" => $error['line']
        ]);
    }
});

// Handle uncaught exceptions
set_exception_handler(function ($exception) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "type" => "Uncaught Exception",
        "message" => $exception->getMessage(),
        "file" => $exception->getFile(),
        "line" => $exception->getLine()
    ]);
    exit;
});
