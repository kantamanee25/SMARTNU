<?php
header('Content-Type: application/json');

$url = "https://transit.nu.ac.th/allGPS"; // ลิงก์ API ตำแหน่งรถแบบเรียลไทม์

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
}
curl_close($ch);

echo $response;
?>
