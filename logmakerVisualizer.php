<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$csvFile = 'system_metrics.csv';

$data = [];
if (($handle = fopen($csvFile, 'r')) !== false) {
    $headers = fgetcsv($handle, 0, ',', '"', '\\');

    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        if (count($row) !== count($headers)) continue;
        $data[] = array_combine($headers, $row);
    }

    fclose($handle);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Metrics Dashboard</title>

    <!-- Luxon -->
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.3/build/global/luxon.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Luxon adapter -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>

    <!-- Zoom plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.umd.min.js"></script>
    <!-- PapaParse plugin -->
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>

    <style>
        body { font-family: Arial; margin: 20px; }
        .chart-container { width: 95%; max-width: 1200px; margin: auto; }
        select, button { padding: 8px; margin: 5px; }
    </style>
</head>
<body>

<h1>System Metrics Dashboard</h1>

<div>
    <label for="machine">Select Machine:</label>
    <select id="machine">
        <option value="all">All Machines</option>
        <?php
        $machines = array_unique(array_column($data, 'machine'));
        foreach ($machines as $machine) {
            echo "<option value=\"$machine\">$machine</option>";
        }
        ?>
    </select>

    <button id="resetZoom">Reset Zoom</button>
</div>

<div class="chart-container">
    <canvas id="metricsChart"></canvas>
</div>

<script>
let rawData = <?php echo json_encode($data, JSON_UNESCAPED_SLASHES); ?>;
let metricsChart;

// Convert CSV rows into chart-ready objects
function prepareData(machine) {
    return rawData
        .filter(row => machine === 'all' || row.machine === machine)
        .map(row => ({
            x: row.timestamp,
            cpu_temp: Number(row.cpu_temp),
            ram_usage: Number(row.ram_usage),
            cpu_usage: Number(row.cpu_usage)
        }));
}

function renderChart(machine = 'all') {
    const chartData = prepareData(machine);
    const ctx = document.getElementById('metricsChart').getContext('2d');

    if (metricsChart) metricsChart.destroy();

    metricsChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: 'CPU Temperature (°C)',
                    data: chartData.map(d => ({ x: d.x, y: d.cpu_temp })),
                    borderColor: 'red',
                    tension: 0.2
                },
                {
                    label: 'RAM Usage (%)',
                    data: chartData.map(d => ({ x: d.x, y: d.ram_usage })),
                    borderColor: 'blue',
                    tension: 0.2
                },
                {
                    label: 'CPU Usage (%)',
                    data: chartData.map(d => ({ x: d.x, y: d.cpu_usage })),
                    borderColor: 'green',
                    tension: 0.2
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        parser: 'yyyy-MM-dd HH:mm:ss',
                        tooltipFormat: 'yyyy-MM-dd HH:mm:ss',
                        unit: 'minute'
                    }
                }
            },
            plugins: {
                zoom: {
                    zoom: {
                        wheel: { enabled: true },
                        pinch: { enabled: true },
                        mode: 'x'
                    },
                    pan: {
                        enabled: true,
                        mode: 'x'
                    }
                }
            }
        }
    });
}

// Machine filter
document.getElementById('machine').addEventListener('change', () => {
    renderChart(document.getElementById('machine').value);
});

// Reset zoom
document.getElementById('resetZoom').addEventListener('click', () => {
    metricsChart.resetZoom();
});

// Live updates every 10 seconds
setInterval(async () => {
    const response = await fetch('system_metrics.csv?' + Date.now());
    const text = await response.text();

    const parsed = Papa.parse(text, {
        header: true,
        skipEmptyLines: true
    });

    rawData = parsed.data;

    renderChart(document.getElementById('machine').value);
}, 10000);


// Initial render
renderChart();
</script>

</body>
</html>
