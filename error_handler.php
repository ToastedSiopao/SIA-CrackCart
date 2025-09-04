<?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'error' => [
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ]);
    die();
});
?>