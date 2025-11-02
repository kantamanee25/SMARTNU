<?php
include 'connect.php'; // $con = pg_connect(...)

// ดึงแถวล่าสุดต่อ 1 ชื่อ พร้อมพิกัด
$sql = "
  SELECT COALESCE(json_agg(row_to_json(result)), '[]'::json) AS data
  FROM (
    SELECT DISTINCT ON (i.name)
      i.eui,
      i.name,
      i.location_name,
      i.latitude,
      i.longitude,
      n.humidity,
      n.temperature,
      n.noise,
      n.pm,
      n.pm10,
      n.timestamp
    FROM public.weather_station2 AS n
    JOIN public.node_info1   AS i ON n.eui = i.eui
    WHERE i.name IN ('NU-05','NU-04','NU-03','NU-06','NU-08')
      AND n.pm > 0
    ORDER BY i.name, n.timestamp DESC
  ) AS result;
";

$q = pg_query($con, $sql);
if (!$q) {
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Database query failed', 'detail' => pg_last_error($con)]);
  exit;
}
$row = pg_fetch_assoc($q);
pg_close($con);

header('Content-Type: application/json');
echo $row['data'];
