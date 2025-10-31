<?php
//  - โค้ดสำหรับคำนวณเส้นทางและส่งผลลัพธ์เป็น JSON

// เพิ่มความเสถียร: เริ่มต้นด้วยการบัฟเฟอร์เอาต์พุต และปิดการแสดงข้อผิดพลาด PHP
ob_start(); 
error_reporting(0); 

// **********************************************
// *** 1. การตั้งค่าและเชื่อมต่อฐานข้อมูล ***
// **********************************************
// *** !!! โปรดปรับเปลี่ยนค่าเหล่านี้ให้ตรงกับฐานข้อมูลของคุณ !!! ***
define("PG_DB" , "test_pgrouting"); // <-- ตรวจสอบชื่อ DB
define("PG_HOST", "localhost");
define("PG_USER" , "postgres");
define("PG_PORT" , "5432");
define("PG_PASS" , "postgres"); // <-- แก้ไขรหัสผ่านของคุณ

// กำหนดค่าคงที่สำหรับ Cost Calculation
define('BIKE_SPEED_KPH', 15);
define('WALK_SPEED_KPH', 5);
define('DEFAULT_VEHICLE_SPEED_KPH', 30); 

// เชื่อมต่อฐานข้อมูล
$conn = @pg_connect("dbname=" . PG_DB . " host=" . PG_HOST . " password=" . PG_PASS . " user=" . PG_USER . " port=" . PG_PORT);

// หากเชื่อมต่อล้มเหลว
if (!$conn) {
    ob_end_clean(); 
    header('Content-type: application/json', true, 500);
    echo json_encode(['error' => 'Database connection failed. Check credentials in pgrouting.php']); 
    exit;
}

pg_set_client_encoding($conn, "UTF-8"); 

// -----------------------------------------------------------------
// *** 1.1 สถานที่สำคัญและ Node ID ที่กำหนดไว้ล่วงหน้า (ใช้สำหรับการเลือกจาก Dropdown คณะ/สำนักงาน) ***
// -----------------------------------------------------------------
$place_to_node_map = [
    // คณะ
    'คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม' => 204,
    'คณะสถาปัตยกรรมศาสตร์' => 274,
    'คณะวิศวกรรมศาสตร์' => 281,
    'คณะสังคมศาสตร์' => 409,
    'คณะนิติศาสตร์' => 408,
    'คณะบริหารธุรกิจ เศรษฐศาสตร์และการสื่อสาร' => 415,
    'คณะมนุษยศาสตร์' => 156,
    'คณะศึกษาศาสตร์' => 684,
    'คณะเภสัชศาสตร์' => 19,
    'คณะทันตแพทยศาสตร์' => 65,
    'คณะสหเวชศาสตร์' => 65, 
    'คณะพยาบาลศาสตร์' => 60,
    'คณะสาธารณสุขศาสตร์' => 709, 
    'คณะวิทยาศาสตร์การแพทย์' => 10,
    'คณะวิทยาศาสตร์' => 73,
    'คณะโลจิสติกส์และดิจิทัลซัพพลายเชน' => 346,
    'วิทยาลัยนานาชาติ' => 492,
    // สำนักงาน 
    'สำนักงานอธิการบดี' => 67, 
    'อาคารมิ่งขวัญ' => 55, 
    'สำนักหอสมุด' => 668, 
    'ตึก CITCOMS' => 158, 
    'อาคารขวัญเมือง' => 364, 
    
];

