<?php

$data = [
    "tahun_unit" => $_POST['tahun_unit'],
    "jumlah_perbaikan" => $_POST['jumlah_perbaikan'],
    "jam_operasional" => $_POST['jam_operasional']
];

$ch = curl_init("http://localhost:5000/predict");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

echo "Hasil Prediksi: " . $result['prediksi'];

?>
