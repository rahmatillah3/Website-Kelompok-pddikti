<?php
$d = json_decode(file_get_contents('data_historis.json'), true);
echo "Keys in data_historis.json: " . implode(', ', array_keys($d)) . "\n";
echo "Total keys: " . count($d) . "\n";

// Check status_prodi values in data_prodi.json
$prodi = json_decode(file_get_contents('data_prodi.json'), true);
$statuses = [];
foreach ($prodi as $p) {
    $s = $p['status_prodi'] ?? 'N/A';
    $statuses[$s] = ($statuses[$s] ?? 0) + 1;
}
echo "Status Prodi values: \n";
foreach ($statuses as $s => $c) {
    echo "  $s: $c\n";
}
