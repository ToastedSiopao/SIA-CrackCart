<?php
error_reporting(0); // Disable default error reporting to prevent non-JSON output

// Function to handle exceptions
function exception_handler($exception) {
    // If headers have already been sent, we can't send a new one
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Uncaught Exception: ' . $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    exit();
}

// Function to handle errors
function error_handler($errno, $errstr, $errfile, $errline) {
    // Respect error_reporting level
    if (!(error_reporting() & $errno)) {
        return;
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => "Error: [$errno] $errstr",
        'file' => $errfile,
        'line' => $errline
    ]);
    exit();
}

// Function to handle fatal errors (shutdown)
function shutdown_handler() {
    $error = error_get_last();
    // Check for a fatal error
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode([
            'status' => 'error',
            'message' => "Fatal Error: [{$error['type']}] {$error['message']}",
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
}

set_exception_handler('exception_handler');
set_error_handler('error_handler');
register_shutdown_function('shutdown_handler');

?>