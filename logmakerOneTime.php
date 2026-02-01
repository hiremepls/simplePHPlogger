<?php
// Specify the CSV file path
$csvFile = 'data.csv';

// Get all parameters from the request
$params = $_REQUEST; // Works for both GET and POST

// Ensure the CSV file exists and has headers if it's new
if (!file_exists($csvFile)) {
    $headers = ['param1', 'param2', 'param3']; // Change these to match your parameters
    $file = fopen($csvFile, 'w');
    fputcsv($file, $headers);
    fclose($file);
}

// Open the CSV file in append mode
$file = fopen($csvFile, 'a');

// Prepare the row data
$row = [];
foreach ($headers as $header) {
    $row[] = isset($params[$header]) ? $params[$header] : '';
}

// Append the row to the CSV file
fputcsv($file, $row);
fclose($file);

// Send a response
http_response_code(200);
echo "Data logged successfully!";
?>
