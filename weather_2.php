<?php
include 'connect.php'; // $con = pg_connect(...)

// รวมข้อมูลจาก weather_station1 และ weather_station2
$sql = "
  SELECT COALESCE(json_agg(row_to_json(result)), '[]'::json) AS data
  FROM (
    -- ข้อมูลจาก weather_station1 (ล่าสุด)
    SELECT * FROM (
      SELECT DISTINCT ON (i.name)
        i.eui,
        i.name,
        i.location_name,
        i.latitude::double precision,
        i.longitude::double precision,
        n.humidity::double precision,
        n.temperature::double precision,
        n.wind_speed::double precision,
        n.wind_direct::double precision,
        n.pm::double precision,
        NULL::double precision as pm10,
        n.rain::double precision,
        n.rainacc::double precision,
        n.rssi::double precision,
        n.snr::double precision,
        n.date_time::timestamp as timestamp,
        'weather_station1' as source_table
      FROM public.weather_station1 AS n
      JOIN public.node_info AS i ON n.eui = i.eui
      WHERE i.name IN ('NU-01', 'NU-02', 'NU-03', 'NU-04', 'NU-05')
        AND n.date_time::timestamp >= CURRENT_DATE - INTERVAL '30 days'
      ORDER BY i.name, n.date_time::timestamp DESC
    ) w1
    
    UNION ALL
    
    -- ข้อมูลจาก weather_station2 (ล่าสุด)
    SELECT * FROM (
      SELECT DISTINCT ON (i.name)
        i.eui,
        i.name,
        i.location_name,
        i.latitude::double precision,
        i.longitude::double precision,
        n.humidity::double precision,
        n.temperature::double precision,
        NULL::double precision as wind_speed,
        NULL::double precision as wind_direct,
        n.pm::double precision,
        n.pm10::double precision,
        NULL::double precision as rain,
        NULL::double precision as rainacc,
        NULL::double precision as rssi,
        NULL::double precision as snr,
        n.timestamp,
        'weather_station2' as source_table
      FROM public.weather_station2 AS n
      JOIN public.node_info1 AS i ON n.eui = i.eui
      WHERE i.name IN ('NU-05','NU-04','NU-03','NU-06','NU-08')
        AND n.timestamp >= CURRENT_DATE - INTERVAL '30 days'
      ORDER BY i.name, n.timestamp DESC
    ) w2
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
