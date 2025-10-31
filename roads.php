<?php
// ตั้งค่าการเชื่อมต่อกับฐานข้อมูล PostgreSQL
$host = "localhost";
$port = "5432";       
$dbname = "test_pgrouting"; 
$user = "postgres";   
$password = "postgres"; 

// เชื่อมต่อกับฐานข้อมูล PostgreSQL
$db = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$db) {
    echo "การเชื่อมต่อฐานข้อมูลล้มเหลว!";
    exit;
}

// คิวรีข้อมูล GeoJSON จากฐานข้อมูล PostgreSQL สำหรับถนนสายสีแดง
$query_red = "SELECT id, name, ST_AsGeoJSON(geom) AS geojson FROM roads_red";

// คิวรีข้อมูล GeoJSON จากฐานข้อมูล PostgreSQL สำหรับถนนสายสีเหลือง
$query_yellow = "SELECT id, name, ST_AsGeoJSON(geom) AS geojson FROM roads_yellow";

$query_blue = "SELECT id, name, ST_AsGeoJSON(geom) AS geojson FROM roads_blue";


$result_red = pg_query($db, $query_red);
$result_yellow = pg_query($db, $query_yellow);
$result_blue = pg_query($db, $query_blue);


if (!$result_red || !$result_yellow || !$result_blue) {
    echo "เกิดข้อผิดพลาดในการดึงข้อมูล: " . pg_last_error($db);
    exit;
}

$geoJsonData_red = [];
while ($row = pg_fetch_assoc($result_red)) {
    $geoJsonData_red[] = [
        'type' => 'Feature',
        'geometry' => json_decode($row['geojson']),
        'properties' => [
            'id' => $row['id'],
            'name' => $row['name'],
        ]
    ];
}

$geoJsonData_yellow = [];
while ($row = pg_fetch_assoc($result_yellow)) {
    $geoJsonData_yellow[] = [
        'type' => 'Feature',
        'geometry' => json_decode($row['geojson']),
        'properties' => [
            'id' => $row['id'],
            'name' => $row['name'],
        ]
    ];
}

$geoJsonData_blue = [];
while ($row = pg_fetch_assoc($result_blue)) {
    $geoJsonData_blue[] = [
        'type' => 'Feature',
        'geometry' => json_decode($row['geojson']),
        'properties' => [
            'id' => $row['id'],
            'name' => $row['name'],
        ]
    ];
}

pg_close($db);

// แปลงข้อมูลเป็น JSON ที่สามารถใช้งานใน JavaScript
$geoJsonData_red = json_encode(['type' => 'FeatureCollection', 'features' => $geoJsonData_red]);
$geoJsonData_yellow = json_encode(['type' => 'FeatureCollection', 'features' => $geoJsonData_yellow]);
$geoJsonData_blue = json_encode(['type' => 'FeatureCollection', 'features' => $geoJsonData_blue]);
?>
