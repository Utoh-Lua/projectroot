<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Path to the trigger file
$triggerFile = '../data_updated_trigger.txt'; // Adjust path as needed
$lastModifiedTime = 0;

// If sessions are used, close session to prevent locking
if (session_status() == PHP_SESSION_ACTIVE) {
    session_write_close();
}

while (true) {
    // Check if the trigger file has been modified
    if (file_exists($triggerFile)) {
        $currentModifiedTime = filemtime($triggerFile);
        if ($currentModifiedTime > $lastModifiedTime) {
            echo "event: data_update\n";
            echo "data: Document list has been updated.\n\n";
            ob_flush();
            flush();
            $lastModifiedTime = $currentModifiedTime;
        }
    }
    // Check every 2 seconds
    sleep(2);

    // Keep connection alive (optional, some browsers/servers might need this)
    // echo ": heartbeat\n\n";
    // ob_flush();
    // flush();
}
?>