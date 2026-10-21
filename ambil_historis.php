<?php
// ======================================================
// PEMBARUAN & SINKRONISASI DATA HISTORIS PDDIKTI (5 TAHUN)
// Politeknik Negeri Lhokseumawe
// ======================================================

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

$format = $_GET['format'] ?? 'json';
if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
} else {
    header('Content-Type: text/plain; charset=utf-8');
}

if (!function_exists('resolve_data_file')) {
    function resolve_data_file($filename, $envVar = null)
    {
        if ($envVar) {
            $envPath = getenv($envVar);
            if ($envPath && file_exists($envPath)) {
                return $envPath;
            }
        }

        $candidates = [
            __DIR__ . '/' . $filename,
            dirname(__DIR__) . '/' . $filename,
            __DIR__ . '/api/' . $filename,
            dirname(__DIR__) . '/api/' . $filename,
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return __DIR__ . '/' . $filename;
    }
}

function format_tanggal_indonesia($timestamp)
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)] ?? date('F', $timestamp);
    $thn = date('Y', $timestamp);
    $jam = date('H:i:s', $timestamp);
    return "{$tgl} {$bln} {$thn}, {$jam} WIB";
}

$file = resolve_data_file('data_historis.json', 'DATA_HISTORIS_PATH');
$targetDir = dirname($file);
$tmpFile = sys_get_temp_dir() . '/data_historis.json';
$tmpMeta = sys_get_temp_dir() . '/pddikti_timestamp.json';

$action = $_GET['action'] ?? 'refresh';

// Status check cepat
if ($action === 'status') {
    $mtime = file_exists($file) ? filemtime($file) : time();
    if (file_exists($tmpFile) && filemtime($tmpFile) > $mtime) {
        $mtime = filemtime($tmpFile);
    }
    echo json_encode([
        'success'    => true,
        'action'     => 'status',
        'timestamp'  => $mtime,
        'updated_at' => format_tanggal_indonesia($mtime),
        'file'       => basename($file)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$idProdiPT = getenv('PDDIKTI_PT_ID') ?: 'B7OPrCaLnSM1jPcSLp7xV7tkDT1uIOvMpNlCjwgA2bq_SvK93yHFHs1lZQmipdXooHANmg==';
$periode = ['20211', '20221', '20231', '20241', '20251'];
$dataHistoris = [];
$errors = [];

foreach ($periode as $kode) {
    $url = 'https://pddikti.kemdiktisaintek.go.id/api/pt/prodi/' . $idProdiPT . '/' . $kode;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Referer: https://pddikti.kemdiktisaintek.go.id/',
            'Origin: https://pddikti.kemdiktisaintek.go.id'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        $errors[] = "Periode $kode: HTTP $httpCode " . ($curlErr ?: '');
        continue;
    }

    $hasil = json_decode($response, true);
    if (!isset($hasil['data']) || !is_array($hasil['data']) || count($hasil['data']) === 0) {
        $errors[] = "Periode $kode: Data tidak valid atau kosong";
        continue;
    }

    $dataHistoris[$kode] = $hasil['data'];
}

if (count($dataHistoris) === 0) {
    http_response_code(502);
    $res = [
        'success'   => false,
        'message'   => 'Gagal mengambil data historis dari API PDDIKTI.',
        'errors'    => $errors,
        'timestamp' => time()
    ];
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$currentTime = time();
$formattedTime = format_tanggal_indonesia($currentTime);

// Siapkan JSON
$json = json_encode($dataHistoris, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Simpan ke file lokal jika direktori dapat ditulis
if (is_writable($targetDir) || (file_exists($file) && is_writable($file))) {
    @file_put_contents($file, $json, LOCK_EX);
    @touch($file, $currentTime);

    $apiFile = dirname($file) . '/api/data_historis.json';
    if (file_exists(dirname($apiFile)) && (is_writable(dirname($apiFile)) || (file_exists($apiFile) && is_writable($apiFile)))) {
        @file_put_contents($apiFile, $json, LOCK_EX);
        @touch($apiFile, $currentTime);
    }
}

// Fallback selalu simpan ke /tmp
@file_put_contents($tmpFile, $json, LOCK_EX);
@touch($tmpFile, $currentTime);

// Simpan Metadata Timestamp
$metaContent = json_encode([
    'last_updated'           => $currentTime,
    'last_updated_formatted' => $formattedTime,
    'total_periode'          => count($dataHistoris),
    'source'                 => 'api_pddikti_historis'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$metaFile = $targetDir . '/data_timestamp.json';
if (is_writable($targetDir) || (file_exists($metaFile) && is_writable($metaFile))) {
    @file_put_contents($metaFile, $metaContent, LOCK_EX);
    @touch($metaFile, $currentTime);
}
@file_put_contents($tmpMeta, $metaContent, LOCK_EX);
@touch($tmpMeta, $currentTime);

if ($format === 'text') {
    echo "BERHASIL: " . count($dataHistoris) . " periode telah diperbarui pada {$formattedTime}.\n";
} else {
    echo json_encode([
        'success'       => true,
        'message'       => 'Data historis PDDIKTI berhasil diperbarui.',
        'total_periode' => count($dataHistoris),
        'timestamp'     => $currentTime,
        'updated_at'    => $formattedTime
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
exit;
