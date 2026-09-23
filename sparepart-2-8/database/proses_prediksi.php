<?php
header('Content-Type: application/json');
define('ML_API_URL', 'http://127.0.0.1:5000/predict');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Request body harus JSON valid']);
    exit;
}

$required = ['tahun_unit', 'jam_operasional', 'kondisi_terakhir', 'jumlah_perbaikan', 'lokasi_operasi', 'jenis_alat'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' wajib diisi sebelum prediksi bisa dijalankan"]);
        exit;
    }
}

$payload = [
    'tahun_unit'        => (int)$input['tahun_unit'],
    'jam_operasional'   => max(0, min(8000, (int)$input['jam_operasional'])),
    'kondisi_terakhir'  => $input['kondisi_terakhir'],
    'jumlah_perbaikan'  => max(0, min(10, (int)$input['jumlah_perbaikan'])),
    'lokasi_operasi'    => $input['lokasi_operasi'],
    'jenis_alat'        => $input['jenis_alat'],
];

$ch = curl_init(ML_API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5,
]);

$response  = curl_exec($ch);
$curl_err  = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_err) {
    http_response_code(503);
    echo json_encode([
        'error' => 'Tidak dapat terhubung ke ML API. Pastikan predict_kerusakan.py sedang berjalan.',
        'detail' => $curl_err,
    ]);
    exit;
}

http_response_code($http_code ?: 500);
echo $response;

