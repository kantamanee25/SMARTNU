<?php 
include 'connect.php'; // Ensure the database connection is properly included

// Define SQL query to get JSON data from PostgreSQL
$sql = "
    SELECT json_agg(row_to_json(result))
    FROM (
        SELECT DISTINCT ON (i.name) 
            n.eui, n.wind_direct, n.wind_speed, n.temperature, n.humidity, 
            n.pm, n.rain, n.rainacc, n.date_time, n.rssi, n.snr, 
            i.name, i.location_name, i.latitude, i.longitude
        FROM weather_station1 n, node_info i
        WHERE n.pm < 500 and n.eui = i.eui and i.name IN ('NU-01', 'NU-02', 'NU-03', 'NU-04', 'NU-05') and n.pm > 0
        ORDER BY i.name, n.date_time DESC
    ) AS result;
";



// Perform database query
$query = pg_query($con, $sql);

if (!$query) {
    echo json_encode(["error" => "Database query failed"]);
    exit;
}

// Fetch the JSON result
$row = pg_fetch_assoc($query);
$jsonResult = $row['json_agg']; // PostgreSQL returns a JSON array

// Close the database connection
pg_close($con);

// Return JSON response
header('Content-Type: application/json');
echo $jsonResult;
?>