// *****************************************************************
// *** 1.2 สถานที่สำคัญในรูปแบบพิกัด (ป้ายรถเมล์) ***
// *****************************************************************
$place_to_coords_map = [
    // สายสีเหลือง
    'ป้ายหอพักอาจารย์และบุคลากร มน.นิเวศน์' => '16.742502,100.198668',
    'ป้ายอาคารปฎิบัติการคณะวิศวกรรม' => '16.742419,100.197306',    
    'ป้ายคณะวิทยาศาสตร์(สาขาเคมี)' => '16.742038,100.195685',  
    'ป้ายอาคารเอกาทศรถ' => '16.743145,100.191929',
    'ป้ายQS ' => '16.745240,100.192673',
    'ป้ายหน้าคณะสาธารณสุขศาสตร์' => '16.745642, 100.190373',
    'ป้ายอาคารคณะทันตแพทยศาสตร์' => '16.747600,100.189662',
    'ป้ายพิพิธภัณฑ์ชีวิต ประตู6' => '16.750687,100.189820',
    'ป้ายสถานีวิทยุ ลานสมเด็จ' => '16.750111,100.191066',
    'ป้ายอาคารอเนกประสงค์(โดม)' => '16.749882,100.193750',
    'ป้ายอาคารปราบไตรจักร2' => '16.748051,100.193943',
    'ป้ายคณะนิติศาสตร์' => '16.748212,100.195900',
    'ป้ายสระว่ายนํ้าสุพรรณกัลยา' => '16.746252,100.196975',
    // สายสีแดง
    'ป้ายหอพักอาจารย์และบุคลากร มน.นิเวศน์' => '16.742268,100.198737',
    'ป้ายหน้าคณะวิทยาศาสตร์' => '16.742576,100.195370',
    'ป้ายคณะวิทยาศาสตร์(สาขาคณิตศาสตร์)' => '16.742538,100.192964',
    'ป้ายสระเอกกษัตริย์' => '16.744216,100.191250',
    'ป้ายQS' => '16.745154,100.192737',
    'ป้ายคณะเภสัชศาสตร์' => '16.746511,100.189811', 
    'ป้ายอาคารมิ่งขวัญ' => '16.749563,100.192287', 
    'ป้ายอาคารปราบไตรจักร1' => '16.748170,100.193830',
    'ป้ายCITCOMS' => '16.748281,100.195700',
    'ป้ายคณะเกษตรศาสตร์' => '16.746647,100.196614', 
    'ป้ายหน้าคณะวิศวกรรม' => '16.743997,100.197690',
];
// -----------------------------------------------------------------


// 2. ตั้งค่า Header และตรวจสอบพารามิเตอร์ที่จำเป็น
header('Content-Type: application/json; charset=utf-8');

// รับพารามิเตอร์
$route_type = pg_escape_string($conn, $_GET['route_type'] ?? '');
$start_point_param = $_GET['start_point'] ?? null; 
$end_point_param = $_GET['end_point'] ?? null;     
$start_place_name = $_GET['start_place'] ?? null;  
$end_place_name = $_GET['end_place'] ?? null;      

// ตรวจสอบความสมบูรณ์
if (empty($route_type) || (empty($start_point_param) && empty($start_place_name)) || (empty($end_point_param) && empty($end_place_name)) ) {
    ob_end_clean();
    echo json_encode(['error' => 'Missing required parameters (route_type, and start/end point or place).']); 
    exit;
}

// ****************************************************************
// *** 3. กำหนด Routing Profile, Filter และ Cost Calculation ***
// ****************************************************************
$filter_column = '';
$cost_kph = 0;

switch ($route_type) {
    case 'car':
        $filter_column = 'is_car_allowed';
        $cost_kph = DEFAULT_VEHICLE_SPEED_KPH; 
        break;
        
    case 'motorcycle':
        $filter_column = 'is_motorcycle_allowed';
        $cost_kph = DEFAULT_VEHICLE_SPEED_KPH; 
        break;
        
    case 'bike':
        $filter_column = 'is_bicycle_allowed';
        $cost_kph = BIKE_SPEED_KPH; 
        break;
        
    case 'walk':
        $filter_column = 'is_walkable'; 
        $cost_kph = WALK_SPEED_KPH; 
        break;
        
    default:
        ob_end_clean();
        echo json_encode(['error' => 'Invalid route type selected.']);
        exit;
}


// ****************************************************************
// *** 4. กำหนด Node ID และดึงพิกัดจริงของ Node นั้น ***
// ****************************************************************
$start_node = null;
$end_node = null;
$start_coords_for_marker = null; 
$end_coords_for_marker = null; 

// Function สำหรับดึงพิกัด Lat/Lng จาก Node ID
function get_node_coords($conn, $node_id) {
    if (!$node_id) return null;
    $sql = "SELECT ST_Y(the_geom) as lat, ST_X(the_geom) as lng FROM ways_vertices_pgr WHERE id = $node_id";
    $res = pg_query($conn, $sql);
    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return ['lat' => (float)$row['lat'], 'lng' => (float)$row['lng']]; 
    }
    return null;
}

