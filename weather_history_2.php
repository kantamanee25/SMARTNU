<?php
header('Content-Type: application/json; charset=utf-8');

// ดึงการเชื่อมต่อฐานข้อมูลจากไฟล์ connect.php
// นี่คือแนวทางปฏิบัติที่ดีที่สุด เพื่อความปลอดภัยและง่ายต่อการจัดการ
include 'connect.php'; 

// ตรวจสอบว่าการเชื่อมต่อฐานข้อมูลสำเร็จหรือไม่
if (!$con) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']);
    exit;
}

// รับค่าพารามิเตอร์ที่จำเป็นจาก URL
$eui = $_GET['eui'] ?? null;
$start_date = $_GET['start'] ?? null;
$end_date = $_GET['end'] ?? null;

// ตรวจสอบความถูกต้องของพารามิเตอร์
if (!$eui || !$start_date || !$end_date) {
    http_response_code(400); // ส่งสถานะ Bad Request
    echo json_encode(['error' => 'พารามิเตอร์ที่จำเป็นไม่ครบถ้วน (eui, start, หรือ end)']);
    exit;
}

try {
    // สร้างคำสั่ง SQL โดยใช้ Prepared Statement
    // การใช้ JOIN กับ node_info จะทำให้โค้ดมีความยืดหยุ่นมากขึ้น
    // ใช้ตัวแปร $1, $2, $3 สำหรับการแทนค่า ซึ่งปลอดภัยจาก SQL Injection
    $sql = "
        SELECT 
            n.eui, 
            n.timestamp, 
            n.pm, 
            n.pm10, 
            n.noise 
        FROM weather_station2 n
        JOIN node_info i ON n.eui = i.eui
        WHERE n.eui = $1
          AND n.timestamp BETWEEN $2 AND $3
        ORDER BY n.timestamp ASC
    ";
    
    // ใช้ pg_query_params() สำหรับการประมวลผลคำสั่ง SQL ที่มีพารามิเตอร์
    $query = pg_query_params(
        $con, 
        $sql, 
        array(
            $eui, 
            $start_date . ' 00:00:00', 
            $end_date . ' 23:59:59'
        )
    );

    // ตรวจสอบว่าคำสั่ง SQL สำเร็จหรือไม่
    if (!$query) {
        http_response_code(500);
        echo json_encode(['error' => 'การเรียกดูข้อมูลจากฐานข้อมูลล้มเหลว: ' . pg_last_error($con)]);
        exit;
    }

    // ดึงข้อมูลทั้งหมดในรูปแบบ array
    $data = pg_fetch_all($query);

    // หากไม่พบข้อมูล ให้ส่ง array เปล่า [] กลับไป แทนที่จะเป็น null
    if ($data === false) {
        $data = [];
    }
    
    // ปิดการเชื่อมต่อฐานข้อมูล
    pg_close($con);

    // ส่งข้อมูลในรูปแบบ JSON
    echo json_encode($data);

} catch (Exception $e) {
    // หากเกิดข้อผิดพลาดที่ไม่คาดคิด
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดที่ไม่คาดคิด: ' . $e->getMessage()]);
}
?>