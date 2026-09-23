<?php

$data = [
    "laporan" => $_POST['laporan']
];

$ch = curl_init("http://localhost:5000/sentimen");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

echo "Hasil Sentimen: " . $result['sentimen'];

?>