// A. ตรวจสอบชื่อสถานที่ (Place Name): ถ้ามีการเลือกจาก dropdown 
if ($start_place_name) {
    if (isset($place_to_node_map[$start_place_name])) {
        // A1. สถานที่ที่เป็น Node ID (คณะ/สำนักงาน)
        $start_node = $place_to_node_map[$start_place_name];
        $start_coords_for_marker = get_node_coords($conn, $start_node); 
    } elseif (isset($place_to_coords_map[$start_place_name])) {
        // A2. ป้ายรถเมล์ (สถานที่ที่เป็นพิกัด) -> ตั้งค่า $start_point_param ให้โค้ดส่วน B ค้นหา Node ที่ใกล้ที่สุด
        $start_point_param = $place_to_coords_map[$start_place_name];
    }
}

if ($end_place_name) {
    if (isset($place_to_node_map[$end_place_name])) {
        // A1. สถานที่ที่เป็น Node ID (คณะ/สำนักงาน)
        $end_node = $place_to_node_map[$end_place_name];
        $end_coords_for_marker = get_node_coords($conn, $end_node); 
    } elseif (isset($place_to_coords_map[$end_place_name])) {
        // A2. ป้ายรถเมล์ (สถานที่ที่เป็นพิกัด) -> ตั้งค่า $end_point_param ให้โค้ดส่วน B ค้นหา Node ที่ใกล้ที่สุด
        $end_point_param = $place_to_coords_map[$end_place_name];
    }
}


// B. ถ้ายังไม่ได้ Node ID ให้ค้นหาจากพิกัด (Coords): (ใช้สำหรับ Map Click และป้ายรถเมล์)
if ($start_node === null && $start_point_param) {
    $start_coords = explode(',', $start_point_param);
    // แปลงพิกัด Lat/Lon เป็น Node ID ที่ใกล้ที่สุด โดยจำกัดเฉพาะ Node ที่เชื่อมกับทางที่อนุญาต
    $sql_start_node = "
        SELECT v.id::int, ST_Y(v.the_geom) as lat, ST_X(v.the_geom) as lng
        FROM ways_vertices_pgr v
        WHERE v.id IN (
            SELECT source FROM public.ways WHERE {$filter_column} = TRUE
            UNION
            SELECT target FROM public.ways WHERE {$filter_column} = TRUE
        )
        ORDER BY v.the_geom <-> ST_SetSRID(ST_MakePoint({$start_coords[1]}, {$start_coords[0]}), 4326) 
        LIMIT 1";
    $res_start = pg_query($conn, $sql_start_node);
    if ($res_start && pg_num_rows($res_start) > 0) {
        $row = pg_fetch_assoc($res_start);
        $start_node = (int)$row['id'];
        $start_coords_for_marker = ['lat' => (float)$row['lat'], 'lng' => (float)$row['lng']]; // ใช้พิกัดจริงของ Node ที่พบ
    } else {
        // Debug: ตรวจสอบว่าทำไมไม่พบ start node
        $debug_start = [
            'start_coords' => $start_coords,
            'filter_column' => $filter_column,
            'sql_error' => pg_last_error($conn),
            'sql_query' => $sql_start_node
        ];
    }
}

if ($end_node === null && $end_point_param) {
    $end_coords = explode(',', $end_point_param);
    // แปลงพิกัด Lat/Lon เป็น Node ID ที่ใกล้ที่สุด โดยจำกัดเฉพาะ Node ที่เชื่อมกับทางที่อนุญาต
    $sql_end_node = "
        SELECT v.id::int, ST_Y(v.the_geom) as lat, ST_X(v.the_geom) as lng
        FROM ways_vertices_pgr v
        WHERE v.id IN (
            SELECT source FROM public.ways WHERE {$filter_column} = TRUE
            UNION
            SELECT target FROM public.ways WHERE {$filter_column} = TRUE
        )
        ORDER BY v.the_geom <-> ST_SetSRID(ST_MakePoint({$end_coords[1]}, {$end_coords[0]}), 4326) 
        LIMIT 1";
        
    $res_end = pg_query($conn, $sql_end_node);
    if ($res_end && pg_num_rows($res_end) > 0) {
        $row = pg_fetch_assoc($res_end);
        $end_node = (int)$row['id'];
        $end_coords_for_marker = ['lat' => (float)$row['lat'], 'lng' => (float)$row['lng']]; // ใช้พิกัดจริงของ Node ที่พบ
    } else {
        // Debug: ตรวจสอบว่าทำไมไม่พบ end node
        $debug_end = [
            'end_coords' => $end_coords,
            'filter_column' => $filter_column,
            'sql_error' => pg_last_error($conn),
            'sql_query' => $sql_end_node
        ];
    }
}


