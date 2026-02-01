<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$csvFile = 'system_metrics.csv';

// Read CSV
$data = [];
if (($handle = fopen($csvFile, 'r')) !== false) {
    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        if (count($row) !== count($headers)) continue;
        $data[] = array_combine($headers, $row);
    }
    fclose($handle);
}

// Machine list
$machines = array_unique(array_column($data, 'machine'));
sort($machines);

// Selected machine
$selectedMachine = $_GET['machine'] ?? 'all';

// Selected time range
$range = $_GET['range'] ?? 'all';

// Convert timestamps
foreach ($data as &$row) {
    $row['ts'] = strtotime($row['timestamp']);
}
unset($row);

// Time filtering
$now = time();
$cutoff = 0;

switch ($range) {
    case 'minute': $cutoff = $now - 60; break;
    case 'hour':   $cutoff = $now - 3600; break;
    case 'day':    $cutoff = $now - 86400; break;
    case 'week':   $cutoff = $now - 604800; break;
    default:       $cutoff = 0; break;
}

$filtered = array_values(array_filter($data, function ($row) use ($selectedMachine, $cutoff) {
    if ($row['ts'] < $cutoff) return false;
    if ($selectedMachine !== 'all' && $row['machine'] !== $selectedMachine) return false;
    return true;
}));

// ASCII chart generator (dash‑only)
function ascii_chart($rows, $field, $title) {
    if (empty($rows)) return "No data.\n";

    $values = array_map('floatval', array_column($rows, $field));
    $min = floor(min($values));
    $max = ceil(max($values));
    $height = 12;

    // Downsample to max 80 columns
    $maxWidth = 80;
    $step = max(1, intdiv(count($rows), $maxWidth));

    $points = [];
    $timestamps = [];

    for ($i = 0; $i < count($rows); $i += $step) {
        $points[] = floatval($rows[$i][$field]);
        $timestamps[] = $rows[$i]['timestamp'];
    }

    $width = count($points);

    // Map value to row
    $map = function($v) use ($min, $max, $height) {
        if ($max == $min) return intdiv($height, 2);
        $ratio = ($v - $min) / ($max - $min);
        return $height - 1 - (int)round($ratio * ($height - 1));
    };

    $grid = array_fill(0, $height, array_fill(0, $width, ' '));

    // Plot using dashes only
    for ($x = 0; $x < $width; $x++) {
        $y = $map($points[$x]);
        $grid[$y][$x] = '-';
    }

    // Build output
    $out = "\n$title\n\n";
    for ($row = 0; $row < $height; $row++) {
        $label = $max - ($max - $min) * $row / ($height - 1);
        $out .= sprintf("%6.1f | ", $label);
        $out .= implode('', $grid[$row]) . "\n";
    }
    $out .= "       +" . str_repeat("-", $width) . "\n";

    // Timestamp legend (5 evenly spaced)
    $legendCount = 5;
    $legend = [];
    for ($i = 0; $i < $legendCount; $i++) {
        $idx = (int)round($i * ($width - 1) / ($legendCount - 1));
        $legend[] = $timestamps[$idx];
    }

    $out .= "       Legend: " . implode("   ", $legend) . "\n";

    return $out;
}

// Build charts
$chartCpu  = ascii_chart($filtered, 'cpu_temp',  "CPU Temperature (°C)");
$chartRam  = ascii_chart($filtered, 'ram_usage', "RAM Usage (%)");
$chartLoad = ascii_chart($filtered, 'cpu_usage', "CPU Usage (%)");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ASCII System Metrics Dashboard</title>
<style>
    body { font-family: monospace; margin: 20px; }
    pre { background: #111; color: #0f0; padding: 10px; white-space: pre-wrap; }
    select, button { padding: 4px; margin: 4px; }
    .range-buttons button { margin-right: 6px; }
</style>
</head>
<body>

<h1>ASCII System Metrics Dashboard</h1>

<form method="get">
    <label for="machine">Machine:</label>
    <select name="machine" id="machine">
        <option value="all"<?= $selectedMachine === 'all' ? ' selected' : '' ?>>All</option>
        <?php foreach ($machines as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>"<?= $selectedMachine === $m ? ' selected' : '' ?>>
                <?= htmlspecialchars($m) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="range-buttons">
        <button name="range" value="minute">Last Minute</button>
        <button name="range" value="hour">Last Hour</button>
        <button name="range" value="day">Last Day</button>
        <button name="range" value="week">Last Week</button>
        <button name="range" value="all">All Time</button>
    </div>
</form>

<pre><?= htmlspecialchars($chartCpu . $chartRam . $chartLoad) ?></pre>

</body>
</html>
