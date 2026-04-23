<?php

$csvFile = 'filename.csv';

$channel = $_POST['channel'] ?? 'NA';
$user    = $_POST['user'] ?? 'NA';
$message = $_POST['message'] ?? 'NA';

if (!file_exists($csvFile)) {
    $file = fopen($csvFile, 'w');
    if (!$file) {
        http_response_code(500);
        echo "Failed to create file";
        exit;
    }
    fputcsv($file, ['timestamp', 'channel', 'user', 'message']);
    fclose($file);
}

$file = fopen($csvFile, 'a');
if (!$file) {
    http_response_code(500);
    echo "Failed to open file";
    exit;
}

fputcsv($file, [
    date('Y-m-d H:i:s'),
    $channel,
    $user,
    $message
]);

fclose($file);

http_response_code(200);
echo "OK";
