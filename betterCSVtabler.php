<?php
// ===== CONFIG: define your CSV files here =====
$csvFiles = [
    'name' => 'name.csv',
    'name2'   => 'name2.csv'
];

// Selected file
$selectedKey = $_GET['file'] ?? array_key_first($csvFiles);

// Validate selection
if (!array_key_exists($selectedKey, $csvFiles)) {
    $selectedKey = array_key_first($csvFiles);
}

$selectedFile = $csvFiles[$selectedKey];

$headers = [];
$data = [];

// Load CSV
if (file_exists($selectedFile)) {
    if (($handle = fopen($selectedFile, "r")) !== false) {
        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $data[] = $row;
        }
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CSV Viewer</title>
<style>
body { font-family: Arial; margin: 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; }
th { background: #f2f2f2; cursor: pointer; }
th.sort-asc::after { content: " ↑"; }
th.sort-desc::after { content: " ↓"; }
.search { margin-bottom: 10px; padding: 8px; width: 300px; }
select { margin-bottom: 10px; padding: 6px; }
</style>
</head>
<body>

<form method="get">
    <label>Select CSV:</label>
    <select name="file" onchange="this.form.submit()">
        <?php foreach ($csvFiles as $label => $file): ?>
            <option value="<?= htmlspecialchars($label) ?>"
                <?= $label === $selectedKey ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<input type="text" id="search" class="search" placeholder="Search...">

<table id="csvTable">
    <thead></thead>
    <tbody></tbody>
</table>

<script>
let currentHeaders = <?php echo json_encode($headers); ?>;
let currentData = <?php echo json_encode($data); ?>;

let sortColumn = null;
let sortDirection = 1;

function renderTable(headers, data) {
    const thead = document.querySelector('#csvTable thead');
    const tbody = document.querySelector('#csvTable tbody');

    thead.innerHTML = '';
    tbody.innerHTML = '';

    const tr = document.createElement('tr');

    headers.forEach((h, i) => {
        const th = document.createElement('th');
        th.textContent = h;
        th.onclick = () => sortTable(i);

        if (i === sortColumn) {
            th.classList.add(sortDirection === 1 ? 'sort-asc' : 'sort-desc');
        }

        tr.appendChild(th);
    });

    thead.appendChild(tr);

    data.forEach(row => {
        const tr = document.createElement('tr');
        row.forEach(cell => {
            const td = document.createElement('td');
            td.textContent = cell;
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

function sortTable(col) {
    if (sortColumn === col) {
        sortDirection *= -1;
    } else {
        sortColumn = col;
        sortDirection = 1;
    }

    currentData.sort((a, b) => {
        return (a[col] || '').localeCompare(b[col] || '', undefined, {numeric: true}) * sortDirection;
    });

    renderTable(currentHeaders, currentData);
}

function filterTable() {
    const term = document.getElementById('search').value.toLowerCase();

    const filtered = currentData.filter(row =>
        row.some(cell => (cell || '').toLowerCase().includes(term))
    );

    renderTable(currentHeaders, filtered);
}

document.getElementById('search').addEventListener('keyup', filterTable);

// Initial render
renderTable(currentHeaders, currentData);
</script>

</body>
</html>