if (!$start_node || !$end_node) {
    ob_end_clean();
    $debug_info = [
        'start_place_name' => $start_place_name ?? 'null',
        'end_place_name' => $end_place_name ?? 'null', 
        'start_node' => $start_node,
        'end_node' => $end_node,
        'route_type' => $route_type,
        'filter_column' => $filter_column,
        'debug_start' => $debug_start ?? null,
        'debug_end' => $debug_end ?? null
    ];
    echo json_encode(['geojson' => null, 'message' => 'Could not find a valid starting or ending node (from map click or selected place). The nearest node might not be on the network allowed for the selected vehicle type).', 'debug' => $debug_info]);
    exit;
}


// 5. กำหนด Cost Calculation 
if ($route_type === 'car' || $route_type === 'motorcycle') {
    $forward_speed_kph = "COALESCE(NULLIF(maxspeed_forward, 0), " . $cost_kph . ")";
    $backward_speed_kph = "COALESCE(NULLIF(maxspeed_backward, 0), " . $cost_kph . ")";
} else {
    $forward_speed_kph = "COALESCE(NULLIF({$cost_kph}, 0), 1)"; 
    $backward_speed_kph = "COALESCE(NULLIF({$cost_kph}, 0), 1)"; 
}

// สูตร (length_m / (Speed_KPH * 1000/3600)) = time_in_seconds
$cost_calc = "length_m / ({$forward_speed_kph} * 1000.0 / 3600.0)";
$reverse_cost_calc = "length_m / ({$backward_speed_kph} * 1000.0 / 3600.0)";


// *******************************************************************
// *** 6. สร้าง pgr_dijkstra Query ***
// *******************************************************************
$sub_query_raw = "SELECT gid AS id, source, target, {$cost_calc} AS cost, {$reverse_cost_calc} AS reverse_cost FROM public.ways WHERE {$filter_column} = TRUE";
$sub_query = pg_escape_string($conn, $sub_query_raw);

$sql_route = "
    SELECT
        ST_AsGeoJSON(ST_Collect(w.the_geom)) AS route_geojson,
        SUM(route.cost) AS total_cost
    FROM public.ways AS w
    JOIN
        pgr_dijkstra(
            '{$sub_query}',  
            {$start_node},
            {$end_node},
            FALSE
        ) AS route
    ON
        w.gid = route.edge;
";

$result = pg_query($conn, $sql_route);

// **********************************************
// *** 7. ส่วน DEBUG และ 8. Output ***
// **********************************************

if (!$result) {
    $error_message = pg_last_error($conn);
    $full_query = $sql_route; 
    
    ob_end_clean(); 
    header('Content-type: application/json', true, 500);
    
    $debug_output = [
        'error' => 'pgRouting query failed: Detailed Error Follows',
        'sql_error_detail' => $error_message, 
        'full_query_sent' => $full_query,      
        'start_node_used' => $start_node,
        'end_node_used' => $end_node,
        'filter_column' => $filter_column
    ];
    echo json_encode($debug_output);
    exit;
}

$row = pg_fetch_assoc($result);

if ($row && $row['route_geojson']) {
    if ($row['total_cost'] === null) {
        $response = ['geojson' => null, 'message' => "No route found for {$route_type}. The start and end points might be disconnected in the selected network."];
    } else {
        $response = [
            'geojson' => $row['route_geojson'],
            'total_time_sec' => number_format((float)$row['total_cost'], 2, '.', ''),
            'start_coords' => $start_coords_for_marker, 
            'end_coords' => $end_coords_for_marker      
        ];
    }
} else {
    $response = ['geojson' => null, 'message' => "No route found for {$route_type}."];
}

ob_end_clean(); 
echo json_encode($response);

pg_close($conn);
?>