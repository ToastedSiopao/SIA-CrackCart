<?php
function log_action($action, $message) {
    $log_file = __DIR__ . '/../activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [{$action}] {$message}\n";
    
    // Use FILE_APPEND to add the entry to the end of the file.
    // Use LOCK_EX to prevent concurrent writes from corrupting the file.
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>