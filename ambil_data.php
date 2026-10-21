<?php
// ======================================================
// PEMBARUAN & SINKRONISASI DATA PROGRAM STUDI PDDIKTI
// PoliteKNIK NEGERI LHOKSEUMAWE - REST API SYNC
// ======================================================

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
header('Content-Type: application/json; charset=utf-8');

// Resolusi direktori root
$rootDir = dirname(__DIR__);
if (!is_dir($rootDir)) {
    $rootDir = __DIR__;
}

if (!function_exists('resolve_data_target')) {
    function resolve_data_target($filename)
    {
        $candidates = [
            __DIR__ . '/' . $filename,
            dirname(__DIR__) . '/' . $filename,
            __DIR__ . '/api/' . $filename,
            dirname(__DIR__) . '/api/' . $filename,
        ];
        foreach ($candidates as $c) {
            if (file_exists($c)) return $c;
        }
        return dirname(__DIR__) . '/' . $filename;
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

$fileProdi = resolve_data_target('data_prodi.json');
$fileMeta  = dirname($fileProdi) . '/data_timestamp.json';
$tmpMeta   = sys_get_temp_dir() . '/pddikti_timestamp.json';
$tmpProdi  = sys_get_temp_dir() . '/data_prodi.json';

function get_latest_timestamp($fileProdi, $fileMeta, $tmpMeta, $tmpProdi)
{
    $times = [time() - 86400];
    if (file_exists($fileProdi)) $times[] = filemtime($fileProdi);
    if (file_exists($tmpProdi))  $times[] = filemtime($tmpProdi);
    if (file_exists($fileMeta)) {
        $meta = json_decode(@file_get_contents($fileMeta), true);
        if (isset($meta['last_updated'])) $times[] = (int)$meta['last_updated'];
    }
    if (file_exists($tmpMeta)) {
        $meta = json_decode(@file_get_contents($tmpMeta), true);
        if (isset($meta['last_updated'])) $times[] = (int)$meta['last_updated'];
    }
    return max($times);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? 'refresh');

// ------------------------------------------------------
// 1. PENGECEKAN STATUS (POLLING CEPAT TANPA LOAD EXTERNAL)
// ------------------------------------------------------
if ($action === 'status') {
    $mtime = get_latest_timestamp($fileProdi, $fileMeta, $tmpMeta, $tmpProdi);
    $count = 0;
    if (file_exists($fileProdi)) {
        $raw = @file_get_contents($fileProdi);
        $arr = json_decode($raw, true);
        if (is_array($arr)) $count = count($arr);
    } elseif (file_exists($tmpProdi)) {
        $raw = @file_get_contents($tmpProdi);
        $arr = json_decode($raw, true);
        if (is_array($arr)) $count = count($arr);
    }

    echo json_encode([
        'success'     => true,
        'action'      => 'status',
        'timestamp'   => $mtime,
        'updated_at'  => format_tanggal_indonesia($mtime),
        'total_prodi' => $count,
        'status'      => 'active'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------
// 2. PEMBARUAN DATA LANGSUNG DARI API PDDIKTI
// ------------------------------------------------------
$idProdiPT = getenv('PDDIKTI_PT_ID') ?: 'B7OPrCaLnSM1jPcSLp7xV7tkDT1uIOvMpNlCjwgA2bq_SvK93yHFHs1lZQmipdXooHANmg==';
$url = 'https://pddikti.kemdiktisaintek.go.id/api/pt/prodi/' . $idProdiPT . '/20251';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer: https://pddikti.kemdiktisaintek.go.id/',
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    http_response_code(502);
    echo json_encode([
        'success'   => false,
        'message'   => 'Gagal mengambil data dari API PDDIKTI.',
        'http_code' => $httpCode,
        'error'     => $curlErr ?: 'HTTP status tidak valid (' . $httpCode . ')',
        'timestamp' => time()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$hasil = json_decode($response, true);
if (!isset($hasil['data']) || !is_array($hasil['data']) || count($hasil['data']) === 0) {
    http_response_code(502);
    echo json_encode([
        'success'   => false,
        'message'   => 'Format respons API PDDIKTI kosong atau tidak sesuai.',
        'timestamp' => time()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$dataProdi = $hasil['data'];
$jsonContent = json_encode($dataProdi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$currentTime = time();
$formattedTime = format_tanggal_indonesia($currentTime);

// Simpan ke lokasi utama dan direktori terkait jika dapat ditulis
$savedTargets = [];

// 1. Root data_prodi.json
$rootFile = dirname(__DIR__) . '/data_prodi.json';
if (!file_exists($rootFile) && file_exists(__DIR__ . '/data_prodi.json')) {
    $rootFile = __DIR__ . '/data_prodi.json';
}
if (is_writable(dirname($rootFile)) || (file_exists($rootFile) && is_writable($rootFile))) {
    if (@file_put_contents($rootFile, $jsonContent, LOCK_EX) !== false) {
        @touch($rootFile, $currentTime);
        $savedTargets[] = $rootFile;
    }
}

// 2. api/data_prodi.json
$apiFile = dirname(__DIR__) . '/api/data_prodi.json';
if (!file_exists($apiFile) && file_exists(__DIR__ . '/api/data_prodi.json')) {
    $apiFile = __DIR__ . '/api/data_prodi.json';
}
if (file_exists(dirname($apiFile)) && (is_writable(dirname($apiFile)) || (file_exists($apiFile) && is_writable($apiFile)))) {
    if (@file_put_contents($apiFile, $jsonContent, LOCK_EX) !== false) {
        @touch($apiFile, $currentTime);
        $savedTargets[] = $apiFile;
    }
}

// 3. Fallback ke /tmp untuk kompatibilitas serverless Vercel read-only
@file_put_contents($tmpProdi, $jsonContent, LOCK_EX);
@touch($tmpProdi, $currentTime);

// Simpan metadata timestamp
$metaContent = json_encode([
    'last_updated'           => $currentTime,
    'last_updated_formatted' => $formattedTime,
    'total_prodi'            => count($dataProdi),
    'source'                 => 'api_pddikti'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$rootMeta = dirname($rootFile) . '/data_timestamp.json';
if (is_writable(dirname($rootMeta)) || (file_exists($rootMeta) && is_writable($rootMeta))) {
    @file_put_contents($rootMeta, $metaContent, LOCK_EX);
    @touch($rootMeta, $currentTime);
}
@file_put_contents($tmpMeta, $metaContent, LOCK_EX);
@touch($tmpMeta, $currentTime);

echo json_encode([
    'success'     => true,
    'message'     => 'Data Program Studi PDDIKTI berhasil diperbarui secara langsung.',
    'total_prodi' => count($dataProdi),
    'timestamp'   => $currentTime,
    'updated_at'  => $formattedTime
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
