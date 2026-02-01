<?php
// Specify the CSV file path
$csvFile = 'system_metrics.csv';

// Get all parameters from the request
$machine = isset($_REQUEST['machine']) ? $_REQUEST['machine'] : 'unknown';
$temp    = isset($_REQUEST['temp'])    ? $_REQUEST['temp']    : 'NA';
$ram     = isset($_REQUEST['ram'])     ? $_REQUEST['ram']     : 'NA';
$cpu     = isset($_REQUEST['cpu'])     ? $_REQUEST['cpu']     : 'NA';

// Ensure the CSV file exists and has headers if it's new
if (!file_exists($csvFile)) {
    $headers = ['timestamp', 'machine', 'cpu_temp', 'ram_usage', 'cpu_usage'];
    $file = fopen($csvFile, 'w');
    fputcsv($file, $headers);
    fclose($file);
}

// Open the CSV file in append mode
$file = fopen($csvFile, 'a');

// Prepare the row data
$row = [
    date('Y-m-d H:i:s'),
    $machine,
    $temp,
    $ram,
    $cpu
];

// Append the row to the CSV file
fputcsv($file, $row);
fclose($file);

// Send a response
http_response_code(200);
echo "Data logged: Machine=$machine, Temp=$temp, RAM=$ram, CPU=$cpu";
?>
