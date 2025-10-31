<?php
// เพิ่ม CORS header
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// รวมข้อมูล GeoJSON จาก roads.php
include('./roads.php');
include('./connect.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>SMART NU - ระบบรถไฟฟ้าแบบเรียลไทม์</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="styles3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/@turf/turf@6.5.0/turf.min.js"></script>
    
<style>
    /* ปุ่มเลือกประเภทรายการ */
    .type-button.active { background-color: #d1e7dd; border-color: #4CAF50; font-weight: 700; }
    .type-button:hover { background-color: #e9e9e9; }
    /* ซ่อน dropdown เริ่มต้น */
    #start_faculty, #start_office, #start_busstop_yellow, #start_busstop_red,
    #end_faculty, #end_office, #end_busstop_yellow, #end_busstop_red { }
    
    /* ==================== IDW Legend Styles ==================== */
    #idw-legend {
        background: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        font-family: 'Sarabun', sans-serif;
        max-width: 250px;
        width: auto;
        border: 1px solid #ddd;
    }
    
    #idw-legend .legend-title {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 8px;
        text-align: center;
        color: #333;
    }
    
    #idw-legend .legend-scale {
        display: flex;
        height: 20px;
        margin-bottom: 5px;
        border-radius: 3px;
        overflow: hidden;
    }
    
    #idw-legend .legend-scale > div {
        flex: 1;
    }
    
    #idw-legend .legend-labels {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-bottom: 5px;
        color: #555;
    }
    
    #idw-legend .legend-info {
        font-size: 10px;
        text-align: center;
        color: #888;
        font-style: italic;
        margin-top: 5px;
    }
    
    /* Responsive สำหรับมือถือ */
    @media (max-width: 768px) {
        #idw-legend {
            font-size: 12px;
            padding: 10px 15px;
            max-width: 200px;
        }
        
        #idw-legend .legend-title {
            font-size: 12px;
            margin-bottom: 6px;
        }
        
        #idw-legend .legend-scale {
            height: 18px;
        }
        
        #idw-legend .legend-labels {
            font-size: 10px;
        }
        
        #idw-legend .legend-info {
            font-size: 9px;
        }
    }
    
    /* ==================== Weather Legend Styles ==================== */
    #weather-legend {
        background: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        font-family: 'Sarabun', sans-serif;
        max-width: 250px;
        width: auto;
        border: 1px solid #ddd;
    }
    
    /* ==================== PM Legend Styles ==================== */
    #pm-legend {
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        font-family: 'Sarabun', sans-serif;
        max-width: 200px;
        width: auto;
        border: 1px solid #ddd;
        font-size: 11px;
    }
    
    #weather-legend .row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        font-size: 12px;
    }
    
    #weather-legend .swatch {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 8px;
        display: inline-block;
    }
    
    #pm-legend .row {
        display: flex;
        align-items: center;
        margin-bottom: 3px;
        font-size: 10px;
    }
    
    #pm-legend .swatch {
        width: 8px;
        height: 8px;
        border-radius: 2px;
        margin-right: 6px;
        display: inline-block;
    }
    
    /* Responsive สำหรับมือถือ */
    @media (max-width: 768px) {
        #weather-legend {
            font-size: 11px;
            padding: 10px 15px;
            max-width: 200px;
            margin-bottom: 80px !important;
        }
        
        #weather-legend .row {
            font-size: 10px;
        }
        
        #weather-legend .swatch {
            width: 10px;
            height: 10px;
            margin-right: 6px;
        }
        
        #pm-legend {
            font-size: 9px;
            padding: 6px 10px;
            max-width: 160px;
            margin-bottom: 60px !important;
        }
        
        #pm-legend .row {
            font-size: 8px;
            margin-bottom: 2px;
        }
        
        #pm-legend .swatch {
            width: 6px;
            height: 6px;
            margin-right: 4px;
        }
    }
    
    /* ==================== Route System Styles ==================== */
    .type-button {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 1px solid #ddd;
        background-color: #f0f0f0;
        border-radius: 4px;
        transition: background-color 0.2s, border-color 0.2s;
    }
    
    .type-button.active {
        background-color: #d1e7dd;
        border-color: #4CAF50;
        font-weight: bold;
    }
    
    .type-button:hover {
        background-color: #e9e9e9;
    }
    
    .type-button span.icon {
        margin-right: 8px;
        font-size: 1.2em;
    }
    
    .place-select-group {
        display: none;
        margin-bottom: 15px;
        position: relative;
        z-index: 10;
    }
    
    .place-select-group select {
        width: 100%;
        max-height: none;
        padding: 8px;
    }
    
    .control-group {
        margin-top: 15px;
        padding: 10px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 4px;
        position: relative;
        overflow: visible;
    }
    
    .control-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .control-group select, .control-group button {
        padding: 8px;
        border-radius: 4px;
        width: 100%;
        margin-top: 5px;
        box-sizing: border-box;
    }
    
    .status-info {
        margin-top: 20px;
        padding: 10px;
        background: #e9e9ff;
        border-radius: 4px;
        font-size: 0.9em;
    }
    
    .status-info strong {
        color: #5c00a3;
    }
</style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="university-logo">NU</div>
            <div class="header-title">
                <h1>SMART NU: การพัฒนาระบบภูมิสารสนเทศเพื่อการเดินทางและการรับรู้สภาพอากาศภายในมหาวิทยาลัยนเรศวร</h1>
                <div class="header-subtitle">SMART NU: Development A Geospatial Information System for Transportation and Weather Awareness within Naresuan University</div>
            </div>
        </div>
        <div class="header-right">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <div class="status-badge">
                <div class="status-dot"></div>
                ระบบออนไลน์ - รถไฟฟ้าเรียลไทม์
            </div>
            <div id="realtime-status" style="font-size: 12px; color: #666; margin-top: 5px;">
                กำลังโหลดข้อมูล...
            </div>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Loading Animation -->
    <div class="loading" id="loadingSpinner">
        <div class="spinner"></div>
        <div>กำลังโหลดข้อมูลรถไฟฟ้า...</div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="./NULOGO.png" alt="GIST NU Logo">
            </div>
            <div class="sidebar-title">SMART NU</div>
            
            <!-- ปุ่มปิดเมนูสำหรับมือถือ -->
            <button class="close-mobile-menu-btn" id="closeMobileMenuBtn" style="display: none;">
                <i class="fas fa-times"></i>
                ปิดเมนู
            </button>
        </div>
        
        <div class="sidebar-content">
            <!-- Route Finding Category -->
            <div class="category-section">
                <div class="category-header" data-target="route-finding">
                    <div class="category-title">
                        <span class="category-number">1</span>
                        <i class="fas fa-route" style="margin-right: 8px;"></i>
                        ระบบนำทางภายใน ม.นเรศวร
                    </div>
                    <i class="fas fa-chevron-down category-icon"></i>
                </div>
                <div class="category-content" id="route-finding">
                    <!-- จุดเริ่มต้น -->
                    <div class="control-group" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; background: #fff; border-radius: 4px;">
                        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #1e3c72;"><span class="icon">📍</span> จุดเริ่มต้น (Start)</h3>
                        <div id="start_type_selector">
                            <div class="type-button" data-type="faculty" data-for="start" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">🎓</span> เลือกคณะ
                            </div>
                            <div class="type-button" data-type="office" data-for="start" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">💼</span> เลือกสำนักงาน/อาคาร
                            </div>
                            <div class="type-button" data-type="busstop_yellow" data-for="start" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">🟡</span> เลือกป้ายรถเมล์สีเหลือง
                            </div>
                            <div class="type-button" data-type="busstop_red" data-for="start" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">🔴</span> เลือกป้ายรถเมล์สีแดง
                            </div>
                        </div>

                        <div class="place-select-group" id="start_faculty_group" style="display: none; margin-bottom: 15px;">
                            <label for="start_faculty_select" style="font-size: 12px; font-weight: 600; color: #1e3c72; display: block; margin-bottom: 5px;">เลือกคณะเริ่มต้น:</label>
                            <select id="start_faculty_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกคณะเริ่มต้น --</option>
                            </select>
                        </div>
                        <div class="place-select-group" id="start_office_group" style="display: none; margin-bottom: 15px;">
                            <label for="start_office_select" style="font-size: 12px; font-weight: 600; color: #1e3c72; display: block; margin-bottom: 5px;">เลือกสำนักงานเริ่มต้น:</label>
                            <select id="start_office_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกสำนักงานเริ่มต้น --</option>
                            </select>
                        </div>
                        <div class="place-select-group" id="start_busstop_yellow_group" style="display: none; margin-bottom: 15px;">
                            <label for="start_busstop_yellow_select" style="font-size: 12px; font-weight: 600; color: #1e3c72; display: block; margin-bottom: 5px;">เลือกป้ายรถเมล์สีเหลืองเริ่มต้น:</label>
                            <select id="start_busstop_yellow_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกป้ายรถเมล์สีเหลืองเริ่มต้น --</option>
                            </select>
                        </div>
                        <div class="place-select-group" id="start_busstop_red_group" style="display: none; margin-bottom: 15px;">
                            <label for="start_busstop_red_select" style="font-size: 12px; font-weight: 600; color: #1e3c72; display: block; margin-bottom: 5px;">เลือกป้ายรถเมล์สีแดงเริ่มต้น:</label>
                            <select id="start_busstop_red_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกป้ายรถเมล์สีแดงเริ่มต้น --</option>
                            </select>
                        </div>
                    </div>

                    <!-- จุดปลายทาง -->
                    <div class="control-group" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; background: #fff; border-radius: 4px;">
                        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #e74c3c;"><span class="icon">🚩</span> จุดปลายทาง (End)</h3>
                        <div id="end_type_selector">
                            <div class="type-button" data-type="faculty" data-for="end" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">🎓</span> เลือกคณะ
                            </div>
                            <div class="type-button" data-type="office" data-for="end" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">💼</span> เลือกสำนักงาน/อาคาร
                            </div>
                            <div class="type-button" data-type="busstop_yellow" data-for="end" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">🟡</span> เลือกป้ายรถเมล์สีเหลือง
                            </div>
                            <div class="type-button" data-type="busstop_red" data-for="end" style="display: flex; align-items: center; padding: 8px 10px; margin-bottom: 5px; cursor: pointer; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px;">
                                <span class="icon" style="margin-right: 8px;">🔴</span> เลือกป้ายรถเมล์สีแดง
                            </div>
                        </div>

                        <div class="place-select-group" id="end_faculty_group" style="display: none; margin-bottom: 15px;">
                            <label for="end_faculty_select" style="font-size: 12px; font-weight: 600; color: #e74c3c; display: block; margin-bottom: 5px;">เลือกคณะปลายทาง:</label>
                            <select id="end_faculty_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกคณะปลายทาง --</option>
                            </select>
                        </div>
                        <div class="place-select-group" id="end_office_group" style="display: none; margin-bottom: 15px;">
                            <label for="end_office_select" style="font-size: 12px; font-weight: 600; color: #e74c3c; display: block; margin-bottom: 5px;">เลือกสำนักงานปลายทาง:</label>
                            <select id="end_office_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกสำนักงานปลายทาง --</option>
                            </select>
                        </div>
                        <div class="place-select-group" id="end_busstop_yellow_group" style="display: none; margin-bottom: 15px;">
                            <label for="end_busstop_yellow_select" style="font-size: 12px; font-weight: 600; color: #e74c3c; display: block; margin-bottom: 5px;">เลือกป้ายรถเมล์สีเหลืองปลายทาง:</label>
                            <select id="end_busstop_yellow_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกป้ายรถเมล์สีเหลืองปลายทาง --</option>
                            </select>
                        </div>
                        <div class="place-select-group" id="end_busstop_red_group" style="display: none; margin-bottom: 15px;">
                            <label for="end_busstop_red_select" style="font-size: 12px; font-weight: 600; color: #e74c3c; display: block; margin-bottom: 5px;">เลือกป้ายรถเมล์สีแดงปลายทาง:</label>
                            <select id="end_busstop_red_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif;">
                                <option value="">-- เลือกป้ายรถเมล์สีแดงปลายทาง --</option>
                            </select>
                        </div>
                    </div>

                    <!-- ประเภทพาหนะและปุ่มคำนวณ -->
                    <div class="control-group" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; background: #fff; border-radius: 4px;">
                        <label for="route_select" style="font-size: 12px; font-weight: 600; color: #28A745; display: block; margin-bottom: 5px;">
                            <i class="fas fa-car"></i> ประเภทพาหนะ:
                        </label>
                        <select id="route_select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif; margin-bottom: 10px;">
                            <option value="walk" selected>คนเดิน 🚶</option>
                            <option value="car">รถยนต์ 🚗</option>
                            <option value="motorcycle">มอเตอร์ไซค์ 🏍️</option>
                            <option value="bike">จักรยาน 🚲</option>
                        </select>
                        
                        <button id="calculate_route_btn" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-family: 'Sarabun', sans-serif; font-size: 14px; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s;">
                            <i class="fas fa-route"></i> คำนวณเส้นทาง
                        </button>
                    </div>

                    <!-- แสดงผลลัพธ์ -->
                    <div class="status-info" style="margin-top: 15px; padding: 12px; background: #e9e9ff; border-radius: 6px; font-size: 12px;">
                        <p id="start_info" style="margin: 0 0 8px 0;">จุดเริ่มต้น: <strong>ยังไม่ได้กำหนด</strong></p>
                        <p id="end_info" style="margin: 0 0 8px 0;">จุดปลายทาง: <strong>ยังไม่ได้กำหนด</strong></p>
                        <p style="margin: 0 0 10px 0;">
                            เวลาเดินทาง: 
                            <strong id="travel_time">--</strong>
                        </p>
                        <button id="clear_map_btn" style="width: 100%; padding: 8px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-family: 'Sarabun', sans-serif; font-size: 12px;">
                            <i class="fas fa-times"></i> ล้างทั้งหมด
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Weather & Environment Category -->
            <div class="category-section">
                <div class="category-header" data-target="weather-layers">
                    <div class="category-title">
                        <span class="category-number">2</span>
                        <i class="fas fa-cloud-sun" style="margin-right: 8px;"></i>
                        สถานีตรวจสอบสภาพอากาศ
                    </div>
                    <i class="fas fa-chevron-down category-icon"></i>
                </div>
                <div class="category-content" id="weather-layers">
                    <!-- Station Search -->
                    <div style="margin-bottom: 15px;">
                        <input type="text" id="station-search" placeholder="ค้นหาสถานี...">
                        <div style="font-size: 12px; font-weight: 500; margin-bottom: 8px;">สถานีตรวจวัดสภาพอากาศ</div>
                        <ul class="station-list" id="station-list"></ul>
                    </div>
                    <div class="layer-item">
                        <div class="layer-icon">
                            <img src="./weather.png" alt="Weather Station">
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">ตำแหน่งสถานีตรวจสอบสภาพอากาศ</div>
                            <div class="layer-title-en">Weather Monitoring Stations</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-weather" >
                            <span class="slider"></span>
                        </label>
                    </div>

<!-- ค่าฝุ่น PM -->
<div class="layer-item">
    <div class="layer-icon">
        <i class="fas fa-smog" style="color: #e67e22;"></i>
    </div>
    <div class="layer-info">
        <div class="layer-title-th">ดูค่าฝุ่นทุกสถานี</div>
        <div class="layer-title-en">View PM Values</div>
    </div>
    <label class="switch">
        <input type="checkbox" id="toggle-pm-view">
        <span class="slider"></span>
    </label>
</div>

<!-- อุณหภูมิ -->
<div class="layer-item">
    <div class="layer-icon">
        <i class="fas fa-thermometer-half" style="color: #e74c3c;"></i>
    </div>
    <div class="layer-info">
        <div class="layer-title-th">ดูอุณหภูมิทุกสถานี</div>
        <div class="layer-title-en">View Temperature</div>
    </div>
    <label class="switch">
        <input type="checkbox" id="toggle-temp-view">
        <span class="slider"></span>
    </label>
</div>

<!-- ความชื้น -->
<div class="layer-item">
    <div class="layer-icon">
        <i class="fas fa-tint" style="color: #3498db;"></i>
    </div>
    <div class="layer-info">
        <div class="layer-title-th">ดูความชื้นทุกสถานี</div>
        <div class="layer-title-en">View Humidity</div>
    </div>
    <label class="switch">
        <input type="checkbox" id="toggle-humidity-view">
        <span class="slider"></span>
    </label>
</div>

<!-- ความเร็วลม -->
<div class="layer-item">
    <div class="layer-icon">
        <i class="fas fa-wind" style="color: #16a085;"></i>
    </div>
    <div class="layer-info">
        <div class="layer-title-th">ดูความเร็วลมทุกสถานี</div>
        <div class="layer-title-en">View Wind Speed</div>
    </div>
    <label class="switch">
        <input type="checkbox" id="toggle-wind-view">
        <span class="slider"></span>
    </label>
</div>

<!-- ปริมาณฝน -->
<div class="layer-item">
    <div class="layer-icon">
        <i class="fas fa-cloud-rain" style="color: #1abc9c;"></i>
    </div>
    <div class="layer-info">
        <div class="layer-title-th">ดูปริมาณฝนทุกสถานี</div>
        <div class="layer-title-en">View Rainfall</div>
    </div>
    <label class="switch">
        <input type="checkbox" id="toggle-rain-view">
        <span class="slider"></span>
    </label>
</div>
<!-- IDW --> 
<div class="layer-item">
    <div class="layer-icon">
        <i class="fas fa-chart-area" style="color: #4285F4;"></i>
    </div>
    <div class="layer-info">
        <div class="layer-title-th">แผนที่ประมาณค่าฝุ่น PM2.5 (IDW)</div>
        <div class="layer-title-en">PM2.5 IDW Interpolation</div>
    </div>
    <label class="switch">
        <input type="checkbox" id="toggle-idw">
        <span class="slider"></span>
    </label>
</div>



                    <!-- กราฟรวมทุกสถานี - เปลี่ยนเป็นกราฟเส้น -->
                    <div class="layer-item">
                        <div class="layer-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">กราฟแสดงข้อมูล 10 สถานี</div>
                            <div class="layer-title-en">Combined Station Line Chart</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-floating-chart">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Bus Transportation Category - ย้ายข้อมูลเส้นทางมาที่นี่ -->
            <div class="category-section">
                <div class="category-header" data-target="bus-layers">
                    <div class="category-title">
                        <span class="category-number">3</span>
                        <i class="fas fa-train" style="margin-right: 8px;"></i>
                        ระบบรถโดยสารในมหาวิทยาลัย
                    </div>
                    <i class="fas fa-chevron-down category-icon"></i>
                </div>
                <div class="category-content" id="bus-layers">
                    <!-- เส้นทางและป้ายรถไฟฟ้า -->
                    <div class="layer-item">
                        <div class="layer-icon">
                            <img src="./busstop_y.png" alt="Yellow Bus Stop">
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">ป้ายรถสายเหลือง</div>
                            <div class="layer-title-en">Yellow Line Bus Stops</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-yellow-stops">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="layer-item">
                        <div class="layer-icon">
                            <i class="fas fa-route" style="color: #FBBC05;"></i>
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">เส้นทางสายเหลือง</div>
                            <div class="layer-title-en">Yellow Line Route</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-yellow-route" >
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="layer-item">
                        <div class="layer-icon">
                            <img src="./busstop_r.png" alt="Red Bus Stop">
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">ป้ายรถสายแดง</div>
                            <div class="layer-title-en">Red Line Bus Stops</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-red-stops">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="layer-item">
                        <div class="layer-icon">
                            <i class="fas fa-route" style="color: #EA4335;"></i>
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">เส้นทางสายแดง</div>
                            <div class="layer-title-en">Red Line Route</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-red-route" >
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="layer-item">
                        <div class="layer-icon">
                            <i class="fas fa-route" style="color: #4285F4;"></i>
                        </div>
                        <div class="layer-info">
                                                        <div class="layer-title-th">เส้นทางสายน้ำเงิน</div>
                            <div class="layer-title-en">Blue Line Route</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-blue-route">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="layer-item">
                        <div class="layer-icon">
                            <i class="fas fa-train" style="color: #1e3c72;"></i>
                        </div>
                        <div class="layer-info">
                            <div class="layer-title-th">ตำแหน่งรถโดยสารแบบเรียลไทม์</div>
                            <div class="layer-title-en">Live Train Tracking</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="toggle-live-vehicles" >
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Infrastructure Category -->
            <div class="category-section">
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div id="map"></div>
        <div id="floating-chart-container"></div>
        
        <!-- Floating Line Chart Container -->
        <div id="floating-line-chart-container" style="display: none;">
            <div class="chart-header">
                <div class="chart-title">กราฟแสดงข้อมูลทั้ง 10 สถานี</div>
                <div class="chart-controls-inline">
                    <select id="floating-chart-data-type">
                        <option value="pm">ค่าฝุ่น (PM)</option>
                        <option value="pm25">ค่าฝุ่น PM2.5</option>
                        <option value="pm10">ค่าฝุ่น PM10</option>
                        <option value="temperature">อุณหภูมิ (°C)</option>
                        <option value="humidity">ความชื้น (%)</option>
                        <option value="wind_speed">ความเร็วลม (m/s)</option>
                        <option value="rain">ปริมาณฝน (mm)</option>
                    </select>
                    <div class="chart-mode-toggle">
                        <label><input type="radio" name="floatingChartMode" value="realtime" checked> เรียลไทม์</label>
                        <label><input type="radio" name="floatingChartMode" value="history"> ย้อนหลัง</label>
                    </div>
                </div>
                <div class="close-button" onclick="hideFloatingChart()">&times;</div>
            </div>
            
            <!-- History Date Controls -->
            <div id="floating-history-controls" style="display: none;">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 11px; font-weight: 500;">วันที่เริ่มต้น:</label>
                        <input type="date" id="floating-start-date" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif; font-size: 11px;">
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label style="font-size: 11px; font-weight: 500;">วันที่สิ้นสุด:</label>
                        <input type="date" id="floating-end-date" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; font-family: 'Sarabun', sans-serif; font-size: 11px;">
                    </div>
                    <button id="fetch-floating-history" style="padding: 6px 12px; background: #4285F4; color: white; border: none; border-radius: 4px; cursor: pointer; font-family: 'Sarabun', sans-serif; font-size: 11px;">
                        <i class="fas fa-search"></i> ดึงข้อมูลย้อนหลัง
                    </button>
                </div>
            </div>
            
            <div class="floating-chart-content">
                <canvas id="floatingLineChart"></canvas>
            </div>
        </div>
        
        <!-- Weather Legend (on map) -->
        <div class="weather-legend" id="weather-legend" style="display: none;">
            <div><strong>สีหมุดตามค่าฝุ่น PM2.5</strong></div>
            <div class="row"><span class="swatch" style="background:#007BFF"></span> ดีมาก (0-15.0 µg/m³)</div>
            <div class="row"><span class="swatch" style="background:#28A745"></span> ดี (15.1-25 µg/m³)</div>
            <div class="row"><span class="swatch" style="background:#FFC107"></span> ปานกลาง (25.1-37.5 µg/m³)</div>
            <div class="row"><span class="swatch" style="background:#FD7E14"></span> เริ่มมีผล (37.6-75 µg/m³)</div>
            <div class="row"><span class="swatch" style="background:#DC3545"></span> อันตราย (>75.0 µg/m³)</div>
            
            <hr />
            
        </div>
        <div class="footer-info">
            
        </div>
    </div>
   
    <script>
        // ---------- ข้อมูลพิกัดคณะและสำนักงาน ----------
window.facultyLocations = {
    'agri': { name: 'คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม', lat: 16.746383, lng: 100.196100 },
    'arch': { name: 'คณะสถาปัตยกรรมศาสตร์', lat: 16.746395, lng: 100.194868 },
    'eng': { name: 'คณะวิศวกรรมศาสตร์', lat: 16.743860, lng: 100.196515 },
    'social': { name: 'คณะสังคมศาสตร์', lat: 16.748997, lng: 100.196393 },
    'law': { name: 'คณะนิติศาสตร์', lat: 16.748868, lng: 100.196088 },
    'bus': { name: 'คณะบริหารธุรกิจ เศรษฐศาสตร์และการสื่อสาร', lat: 16.748785, lng: 100.196628 },
    'human': { name: 'คณะมนุษยศาสตร์', lat: 16.749175, lng: 100.194247 },
    'edu': { name: 'คณะศึกษาศาสตร์', lat: 16.747387, lng: 100.194196 },
    'pharma': { name: 'คณะเภสัชศาสตร์', lat: 16.746546, lng: 100.189920 },
    'dent': { name: 'คณะทันตแพทยศาสตร์', lat: 16.746553, lng: 100.189394 },
    'sahavej': { name: 'คณะสหเวชศาสตร์', lat: 16.746064, lng: 100.189329 },
    'nurs': { name: 'คณะพยาบาลศาสตร์', lat: 16.745468, lng: 100.189493 },
    'public': { name: 'คณะสาธารณสุขศาสตร์', lat: 16.745200, lng: 100.189803 },
    'medsci': { name: 'คณะวิทยาศาสตร์การแพทย์', lat: 16.745846, lng: 100.191327 },
    'sci': { name: 'คณะวิทยาศาสตร์', lat: 16.742166, lng: 100.194213 },
    'logis': { name: 'คณะโลจิสติกส์และดิจิทัลซัพพลายเชน', lat: 16.742474, lng: 100.191546 },
    'inter': { name: 'วิทยาลัยนานาชาติ', lat: 16.745603, lng: 100.193538 }
};
window.officeLocations = {
    'main_admin': { name: 'สำนักงานอธิการบดี', lat: 16.748185, lng: 100.192062 },
    'mingkwan': { name: 'อาคารมิ่งขวัญ', lat: 16.749019, lng: 100.192431 },
    'library': { name: 'สำนักหอสมุด', lat: 16.745862, lng: 100.193568 },
    'citcoms': { name: 'ตึก CITCOMS', lat: 16.747596, lng: 100.195486 },
    'khwanmuang': { name: 'อาคารขวัญเมือง', lat: 16.737228, lng: 100.199559 }
};

// ข้อมูลป้ายรถเมล์ตาม 
window.busStopYellowLocations = {
    '13': { name: 'ป้ายรถเมล์ NU YL - 13', lat: 16.742501839952524, lng: 100.19866832081888, destination: 'ป้ายหอพักอาจารย์และบุคลากร มน.นิเวศน์ 7-15' },
    '01': { name: 'ป้ายรถเมล์ NU YL - 01', lat: 16.74241860000000059, lng: 100.19730619999999988, destination: 'ป้ายอาคารปฎิบัติการคณะวิศวกรรม' },
    '02': { name: 'ป้ายรถเมล์ NU YL - 02', lat: 16.742038071589917, lng: 100.19568533951318, destination: 'ป้ายคณะวิทยาศาสตร์(สาขาเคมี)' },   
    '03': { name: 'ป้ายรถเมล์ NU YL - 03', lat: 16.74314534758817, lng: 100.19192926687391, destination: 'ป้ายอาคารเอกาทศรถ' },
    '04': { name: 'ป้ายรถเมล์ NU YL - 04', lat: 16.745239898180774, lng: 100.19267294750709, destination: 'ป้ายQS NUCANTEEN' },
    '05': { name: 'ป้ายรถเมล์ NU YL - 05', lat: 16.745642703119394, lng: 100.19037399059383, destination: 'ป้ายคณะสาธารณสุขศาสตร์' }, 
    '06': { name: 'ป้ายรถเมล์ NU YL - 06', lat: 16.747600203501495, lng: 100.18966231838917, destination: 'ป้ายอาคารคณะทันตแพทยศาสตร์' },
    '07': { name: 'ป้ายรถเมล์ NU YL - 07', lat: 16.75068673963867, lng: 100.1898203651526, destination: 'ป้ายพิพิธภัณฑ์ชีวิต ประตู6' },
    '08': { name: 'ป้ายรถเมล์ NU YL - 08', lat: 16.75011058579916, lng: 100.1910655026821, destination: 'ป้ายสถานีวิทยุ ลานสมเด็จ' },
    '09': { name: 'ป้ายรถเมล์ NU YL - 09', lat: 16.749882311607408, lng: 100.1937495402326, destination: 'ป้ายอาคารอเนกประสงค์(โดม)' },
    '10': { name: 'ป้ายรถเมล์ NU YL - 10', lat: 16.748051404713042, lng: 100.19394314477096, destination: 'ป้ายอาคารปราบไตรจักร2' },
    '11': { name: 'ป้ายรถเมล์ NU YL - 11', lat: 16.748211554793443, lng: 100.19590000130128, destination: 'ป้ายคณะนิติศาสตร์' },
    '12': { name: 'ป้ายรถเมล์ NU YL - 12', lat: 16.746252467932344, lng: 100.19697488059657, destination: 'ป้ายสระว่ายนํ้าสุพรรณกัลยา' }
};

window.busStopRedLocations = {
    '12': { name: 'ป้ายรถเมล์ NU RL - 12', lat: 16.742268493660585, lng: 100.19873744944994, destination: 'ป้ายหอพัก มน นิเวศน์ 7-15' },
    '01': { name: 'ป้ายรถเมล์ NU RL - 01', lat: 16.74399694418608, lng: 100.19769039925352, destination: 'ป้ายหน้าคณะวิศวกรรม' },
    '02': { name: 'ป้ายรถเมล์ NU RL - 02', lat: 16.746646919967027, lng: 100.19661447423158, destination: 'ป้ายคณะเกษตรศาสตร์' },
    '03': { name: 'ป้ายรถเมล์ NU RL - 03', lat: 16.748281056060517, lng: 100.19570028509864, destination: 'ป้ายCITCOMS' },   
    '05': { name: 'ป้ายรถเมล์ NU RL - 05', lat: 16.74816982611633, lng: 100.19382974671647, destination: 'ป้ายอาคารปราบไตรจักร1' },
    '06': { name: 'ป้ายรถเมล์ NU RL - 06', lat: 16.749562676255938, lng: 100.19228673271166, destination: 'ป้ายอาคารมิ่งขวัญ' },
    '07': { name: 'ป้ายรถเมล์ NU RL - 07', lat: 16.746511622799467,  lng:100.18981123916531, destination: 'ป้ายคณะเภสัชศาสตร์' },
    '08': { name: 'ป้ายรถเมล์ NU RL - 08', lat: 16.745153628182358, lng: 100.19273728909583, destination: 'ป้ายQS' },
    '09': { name: 'ป้ายรถเมล์ NU RL - 09', lat: 16.74421599536903, lng: 100.1912495070864, destination: 'ป้ายสระเอกกษัตริย์' },
    '10': { name: 'ป้ายรถเมล์ NU RL - 10', lat: 16.742538164356752, lng: 100.19296363791061, destination: 'ป้ายคณะวิทยาศาสตร์(สาขาคณิตศาสตร์)' },
    '11': { name: 'ป้ายรถเมล์ NU RL - 11', lat: 16.742575856442695, lng: 100.19537042387871, destination: 'ป้ายหน้าคณะวิทยาศาสตร์' }
};

// ==================== ข้อมูลสถานที่สำหรับระบบเส้นทาง ====================
// ข้อมูลสถานที่ที่จัดหมวดหมู่
const categorizedPlaces = {
    'faculty': [
        'คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม', 'คณะสถาปัตยกรรมศาสตร์', 'คณะวิศวกรรมศาสตร์', 
        'คณะสังคมศาสตร์', 'คณะนิติศาสตร์', 'คณะบริหารธุรกิจ เศรษฐศาสตร์และการสื่อสาร',
        'คณะมนุษยศาสตร์', 'คณะศึกษาศาสตร์', 'คณะเภสัชศาสตร์', 'คณะทันตแพทยศาสตร์',
        'คณะสหเวชศาสตร์', 'คณะพยาบาลศาสตร์', 'คณะสาธารณสุขศาสตร์', 'คณะวิทยาศาสตร์การแพทย์',
        'คณะวิทยาศาสตร์', 'คณะโลจิสติกส์และดิจิทัลซัพพลายเชน', 'วิทยาลัยนานาชาติ'
    ],
    'office': [
        'สำนักงานอธิการบดี', 'อาคารมิ่งขวัญ', 'สำนักหอสมุด', 'ตึก CITCOMS',
        'อาคารขวัญเมือง'
    ],
    'busstop_yellow': [
        'ป้ายหอพักอาจารย์และบุคลากร มน.นิเวศน์', 'ป้ายอาคารปฎิบัติการคณะวิศวกรรม', 'ป้ายคณะวิทยาศาสตร์(สาขาเคมี)', 
        'ป้ายอาคารเอกาทศรถ', 'ป้ายQS ', 'ป้ายหน้าคณะสาธารณสุขศาสตร์',
        'ป้ายอาคารคณะทันตแพทยศาสตร์', 'ป้ายพิพิธภัณฑ์ชีวิต ประตู6', 'ป้ายสถานีวิทยุ ลานสมเด็จ',
        'ป้ายอาคารอเนกประสงค์(โดม)', 'ป้ายอาคารปราบไตรจักร2', 'ป้ายคณะนิติศาสตร์',
        'ป้ายสระว่ายนํ้าสุพรรณกัลยา'
    ],
    'busstop_red': [
        'ป้ายหอพักอาจารย์และบุคลากร มน.นิเวศน์', 'ป้ายหน้าคณะวิทยาศาสตร์', 'ป้ายคณะวิทยาศาสตร์(สาขาคณิตศาสตร์)',
        'ป้ายสระเอกกษัตริย์', 'ป้ายQS', 'ป้ายคณะเภสัชศาสตร์',
        'ป้ายอาคารมิ่งขวัญ', 'ป้ายอาคารปราบไตรจักร1',
        'ป้ายCITCOMS', 'ป้ายคณะเกษตรศาสตร์', 'ป้ายหน้าคณะวิศวกรรม'
    ]
};

// ==================== ระบบเส้นทาง - JavaScript Functions ====================
// Global Variables สำหรับระบบเส้นทาง
let startPoint = null; 
let endPoint = null;
let startMarker = null;
let endMarker = null;
let routeLayer = null;

let currentStartType = null; 
let currentEndType = null;

// Function สำหรับแปลงเวลาเป็นรูปแบบที่อ่านง่าย
function formatTime(totalSeconds) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = Math.round(totalSeconds % 60);
    if (minutes > 0) {
        return `${minutes} นาที ${seconds} วินาที`;
    }
    return `${seconds} วินาที`;
}

// Function สำหรับเคลียร์ Marker และเส้นทางทั้งหมด
function resetMarkersAndRoute() {
    console.log('Resetting markers and route');
    if (routeLayer) {
        console.log('Removing route layer');
        map.removeLayer(routeLayer);
    }
    if (startMarker) map.removeLayer(startMarker);
    if (endMarker) map.removeLayer(endMarker);
    startMarker = null;
    endMarker = null;
    startPoint = null;
    endPoint = null;
    
    $('.place-select-group select').val('');
    $('.place-select-group').hide();
    $('.type-button').removeClass('active');
    currentStartType = null;
    currentEndType = null;
    
    $('#start_info').html('จุดเริ่มต้น: <strong>ยังไม่ได้กำหนด</strong>');
    $('#end_info').html('จุดปลายทาง: <strong>ยังไม่ได้กำหนด</strong>');
    $('#travel_time').text('--');
}

// Function สำหรับดึงพิกัดจากชื่อสถานที่
function getCoordinatesFromPlace(placeName, type) {
    if (type === 'faculty' && window.facultyLocations) {
        for (let key in window.facultyLocations) {
            if (window.facultyLocations[key].name === placeName) {
                return window.facultyLocations[key];
            }
        }
    } else if (type === 'office' && window.officeLocations) {
        for (let key in window.officeLocations) {
            if (window.officeLocations[key].name === placeName) {
                return window.officeLocations[key];
            }
        }
    } else if (type === 'busstop_yellow' && window.busStopYellowLocations) {
        for (let key in window.busStopYellowLocations) {
            if (window.busStopYellowLocations[key].name === placeName) {
                console.log('Found yellow bus stop:', window.busStopYellowLocations[key]);
                return window.busStopYellowLocations[key];
            }
        }
    } else if (type === 'busstop_red' && window.busStopRedLocations) {
        for (let key in window.busStopRedLocations) {
            if (window.busStopRedLocations[key].name === placeName) {
                console.log('Found red bus stop:', window.busStopRedLocations[key]);
                return window.busStopRedLocations[key];
            }
        }
    }
    return null;
}

// ==================== Event Listeners สำหรับระบบเส้นทาง ====================
$(document).ready(function() {
    // เติม Dropdown ตามหมวดหมู่
    for (const type in categorizedPlaces) {
        categorizedPlaces[type].forEach(place => {
            const option = `<option value="${place}">${place}</option>`;
            $(`#start_${type}_select`).append(option);
            $(`#end_${type}_select`).append(option);
        });
    }
    
    // Event Listener สำหรับปุ่มเลือกประเภทสถานที่
    $('.type-button').on('click', function() {
        const type = $(this).data('type');
        const role = $(this).data('for'); 
        const buttonText = $(this).text().trim();
        
        // ล้าง Dropdown ที่ไม่เกี่ยวข้องและยกเลิกสถานะ Active ของปุ่มอื่น
        $(`#${role}_type_selector .type-button`).removeClass('active');
        $(`#${role}_type_selector ~ .place-select-group`).hide();
        $(`#${role}_type_selector ~ .place-select-group select`).val('');

        // กำหนดสถานะ Active
        $(this).addClass('active');

        // แสดง Dropdown ที่ถูกต้อง
        $(`#${role}_${type}_group`).show();

        // ตั้งค่าประเภทปัจจุบัน
        if (role === 'start') {
            currentStartType = type;
            $('#start_info').html(`จุดเริ่มต้น: <strong>เลือกจาก ${buttonText}</strong>`);
        } else {
            currentEndType = type;
            $('#end_info').html(`จุดปลายทาง: <strong>เลือกจาก ${buttonText}</strong>`);
        }
    });

    // Event Listener เมื่อเลือกจาก Dropdown (สำหรับทั้ง Start และ End)
    $('.place-select-group select').on('change', function() {
        const value = $(this).val();
        const role = $(this).attr('id').startsWith('start') ? 'start' : 'end';
        
        if (role === 'start') {
            if (startMarker) map.removeLayer(startMarker);
            startMarker = null;
            startPoint = null; 
            $('#start_info').html(`จุดเริ่มต้น: <strong>${value || 'ยังไม่ได้กำหนด'}</strong>`);
        } else {
            if (endMarker) map.removeLayer(endMarker);
            endMarker = null;
            endPoint = null; 
            $('#end_info').html(`จุดปลายทาง: <strong>${value || 'ยังไม่ได้กำหนด'}</strong>`);
        }
    });
    
    // Event Listener สำหรับปุ่มล้าง
    $('#clear_map_btn').on('click', resetMarkersAndRoute);
});

// Map click functionality removed - only dropdown selection is used for route planning

// Event Listener สำหรับปุ่มคำนวณเส้นทาง
$('#calculate_route_btn').on('click', function() {
    console.log('Calculate route button clicked');
    const routeType = $('#route_select').val();
    console.log('Route type selected:', routeType);
    
    let startPlace = currentStartType ? $(`#start_${currentStartType}_select`).val() : null;
    let endPlace = currentEndType ? $(`#end_${currentEndType}_select`).val() : null;
    
    let dataToSend = { route_type: routeType };
    let startName, endName;

    // ตรวจสอบเงื่อนไขการส่งข้อมูล: ใช้เฉพาะ Dropdown selection
    if (startPlace && endPlace) {
        dataToSend.start_place = startPlace;
        dataToSend.end_place = endPlace;
        startName = startPlace;
        endName = endPlace;
        console.log('Using place names for routing:', { startPlace, endPlace });
    } else {
        alert('กรุณาเลือกจุดเริ่มต้นและจุดปลายทางให้ครบถ้วน');
        return;
    }
    
    if (!routeType) {
        alert('กรุณาเลือกประเภทพาหนะ');
        return;
    }

    if (routeLayer) {
        console.log('Removing existing route layer before calculation');
        map.removeLayer(routeLayer);
    }
    
    // ปิดสถานีและสีหมุดตามค่าฝุ่นเมื่อคำนวณเส้นทาง
    if (pmColorEnabled) {
        document.getElementById('toggle-weather').checked = false;
        pmColorEnabled = false;
        hideWeatherLegendFromMap();
        // ซ่อนสถานีทั้งหมด
        if (layerW1) map.removeLayer(layerW1);
        if (layerW2) map.removeLayer(layerW2);
        loadAllMarkers();
    }
    
    $('#travel_time').text('กำลังคำนวณ...');
    console.log('Data to send:', dataToSend);

    $.ajax({
        url: './pgrouting.php', 
        method: 'GET',
        dataType: 'json', 
        data: dataToSend,
        success: function(response) {
            console.log('AJAX Response received:', response);
            
            if (response.geojson) {
                console.log('GeoJSON data found in response');
                const geojsonData = JSON.parse(response.geojson);
                
                let color = '#28a745'; 
                if (routeType === 'car') color = '#dc3545';
                else if (routeType === 'motorcycle') color = '#ffc107';
                else if (routeType === 'bike') color = '#007bff';

                routeLayer = L.geoJSON(geojsonData, {
                    style: {
                        color: color,
                        weight: 6,
                        opacity: 0.8
                    }
                }).addTo(map);
                
                // Debug: ตรวจสอบว่า routeLayer ถูกสร้างและเพิ่มลงใน map แล้ว
                console.log('Route layer created:', routeLayer);
                console.log('Route layer added to map:', map.hasLayer(routeLayer));
                console.log('Route layer features count:', routeLayer.getLayers().length);
                console.log('Route layer bounds:', routeLayer.getBounds());

               if (response.start_coords) {
    const s_coords = response.start_coords;
    const popupText = `จุดเริ่มต้น: ${startName}`;
    
    // สร้าง custom icon สีเขียวสำหรับจุดเริ่มต้น
    const startIcon = L.divIcon({
        html: `
            <div style="
                width: 32px;
                height: 32px;
                border-radius: 50% 50% 50% 0;
                background-color: #28a745;
                border: 3px solid white;
                box-shadow: 0 3px 10px rgba(0,0,0,0.3);
                transform: rotate(-45deg);
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <i class="fas fa-flag" style="
                    color: white;
                    font-size: 14px;
                    transform: rotate(45deg);
                "></i>
            </div>
        `,
        className: 'custom-start-icon',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
    
    startMarker = L.marker([s_coords.lat, s_coords.lng], {icon: startIcon})
        .addTo(map)
        .bindPopup(popupText)
        .openPopup();
}

if (response.end_coords) {
    const e_coords = response.end_coords;
    const popupText = `จุดปลายทาง: ${endName}`;
    
    // สร้าง custom icon สีแดงสำหรับจุดปลายทาง
    const endIcon = L.divIcon({
        html: `
            <div style="
                width: 32px;
                height: 32px;
                border-radius: 50% 50% 50% 0;
                background-color: #dc3545;
                border: 3px solid white;
                box-shadow: 0 3px 10px rgba(0,0,0,0.3);
                transform: rotate(-45deg);
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <i class="fas fa-map-marker-alt" style="
                    color: white;
                    font-size: 14px;
                    transform: rotate(45deg);
                "></i>
            </div>
        `,
        className: 'custom-end-icon',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
    
    endMarker = L.marker([e_coords.lat, e_coords.lng], {icon: endIcon})
        .addTo(map)
        .bindPopup(popupText)
        .openPopup();
}
                
                $('#start_info').html(`จุดเริ่มต้น: <strong>${startName}</strong>`);
                $('#end_info').html(`จุดปลายทาง: <strong>${endName}</strong>`);

                if (response.total_time_sec) {
                    $('#travel_time').html(`✅ <strong>${formatTime(response.total_time_sec)}</strong>`);
                }

                map.fitBounds(routeLayer.getBounds());
                
                // Debug: ตรวจสอบว่าเส้นทางแสดงบนแผนที่
                console.log('Route bounds:', routeLayer.getBounds());
                console.log('Map layers count:', map._layers ? Object.keys(map._layers).length : 'Unknown');
                
                // ตรวจสอบว่า route layer มีข้อมูลและแสดงผล
                if (routeLayer.getLayers().length === 0) {
                    console.error('Route layer has no features!');
                } else {
                    console.log('Route layer has', routeLayer.getLayers().length, 'features');
                    // ตรวจสอบสไตล์ของแต่ละ feature
                    routeLayer.eachLayer(function(layer) {
                        console.log('Feature style:', layer.options);
                    });
                }
                
            } else if (response.message) {
                $('#travel_time').html(`❌ <strong>ไม่พบเส้นทาง</strong> (${routeType} ไปไม่ได้)`);
                alert(`ไม่พบเส้นทางสำหรับ ${routeType}: ${response.message}`);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Status:", status);
            console.error("HTTP Error:", error);
            let errorMessage = 'เกิดข้อผิดพลาดในการคำนวณเส้นทาง: โปรดตรวจสอบ Console';
            try {
                const responseJson = JSON.parse(xhr.responseText);
                if (responseJson.sql_error_detail) {
                     errorMessage = `🚨 PG QUERY FAILED: ${responseJson.sql_error_detail}`;
                } else if (responseJson.error) {
                     errorMessage = `ข้อผิดพลาดจาก Server: ${responseJson.error}`;
                }
            } catch (e) {
                 errorMessage = 'ข้อผิดพลาดร้ายแรง Server: โปรดตรวจสอบ Logs บนเซิร์ฟเวอร์';
            }
            
            alert(errorMessage);
            $('#travel_time').html('❌ <strong>เกิดข้อผิดพลาด</strong>');
        }
    });
});





        // Show loading spinner
        document.getElementById('loadingSpinner').style.display = 'block';

        // Weather System Constants - ใช้ endpoint เดียวสำหรับข้อมูล weather ทั้งหมด
        const ENDPOINT_WEATHER_LATEST = './weather_2.php';
        const ENDPOINT_W1_HISTORY = './weather_history_1.php';
        const ENDPOINT_W2_HISTORY = './weather_history_2.php';
        
        const REFRESH_MS = 30_000; // อัปเดตทุก 30 วินาที

        // Station Config - ใช้ endpoint เดียวสำหรับข้อมูล weather ทั้งหมด
        const STATIONS = [
            { name: 'คณะเกษตรศาสตร์ทรัพยากรธรรมชาติและสิ่งแวดล้อม', eui: '0000fcc23d222cb9', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W1_HISTORY, lat: 16.7441, lng: 100.1972 },
            { name: 'แปลงเกษตร', eui: '0000fcc23d223e2f', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W1_HISTORY, lat: 16.7423, lng: 100.1985 },
            { name: 'อาคาร KNECC', eui: '0000fcc23d22ac5d', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W1_HISTORY, lat: 16.7460, lng: 100.1955 },
            { name: 'สนามฟุตบอล', eui: '0000fcc23d22248c', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W1_HISTORY, lat: 16.7465, lng: 100.1980 },
            { name: 'สนามฟุตซอล', eui: '0000fcc23d221b88', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W1_HISTORY, lat: 16.7387, lng: 100.1993 },  
            { name: 'คณะวิทยาศาสตร์', eui: '0000fcc23d224d77', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W2_HISTORY, lat: 16.7470, lng: 100.1990 },
            { name: 'อ่างเก็บน้ำหลังหอใน', eui: '0000fcc23d22894f', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W2_HISTORY, lat: 16.7435, lng: 100.1950 },
            { name: 'โรงเรียนอนุบาลและประถมศาธิต', eui: '0000fcc23d224ae6', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W2_HISTORY, lat: 16.7480, lng: 100.1965 },
            { name: 'คณะพยาบาลศาสตร์', eui: '0000fcc23d229a41', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W2_HISTORY, lat: 16.7495, lng: 100.1975 },
            { name: 'ลานสมเด็จพระนเรศวรมหาราช', eui: '0000fcc23d22ad80', latest_endpoint: ENDPOINT_WEATHER_LATEST, history_endpoint: ENDPOINT_W2_HISTORY, lat: 16.7510, lng: 100.1980 }
        ];

        // Chart Field Mapping
        const chartFieldMap = {
            pm: { label: "ค่าฝุ่น (µg/m³)", color: "#e67e22", get: d => Number(d.pm ?? d.pm25 ?? 0) },
            pm25: { label: "ค่าฝุ่น PM2.5 (µg/m³)", color: "#e67e22", get: d => Number(d.pm25 ?? 0) },
            pm10: { label: "ค่าฝุ่น PM10 (µg/m³)", color: "#f1c40f", get: d => Number(d.pm10 ?? 0) },
            temperature: { label: "อุณหภูมิ (°C)", color: "#e74c3c", get: d => Number(d.temperature ?? 0) },
            humidity: { label: "ความชื้น (%)", color: "#3498db", get: d => Number(d.humidity ?? 0) },
            wind_speed: { label: "ความเร็วลม (m/s)", color: "#16a085", get: d => Number(d.wind_speed ?? 0) },
            rain: { label: "ปริมาณฝน (mm)", color: "#1abc9c", get: d => Number(d.rain ?? 0) },
        };

        // Variables - แก้ไขระบบจัดเก็บข้อมูลเรียลไทม์
        let latestStationData = [];
        const dailyRealtimeData = {}; // เก็บข้อมูลทั้งวัน
        const floatingRealtimeData = {}; // เก็บข้อมูลสำหรับกราฟรวม 10 สถานี
        let chartInstance = null;
        let floatingChartInstance = null;
        let activeEui = null;
        const floatingChartContainer = document.getElementById('floating-chart-container');
        const floatingLineChartContainer = document.getElementById('floating-line-chart-container');
        let pmColorEnabled = true;
        let isNavigationMode = false; // ตัวแปรติดตามสถานะโหมดนำทาง
        
        // ตั้งค่าให้สถานีและสีหมุดตามค่าฝุ่นเปิดขึ้นมาโดยอัตโนมัติเมื่อโหลดหน้าเว็บ
        document.addEventListener('DOMContentLoaded', function() {
            // เปิดสวิตช์สถานีและสีหมุดตามค่าฝุ่น
            const weatherToggle = document.getElementById('toggle-weather');
            if (weatherToggle) {
                weatherToggle.checked = true;
                pmColorEnabled = true;
                showWeatherLegendOnMap();
                
                // เปิด layer อัตโนมัติ
                if (layerW1) map.addLayer(layerW1);
                if (layerW2) map.addLayer(layerW2);
            }
            
            // Mobile menu functionality
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.querySelector('.sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const backToMapBtn = document.getElementById('backToMapBtn');
            const closeMobileMenuBtn = document.getElementById('closeMobileMenuBtn');
            
            if (mobileMenuBtn && sidebar && mobileOverlay) {
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    mobileOverlay.classList.toggle('active');
                });
                
                mobileOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    mobileOverlay.classList.remove('active');
                });
            }
            
            // Close mobile menu functionality
            if (closeMobileMenuBtn) {
                closeMobileMenuBtn.addEventListener('click', function() {
                    if (sidebar) {
                        sidebar.classList.remove('active');
                    }
                    if (mobileOverlay) {
                        mobileOverlay.classList.remove('active');
                    }
                });
            }
            
            // Notification function
            window.showNotification = function(message, type = 'info') {
                // สร้าง notification element
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <div class="notification-header">
                        <div class="notification-title">${type === 'success' ? 'สำเร็จ' : 'แจ้งเตือน'}</div>
                        <button class="notification-close">&times;</button>
                    </div>
                    <div class="notification-body">${message}</div>
                `;
                
                // เพิ่ม notification container ถ้ายังไม่มี
                let container = document.querySelector('.notification-container');
                if (!container) {
                    container = document.createElement('div');
                    container.className = 'notification-container';
                    document.body.appendChild(container);
                }
                
                // เพิ่ม notification
                container.appendChild(notification);
                
                // แสดง notification
                setTimeout(() => notification.classList.add('show'), 100);
                
                // ปิด notification อัตโนมัติ
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
                
                // ปิด notification เมื่อคลิกปุ่มปิด
                notification.querySelector('.notification-close').addEventListener('click', () => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                });
            };
            
            // Back to map functionality
            if (backToMapBtn) {
                backToMapBtn.addEventListener('click', function() {
                    // ปิดเมนูมือถือ
                    if (sidebar) {
                        sidebar.classList.remove('active');
                    }
                    if (mobileOverlay) {
                        mobileOverlay.classList.remove('active');
                    }
                    
                    // ซูมไปที่แผนที่หลัก
                    if (window.map) {
                        // ซูมไปที่มหาวิทยาลัยนเรศวร
                        window.map.setView([16.8208, 100.2651], 16);
                        
                        // แสดงการแจ้งเตือน
                        window.showNotification('กลับไปหน้าแผนที่แล้ว', 'success');
                    }
                });
            }
            
            // Touch-friendly interactions
            let touchStartY = 0;
            let touchStartX = 0;
            
            // Handle touch events for better mobile experience
            document.addEventListener('touchstart', function(e) {
                touchStartY = e.touches[0].clientY;
                touchStartX = e.touches[0].clientX;
            }, { passive: true });
            
            document.addEventListener('touchmove', function(e) {
                // Prevent default scrolling behavior for certain elements
                if (e.target.closest('.chart-container') || 
                    e.target.closest('#floating-chart-container') ||
                    e.target.closest('#floating-line-chart-container')) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            // Add touch feedback for buttons
            const buttons = document.querySelectorAll('button, .type-button, .layer-item, .category-header');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                    this.style.transition = 'transform 0.1s ease';
                });
                
                button.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
                
                button.addEventListener('touchcancel', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Improve popup interactions on mobile
            document.addEventListener('click', function(e) {
                if (e.target.closest('.leaflet-popup-close-button')) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            
            // Handle orientation change
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    if (window.map) {
                        window.map.invalidateSize();
                    }
                }, 100);
            });
            
            // Prevent zoom on double tap for certain elements
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(e) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
        });
        let currentDate = new Date().toDateString(); // เก็บวันที่ปัจจุบัน
        let isPageVisible = true; // ตรวจสอบว่าหน้าเว็บกำลังใช้งานหรือไม่
        const MAX_REALTIME_POINTS = 288; // จำกัดที่ 288 จุด (24 ชม. x 12 จุดต่อชม.)
        
        

        // ตรวจสอบการมองเห็นหน้าเว็บ
        document.addEventListener('visibilitychange', function() {
            isPageVisible = !document.hidden;
        });

        // ฟังก์ชันตรวจสอบและรีเซ็ตข้อมูลเมื่อหมดวัน
        function checkAndResetDailyData() {
            const today = new Date().toDateString();
            if (currentDate !== today) {
                console.log('วันใหม่ - รีเซ็ตข้อมูลเรียลไทม์');
                // รีเซ็ตข้อมูลทั้งหมด
                Object.keys(dailyRealtimeData).forEach(eui => {
                    dailyRealtimeData[eui] = [];
                });
                Object.keys(floatingRealtimeData).forEach(eui => {
                    floatingRealtimeData[eui] = [];
                });
                currentDate = today;
            }
        }
        

        // Map
        var map = L.map('map').setView([16.746678324901865, 100.193070859123991], 15);
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
                var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© ESRI'
        });
        osm.addTo(map);

        map.whenReady(function() {
            setTimeout(() => {
                document.getElementById('loadingSpinner').style.display = 'none';
            }, 1000);
            
            // Map click handlers removed - only dropdown selection is used
        });

        // Layer Groups
        const layerW1 = L.layerGroup();
        const layerW2 = L.layerGroup();
        const markers = {};

        // ฟังก์ชันแปลงค่าฝุ่น PM2.5 เป็นสีตามมาตรการคุณภาพอากาศไทย (แก้ไขตามที่ขอ)
        function getColorForPM(pm) {
            if (pm == null || isNaN(pm)) return [127, 140, 141, 0.8]; // เทา default
            pm = Number(pm);
            if (pm <= 15.0) {
                return [0, 123, 255, 0.8];   // ฟ้า - ดีมาก
            } else if (pm <= 25.0) {
                return [40, 167, 69, 0.8];   // เขียว - ดี
            } else if (pm <= 37.5) {
                return [255, 193, 7, 0.8];   // เหลือง - ปานกลาง
            } else if (pm <= 75.0) {
                return [253, 126, 20, 0.8];  // ส้ม - เริ่มมีผลกระทบ
            } else {
                return [220, 53, 69, 0.8];   // แดง - มีผลกระทบ
            }
        }

        // PM Color function (ปรับแต่งสีตามค่าฝุ่น PM2.5)
        function pmColor(v) {
            if (v == null || isNaN(v)) return '#7f8c8d';
            v = Number(v);
            
            // ตามมาตรฐาน WHO และกรมควบคุมมลพิษ
            if (v <= 12.0) return '#007BFF';      // ฟ้า - ดีมาก (0-12 µg/m³)
            if (v <= 25.0) return '#28A745';     // เขียว - ดี (12.1-25 µg/m³)
            if (v <= 37.5) return '#FFC107';     // เหลือง - ปานกลาง (25.1-37.5 µg/m³)
            if (v <= 50.0) return '#FD7E14';     // ส้ม - เริ่มมีผลกระทบ (37.6-50 µg/m³)
            if (v <= 75.0) return '#FF6B35';     // ส้มแดง - มีผลกระทบ (50.1-75 µg/m³)
            return '#DC3545';                    // แดง - อันตราย (>75 µg/m³)
        }

        function normalizeW1(d) {
            // แปลงค่า pm เป็น number และจัดรูปแบบเป็นจำนวนเต็ม
            const pmValue = Number(d.pm);
            const formattedPM = isNaN(pmValue) ? null : Math.round(pmValue);
            
            return {
                source_table: 'weather_station1', eui: d.eui, name: d.name, location: d.location_name,
                lat: Number(d.latitude), lng: Number(d.longitude),
                temperature: d.temperature, humidity: d.humidity, pm: formattedPM,
                pm25: formattedPM, pm10: null, // ใส่ pm25 = pm สำหรับ w1
                wind_speed: d.wind_speed, wind_direct: d.wind_direct,
                rain: d.rain, rainacc: d.rainacc, timestamp: d.timestamp
            };
        }

        function normalizeW2(d) {
            // แปลงค่า pm เป็น number และจัดรูปแบบเป็นจำนวนเต็ม
            const pmValue = Number(d.pm);
            const formattedPM = isNaN(pmValue) ? null : Math.round(pmValue);
            
            return {
                source_table: 'weather_station2', eui: d.eui, name: d.name, location: d.location_name,
                lat: Number(d.latitude), lng: Number(d.longitude),
                temperature: d.temperature, humidity: d.humidity, pm: formattedPM,
                pm25: formattedPM, pm10: d.pm10,
                wind_speed: null, wind_direct: null, rain: null, rainacc: null,
                timestamp: d.timestamp
            };
        }

        // แก้ไข weather_history_2.php endpoint - สร้าง dynamic endpoint
        function getHistoryEndpoint(station, startDate, endDate) {
            if (station.source_table === 'weather_station2') {
                // สำหรับ weather_station2 ใช้ weather_2_history.php พร้อม parameter สำหรับ history
                return `./weather_2_history.php?eui=${station.eui}&start=${startDate}&end=${endDate}`;
            } else {
                // สำหรับ weather_station1 ใช้ endpoint เดิม
                return `${station.history_endpoint}?eui=${station.eui}&start=${startDate}&end=${endDate}`;
            }
        }

        // แก้ไขฟังก์ชัน createChart สำหรับกราฟแต่ละสถานี
        function createChart(canvas, labels, values, chartType, chartLabel, isRealtime = false) {
            if (chartInstance) chartInstance.destroy();
            const ctx = canvas.getContext('2d');
            const chartMeta = chartFieldMap[chartType];
            const chartTypeToUse = 'line';
            chartInstance = new Chart(ctx, {
                type: chartTypeToUse,
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartLabel || chartMeta.label,
                        data: values,
                        borderColor: chartMeta.color,
                        backgroundColor: isRealtime ? chartMeta.color + '99' : chartMeta.color + '33',
                        pointRadius: isRealtime ? 4 : 3,
                        pointBackgroundColor: chartMeta.color,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        tension: 0.2,
                        borderWidth: isRealtime ? 1 : 2,
                        borderRadius: isRealtime ? 4 : 0,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    layout: {
                        padding: {
                            bottom: 40
                        }
                    },
                    plugins: { 
                        legend: { 
                            display: true, 
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 6,
                                font: {
                                    size: 8
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: chartMeta.color,
                            borderWidth: 1,
                            cornerRadius: 4,
                            displayColors: true,
                            titleFont: {
                                size: 10,
                                family: 'Sarabun'
                            },
                            bodyFont: {
                                size: 9,
                                family: 'Sarabun'
                            },
                            padding: 6,
                            caretSize: 4,
                            callbacks: {
                                title: function(context) {
                                    const label = context[0].label;
                                    if (label) {
                                        // ถ้า label เป็น string ที่มีรูปแบบวันที่ ให้แปลงเป็น Date
                                        if (typeof label === 'string' && label.includes('/')) {
                                            const date = new Date(label);
                                            if (!isNaN(date.getTime())) {
                                                return date.toLocaleDateString('th-TH', {
                                                    day: '2-digit',
                                                    month: '2-digit',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                });
                                            }
                                        }
                                        // ถ้า label เป็น Date object หรือ timestamp
                                        const date = new Date(label);
                                        if (!isNaN(date.getTime())) {
                                            return date.toLocaleDateString('th-TH', {
                                                day: '2-digit',
                                                month: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            });
                                        }
                                    }
                                    return label || 'Invalid Date';
                                },
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const unit = chartMeta.label.includes('°C') ? '°C' : 
                                               chartMeta.label.includes('%') ? '%' : 
                                               chartMeta.label.includes('m/s') ? 'm/s' :
                                               chartMeta.label.includes('mm') ? 'mm' :
                                               chartMeta.label.includes('dB') ? 'dB' : 'µg/m³';
                                    return `${chartMeta.label}: ${value.toFixed(1)} ${unit}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: { 
                                display: true, 
                                text: isRealtime ? 'เวลา (ข้อมูลตลอดวัน)' : 'วันและเวลา',
                                font: { family: 'Sarabun' },
                                padding: 10
                            },
                            grid: { display: !isRealtime },
                            ticks: {
                                font: { family: 'Sarabun', size: 7 },
                                maxRotation: 45,
                                maxTicksLimit: 8, // จำกัดจำนวน tick ในแกน X สำหรับมือถือ
                                minRotation: 0,
                                padding: 8
                            }
                        },
                        y: { 
                            title: { 
                                display: true, 
                                text: chartMeta.label,
                                font: { family: 'Sarabun' }
                            }, 
                            beginAtZero: true,
                            grid: { 
                                color: 'rgba(0,0,0,0.1)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: { family: 'Sarabun', size: 10 },
                                maxTicksLimit: 6 // จำกัดจำนวน tick ในแกน Y สำหรับมือถือ
                            }
                        }
                    },
                    animation: {
                        duration: isRealtime ? 500 : 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        
// แก้ไขฟังก์ชัน createFloatingLineChart สำหรับกราฟรวม 10 สถานี
function createFloatingLineChart(dataType, isRealtime, historyData = null) {
    const canvas = document.getElementById('floatingLineChart');
    const ctx = canvas.getContext('2d');
    if (floatingChartInstance) floatingChartInstance.destroy();
    
    const fieldMap = chartFieldMap[dataType];
    
    // รวม timestamps ทั้งหมดตามโหมด
    const tsSet = new Set();
    if (isRealtime) {
        STATIONS.forEach(st => {
            const stationData = floatingRealtimeData[st.eui] || [];
            console.log(`สถานี ${st.name}: ${stationData.length} จุดข้อมูล`);
            stationData.forEach(d => d.timestamp && tsSet.add(d.timestamp));
        });
        console.log(`รวม timestamps ทั้งหมด: ${tsSet.size} จุดเวลา`);
    } else if (historyData) {
        Object.values(historyData).forEach(list => list.forEach(r => r.timestamp && tsSet.add(r.timestamp)));
    }

    const sortedTs = Array.from(tsSet).sort();
    console.log(`Timestamps ที่เรียงแล้ว: ${sortedTs.length} จุด`);
    
    // ปรับปรุงการเลือก timestamps สำหรับโหมดเรียลไทม์
    let selectedTs;
    if (isRealtime) {
        // สำหรับโหมดเรียลไทม์ ให้แสดงเฉพาะข้อมูลของวันนี้
        const today = new Date();
        const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const todayEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);
        
        selectedTs = sortedTs.filter(ts => {
            const tsDate = new Date(ts);
            return tsDate >= todayStart && tsDate < todayEnd;
        });
        
        console.log(`ข้อมูลวันนี้: ${selectedTs.length} จุดจาก ${sortedTs.length} จุดทั้งหมด`);
    } else {
        const maxLabels = 30;
        const step = Math.max(1, Math.floor(sortedTs.length / maxLabels));
        selectedTs = sortedTs.filter((_, i) => i % step === 0);
    }
    
    console.log(`Timestamps ที่เลือกแสดง: ${selectedTs.length} จุด`);
    
    // สร้าง labels แกน X (เวลา) - ใช้ Date objects สำหรับ time scale
    const labels = selectedTs.map(ts => new Date(ts));

    // สร้าง datasets - แต่ละสถานีเป็น dataset (เหมือนเดิม)
    const datasets = STATIONS.map((station, idx) => {
        const byTs = new Map();
        
        if (isRealtime) {
            const stationData = floatingRealtimeData[station.eui] || [];
            console.log(`สร้าง dataset สำหรับ ${station.name}: ${stationData.length} จุดข้อมูล`);
            stationData.forEach(d => {
                const value = fieldMap.get(d.originalData);
                if (value !== null && value !== undefined) {
                    byTs.set(d.timestamp, value);
                }
            });
        } else if (historyData) {
            (historyData[station.eui] || []).forEach(r => {
                byTs.set(r.timestamp, fieldMap.get(r));
            });
        }
        
        const dataValues = selectedTs.map(ts => byTs.get(ts) ?? null);
        const validValues = dataValues.filter(v => v !== null && v !== undefined);
        console.log(`${station.name}: ${validValues.length} ค่าที่ถูกต้องจาก ${dataValues.length} ค่า`);
        
        return {
            label: station.name,
            data: dataValues,
            borderColor: `hsl(${idx * 36}, 70%, 50%)`,
            backgroundColor: `hsla(${idx * 36}, 70%, 50%, 0.1)`,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: `hsl(${idx * 36}, 70%, 50%)`,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            borderWidth: 2,
            tension: 0.3,
            spanGaps: true,
            fill: false
        };
    });

    floatingChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            layout: {
                padding: {
                    bottom: 20,
                    top: 10,
                    left: 10,
                    right: 10
                }
            },
            onResize: function(chart, size) {
                // เพิ่มปุ่มเลื่อนลงเมื่อกราฟถูกสร้าง
                addScrollButton(chart);
            },
            plugins: { 
                legend: { 
                    display: true, 
                    position: 'bottom', // เปลี่ยนตำแหน่ง legend ไปด้านล่าง
                    labels: { 
                        usePointStyle: true, 
                        padding: 1, 
                        font: { family: 'Sarabun', size: 6 },
                        boxWidth: 6,
                        boxHeight: 6,
                        generateLabels: function(chart) {
                            const original = Chart.defaults.plugins.legend.labels.generateLabels;
                            const labels = original.call(this, chart);
                            
                            // ปรับปรุงการแสดงผลของ labels
                            labels.forEach(label => {
                                // จำกัดความยาวของชื่อสถานี
                                if (label.text.length > 15) {
                                    label.text = label.text.substring(0, 12) + '...';
                                }
                            });
                            
                            return labels;
                        }
                    } 
                },
                tooltip: { 
                    backgroundColor: 'rgba(0,0,0,0.8)', 
                    titleColor: '#fff', 
                    bodyColor: '#fff', 
                    cornerRadius: 4, 
                    displayColors: true,
                    titleFont: {
                        size: 10,
                        family: 'Sarabun'
                    },
                    bodyFont: {
                        size: 9,
                        family: 'Sarabun'
                    },
                    padding: 6,
                    caretSize: 4,
                    callbacks: {
                        title: function(context) {
                            // ใช้ index เพื่อหา timestamp จาก labels array
                            const index = context[0].dataIndex;
                            const timestamp = labels[index];
                            if (timestamp) {
                                const date = new Date(timestamp);
                                return date.toLocaleDateString('th-TH', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                            }
                            return 'Invalid Date';
                        },
                        label: function(context) {
                            const value = context.parsed.y;
                            if (value === null) return context.dataset.label + ': ไม่มีข้อมูล';
                            const unit = fieldMap.label.includes('°C') ? '°C' : 
                                       fieldMap.label.includes('%') ? '%' : 
                                       fieldMap.label.includes('m/s') ? 'm/s' :
                                       fieldMap.label.includes('mm') ? 'mm' :
                                       fieldMap.label.includes('dB') ? 'dB' : 'µg/m³';
                            const decimalPlaces = 1;
                            return `${context.dataset.label}: ${Number(value).toFixed(decimalPlaces)} ${unit}`;
                        }
                    }
                }
            },
            scales: {
                x: { 
                    type: 'time', 
                    time: {
                        parser: 'YYYY-MM-DD HH:mm:ss',
                        displayFormats: {
                            hour: 'DD/MM HH:mm',
                            day: 'DD/MM HH:mm',
                            month: 'DD/MM HH:mm'
                        }
                    },
                    title: { 
                        display: true, 
                        text: isRealtime ? 'เวลา (ข้อมูลตลอดวัน)' : 'วันที่และเวลา', 
                        font: { family: 'Sarabun', size: 10 },
                        padding: 10
                    }, 
                    ticks: { 
                        font: { family: 'Sarabun', size: 7 }, 
                        maxRotation: 45,
                        maxTicksLimit: 8, // เพิ่มจำนวน tick ในแกน X
                        minRotation: 0,
                        padding: 8,
                        source: 'auto',
                        callback: function(value, index, ticks) {
                            const date = new Date(value);
                            return date.toLocaleDateString('th-TH', {
                                day: '2-digit',
                                month: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }
                    } 
                },
                y: { 
                    type: 'category',
                    labels: STATIONS.map(st => st.name),
                    title: { 
                        display: true, 
                        text: fieldMap.label, 
                        font: { family: 'Sarabun', size: 8 } 
                    },
                    ticks: {
                        font: { family: 'Sarabun', size: 6 },
                        maxTicksLimit: 10, // แสดงครบทั้ง 10 สถานี
                        callback: function(value, index, ticks) {
                            // จำกัดความยาวของชื่อสถานี
                            const label = STATIONS[index]?.name || value;
                            if (label && label.length > 20) {
                                return label.substring(0, 17) + '...';
                            }
                            return label || '';
                        }
                    }
                }
            },
            animation: { duration: 800, easing: 'easeInOutQuart' }
        }
    });
    
    // เพิ่มปุ่มเลื่อนลงหลังจากสร้างกราฟ
    setTimeout(() => {
        addScrollButton(floatingChartInstance);
    }, 500);
}
        // ฟังก์ชันดึงข้อมูลย้อนหลังสำหรับทุกสถานี
        async function fetchAllStationsHistory(startDate, endDate) {
            const historyData = {};
            const promises = [];

            STATIONS.forEach(station => {
                const promise = (async () => {
                    try {
                        const url = getHistoryEndpoint(station, startDate, endDate);
                        const response = await fetch(url);
                        
                        if (!response.ok) {
                            console.warn(`ไม่สามารถดึงข้อมูลสถานี ${station.name}: HTTP ${response.status}`);
                            return { eui: station.eui, data: [] };
                        }

                        const data = await response.json();
                        const normalizedData = data.map(d => {
                            return station.source_table === 'weather_station1' ? normalizeW1(d) : normalizeW2(d);
                        }).filter(d => d && d.timestamp);

                        return { eui: station.eui, data: normalizedData };
                        
                    } catch (error) {
                        console.warn(`ข้อผิดพลาดในการดึงข้อมูลสถานี ${station.name}:`, error);
                        return { eui: station.eui, data: [] };
                    }
                })();
                promises.push(promise);
            });

            const results = await Promise.all(promises);
            
            // จัดระเบียบข้อมูลตาม EUI
            results.forEach(result => {
                historyData[result.eui] = result.data;
            });

            return historyData;
        }

        // เพิ่มฟังก์ชันบังคับตำแหน่งกราฟ
        function forceChartPosition() {
            const container = document.getElementById('floating-line-chart-container');
            if (container) {
                container.style.position = 'absolute';
                container.style.top = '20px';
                container.style.left = '20px';
                container.style.right = '20px';
                container.style.transform = 'none';
                container.style.margin = '0';
                container.style.width = 'auto';
            }
        }

        // ฟังก์ชันแสดง/ซ่อน Floating Chart
function showFloatingChart() {
    floatingLineChartContainer.style.display = 'block';
    
    // **แก้ไขตรงนี้ - บังคับตำแหน่งกราฟรวม 10 สถานี**
    floatingLineChartContainer.style.position = 'fixed';
    floatingLineChartContainer.style.top = '100px';
    floatingLineChartContainer.style.left = '50%';
    floatingLineChartContainer.style.transform = 'translateX(-50%)';
    floatingLineChartContainer.style.zIndex = '10000';
    floatingLineChartContainer.style.maxHeight = '80vh';
    floatingLineChartContainer.style.overflow = 'auto';
    
    const dataType = document.getElementById('floating-chart-data-type').value;
    const isRealtime = document.querySelector('input[name="floatingChartMode"]:checked').value === 'realtime';
    createFloatingLineChart(dataType, isRealtime);
    
    // แสดง/ซ่อน history controls
    updateFloatingHistoryControls();
}
        function hideFloatingChart() {
            floatingLineChartContainer.style.display = 'none';
            if (floatingChartInstance) {
                floatingChartInstance.destroy();
                floatingChartInstance = null;
            }
        }

        // ฟังก์ชันอัปเดต history controls
        function updateFloatingHistoryControls() {
            const historyControls = document.getElementById('floating-history-controls');
            const isRealtime = document.querySelector('input[name="floatingChartMode"]:checked').value === 'realtime';
            
            if (isRealtime) {
                historyControls.style.display = 'none';
            } else {
                historyControls.style.display = 'block';
                // ตั้งค่าวันที่เริ่มต้น
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                
                document.getElementById('floating-end-date').value = today.toISOString().split('T')[0];
                document.getElementById('floating-start-date').value = yesterday.toISOString().split('T')[0];
            }
        }

        // Event Listeners สำหรับ Floating Chart
        const floatingChartToggle = document.getElementById('toggle-floating-chart');
        if (floatingChartToggle) {
            floatingChartToggle.addEventListener('change', function() {
                if (this.checked) {
                    showFloatingChart();
                } else {
                    hideFloatingChart();
                }
            });
        }

        const floatingChartDataType = document.getElementById('floating-chart-data-type');
        if (floatingChartDataType) {
            floatingChartDataType.addEventListener('change', function() {
                const floatingChartToggle = document.getElementById('toggle-floating-chart');
                if (floatingChartToggle && floatingChartToggle.checked) {
                    const isRealtime = document.querySelector('input[name="floatingChartMode"]:checked').value === 'realtime';
                    createFloatingLineChart(this.value, isRealtime);
                }
            });
        }

        document.querySelectorAll('input[name="floatingChartMode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                updateFloatingHistoryControls();
                if (document.getElementById('toggle-floating-chart').checked) {
                    const dataType = document.getElementById('floating-chart-data-type').value;
                    const isRealtime = this.value === 'realtime';
                    createFloatingLineChart(dataType, isRealtime);
                }
            });
        });

        // Event listener สำหรับปุ่มดึงข้อมูลย้อนหลัง
        const fetchFloatingHistory = document.getElementById('fetch-floating-history');
        if (fetchFloatingHistory) {
            fetchFloatingHistory.addEventListener('click', async function() {
            const startDate = document.getElementById('floating-start-date').value;
            const endDate = document.getElementById('floating-end-date').value;
            const dataType = document.getElementById('floating-chart-data-type').value;

            if (!startDate || !endDate) {
                alert('กรุณาเลือกวันที่เริ่มต้นและวันที่สิ้นสุด');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('วันที่เริ่มต้นต้องไม่เกินวันที่สิ้นสุด');
                return;
            }

            // แสดง loading
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังโหลด...';
            this.style.fontSize = '11px';

            try {
                const historyData = await fetchAllStationsHistory(startDate, endDate);
                
                // ตรวจสอบว่ามีข้อมูลหรือไม่
                const hasData = Object.values(historyData).some(stationData => stationData.length > 0);
                
                if (!hasData) {
                    alert('ไม่พบข้อมูลในช่วงเวลาที่เลือก');
                } else {
                    createFloatingLineChart(dataType, false, historyData);
                }

            } catch (error) {
                console.error('Error fetching history data:', error);
                alert('เกิดข้อผิดพลาดในการดึงข้อมูล: ' + error.message);
            } finally {
                // ซ่อน loading
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-search"></i> ดึงข้อมูลย้อนหลัง';
                this.style.fontSize = '11px';
            }
            });
        }

        function hideGraph() {
            floatingChartContainer.style.display = 'none';
            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }
            activeEui = null;
        }

       
        // แก้ไขฟังก์ชัน showGraph สำหรับกราฟแต่ละสถานี
async function showGraph(eui) {
    const station = latestStationData.find(s => s.eui === eui);
    if (!station) {
        console.error('ไม่พบข้อมูลสถานี');
        hideGraph();
        return;
    }
    if (chartInstance) chartInstance.destroy();
    
    const contentHTML = `
        <div class="chart-header">
            <div style="font-size: 16px; font-weight: 600; color: #1e3c72;">${station.name}</div>
            <div class="close-button" onclick="hideGraph()">&times;</div>
        </div>
        <div class="chart-controls">
            <div class="chart-mode-select">
                <label><input type="radio" name="chartMode" value="realtime" checked> เรียลไทม์</label>
                <label><input type="radio" name="chartMode" value="history"> ย้อนหลัง</label>
            </div>
            <select id="chartType">
                ${station.source_table === 'weather_station1' ? `
                    <option value="pm">ค่าฝุ่น (PM)</option>
                    <option value="temperature">อุณหภูมิ (°C)</option>
                    <option value="humidity">ความชื้น (%)</option>
                    <option value="wind_speed">ความเร็วลม (m/s)</option>
                    <option value="rain">ปริมาณฝน (mm)</option>
                ` : `
                    <option value="pm25">ค่าฝุ่น PM2.5 (µg/m³)</option>
                    <option value="pm10">ค่าฝุ่น PM10 (µg/m³)</option>
                    <option value="temperature">อุณหภูมิ (°C)</option>
                    <option value="humidity">ความชื้น (%)</option>
                `}
            </select>
            <div id="historyControls" style="display: none;">
                <div class="date-inputs">
                    <input type="date" id="startDate">
                    <input type="date" id="endDate">
                </div>
                <button id="fetchHistory">ดูข้อมูลย้อนหลัง</button>
            </div>
        </div>
        <canvas id="weatherChart"></canvas>
    `;
    
    floatingChartContainer.innerHTML = contentHTML;
    
    // **แก้ไขตรงนี้ - บังคับให้กราฟอยู่ด้านบน**
    floatingChartContainer.style.display = 'block';
    floatingChartContainer.style.position = 'fixed';
    floatingChartContainer.style.top = '100px';
    floatingChartContainer.style.left = '50%';
    floatingChartContainer.style.transform = 'translateX(-50%)';
    floatingChartContainer.style.zIndex = '10000';
    floatingChartContainer.style.maxHeight = '70vh';
    floatingChartContainer.style.overflow = 'auto';
    
    activeEui = eui;
    const chartTypeSelect = document.getElementById('chartType');
    const fetchHistoryBtn = document.getElementById('fetchHistory');
    const historyControls = document.getElementById('historyControls');
    const chartCanvas = document.getElementById('weatherChart');
    window.hideGraph = hideGraph;

    // แก้ไขฟังก์ชัน showRealtime สำหรับแสดงข้อมูลตลอดวัน
    const showRealtime = () => {
        const chartType = chartTypeSelect.value;
        const data = dailyRealtimeData[eui] || [];
        
        console.log(`ข้อมูลเรียลไทม์สำหรับ ${station.name}:`, data.length, 'จุดข้อมูล');
        
        // ถ้าไม่มีข้อมูล ให้แสดงข้อความแจ้งเตือน
        if (data.length === 0) {
            console.log('ไม่มีข้อมูลเรียลไทม์ - กำลังรอข้อมูล...');
            // สร้างกราฟว่างพร้อมข้อความแจ้งเตือน
            createChart(chartCanvas, ['กำลังรอข้อมูล...'], [0], chartType, station.name, true);
            return;
        }
        
        // จำกัดจำนวนจุดข้อมูลที่แสดงเพื่อไม่ให้กราฟแออัด
        const maxDataPoints = 50;
        const step = Math.max(1, Math.floor(data.length / maxDataPoints));
        const displayData = data.filter((_, index) => index % step === 0);
        
        console.log(`แสดงข้อมูล ${displayData.length} จุดจาก ${data.length} จุดข้อมูล`);
        
        const labels = displayData.map(d => {
            const date = new Date(d.timestamp);
            return `${date.toLocaleDateString('th-TH', { 
                day: '2-digit', 
                month: '2-digit' 
            })} ${date.toLocaleTimeString('th-TH', { 
                hour: '2-digit', 
                minute: '2-digit' 
            })}`;
        });
        const values = displayData.map(d => chartFieldMap[chartType].get(d.originalData));
        
        // ตรวจสอบว่ามีข้อมูลที่ถูกต้องหรือไม่
        const validValues = values.filter(v => v !== null && v !== undefined);
        console.log(`ค่าที่ถูกต้อง: ${validValues.length} จาก ${values.length} ค่า`);
        
        createChart(chartCanvas, labels, values, chartType, station.name, true);
    };

    const showHistory = async () => {
        const chartType = chartTypeSelect.value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (!startDate || !endDate) return;
        
        const url = getHistoryEndpoint(station, startDate, endDate);
        
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const data = await response.json();
            
            const historyData = data.map(d => {
                return station.source_table === 'weather_station1' ? normalizeW1(d) : normalizeW2(d);
            }).filter(d => d && chartFieldMap[chartType].get(d) != null);

            if (historyData.length === 0) {
                alert('ไม่พบข้อมูลในช่วงเวลาที่เลือก');
                if (chartInstance) chartInstance.destroy();
                return;
            }
            historyData.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
            
            // จำกัดจำนวนจุดข้อมูลสำหรับ history
            const maxHistoryPoints = 100;
            const step = Math.max(1, Math.floor(historyData.length / maxHistoryPoints));
            const displayData = historyData.filter((_, index) => index % step === 0);
            
            const labels = displayData.map(d => {
                const date = new Date(d.timestamp);
                return `${date.toLocaleDateString()} ${date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`;
            });
            const values = displayData.map(d => chartFieldMap[chartType].get(d));
            createChart(chartCanvas, labels, values, chartType, station.name, false);
        } catch (err) {
            console.error('Failed to fetch history data:', err);
            alert('เกิดข้อผิดพลาดในการดึงข้อมูลย้อนหลัง: ' + err.message);
        }
    };

    document.querySelectorAll('input[name="chartMode"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'realtime') {
                historyControls.style.display = 'none';
                showRealtime();
            } else {
                historyControls.style.display = 'block';
                if (chartInstance) chartInstance.destroy();
            }
        });
    });

    chartTypeSelect.addEventListener('change', () => {
        const chartMode = document.querySelector('input[name="chartMode"]:checked').value;
        if (chartMode === 'realtime') {
            showRealtime();
        } else if (document.getElementById('startDate').value && document.getElementById('endDate').value) {
            showHistory();
        }
    });
    fetchHistoryBtn.addEventListener('click', showHistory);
    showRealtime();
}

// ฟังก์ชันสำหรับเพิ่มแท่งเลื่อนแบบ scrollbar
function addScrollButton(chart) {
    const container = document.getElementById('floating-line-chart-container');
    if (!container) return;
    
    // ตรวจสอบว่าเป็นโหมดย้อนหลังหรือไม่
    const isRealtime = document.querySelector('input[name="floatingChartMode"]:checked').value === 'realtime';
    if (isRealtime) {
        // ลบปุ่มเลื่อนทั้งหมดในโหมดเรียลไทม์
        const existingButtons = container.querySelectorAll('.chart-scroll-button');
        existingButtons.forEach(button => button.remove());
        return;
    }
    
    // ลบแท่งเก่าถ้ามี
    const existingScrollbar = container.querySelector('.chart-scrollbar');
    const existingIndicator = container.querySelector('.chart-scroll-indicator');
    if (existingScrollbar) existingScrollbar.remove();
    if (existingIndicator) existingIndicator.remove();
    
    // สร้างปุ่มเลื่อนขึ้น
    const scrollButtonUp = document.createElement('button');
    scrollButtonUp.className = 'chart-scroll-button chart-scroll-button-up';
    scrollButtonUp.innerHTML = '↑';
    scrollButtonUp.title = 'เลื่อนขึ้น';
    
    // สร้างปุ่มเลื่อนลง
    const scrollButtonDown = document.createElement('button');
    scrollButtonDown.className = 'chart-scroll-button chart-scroll-button-down';
    scrollButtonDown.innerHTML = '↓';
    scrollButtonDown.title = 'เลื่อนลงดูแกน X';
    
    // เพิ่ม event listener สำหรับปุ่มเลื่อนขึ้น
    scrollButtonUp.addEventListener('click', function() {
        const chartContent = container.querySelector('.floating-chart-content');
        if (chartContent) {
            chartContent.scrollBy({
                top: -80,
                behavior: 'smooth'
            });
        }
    });
    
    // เพิ่ม event listener สำหรับปุ่มเลื่อนลง
    scrollButtonDown.addEventListener('click', function() {
        const chartContent = container.querySelector('.floating-chart-content');
        if (chartContent) {
            chartContent.scrollBy({
                top: 80,
                behavior: 'smooth'
            });
        }
    });
    
    // เพิ่มปุ่มลงใน container
    container.appendChild(scrollButtonUp);
    container.appendChild(scrollButtonDown);
    
    // เพิ่ม touch scrolling functionality
    addTouchScrolling(container);
    
    // อัปเดตปุ่มเลื่อนเมื่อเลื่อน
    const chartContent = container.querySelector('.floating-chart-content');
    if (chartContent) {
        const updateScrollButtons = () => {
            const scrollTop = chartContent.scrollTop;
            const scrollHeight = chartContent.scrollHeight;
            const clientHeight = chartContent.clientHeight;
            const maxScroll = scrollHeight - clientHeight;
            
            if (maxScroll > 0) {
                // แสดง/ซ่อนปุ่มตามตำแหน่งการเลื่อน
                const isAtTop = scrollTop <= 10;
                const isAtBottom = scrollTop >= maxScroll - 10;
                
                scrollButtonUp.style.display = isAtTop ? 'none' : 'flex';
                scrollButtonDown.style.display = isAtBottom ? 'none' : 'flex';
            } else {
                scrollButtonUp.style.display = 'none';
                scrollButtonDown.style.display = 'none';
            }
        };
        
        chartContent.addEventListener('scroll', updateScrollButtons);
        updateScrollButtons(); // เรียกใช้ครั้งแรก
    }
}

// ฟังก์ชันสำหรับเพิ่ม touch scrolling
function addTouchScrolling(container) {
    const chartContent = container.querySelector('.floating-chart-content');
    if (!chartContent) return;
    
    let startY = 0;
    let startScrollTop = 0;
    let isScrolling = false;
    
    // Touch start
    chartContent.addEventListener('touchstart', function(e) {
        startY = e.touches[0].clientY;
        startScrollTop = chartContent.scrollTop;
        isScrolling = true;
        e.preventDefault();
    }, { passive: false });
    
    // Touch move
    chartContent.addEventListener('touchmove', function(e) {
        if (!isScrolling) return;
        
        const currentY = e.touches[0].clientY;
        const deltaY = startY - currentY;
        const newScrollTop = startScrollTop + deltaY;
        
        // จำกัดการเลื่อนไม่ให้เกินขอบเขต
        const maxScroll = chartContent.scrollHeight - chartContent.clientHeight;
        const clampedScrollTop = Math.max(0, Math.min(newScrollTop, maxScroll));
        
        chartContent.scrollTop = clampedScrollTop;
        e.preventDefault();
    }, { passive: false });
    
    // Touch end
    chartContent.addEventListener('touchend', function(e) {
        isScrolling = false;
    }, { passive: false });
    
    // Mouse events สำหรับ desktop
    let mouseStartY = 0;
    let mouseStartScrollTop = 0;
    let isMouseScrolling = false;
    
    chartContent.addEventListener('mousedown', function(e) {
        mouseStartY = e.clientY;
        mouseStartScrollTop = chartContent.scrollTop;
        isMouseScrolling = true;
        e.preventDefault();
    });
    
    chartContent.addEventListener('mousemove', function(e) {
        if (!isMouseScrolling) return;
        
        const currentY = e.clientY;
        const deltaY = mouseStartY - currentY;
        const newScrollTop = mouseStartScrollTop + deltaY;
        
        const maxScroll = chartContent.scrollHeight - chartContent.clientHeight;
        const clampedScrollTop = Math.max(0, Math.min(newScrollTop, maxScroll));
        
        chartContent.scrollTop = clampedScrollTop;
        e.preventDefault();
    });
    
    chartContent.addEventListener('mouseup', function(e) {
        isMouseScrolling = false;
    });
    
    chartContent.addEventListener('mouseleave', function(e) {
        isMouseScrolling = false;
    });
}

// ฟังก์ชันสำหรับเพิ่ม scrollbar thumb ที่เลื่อนได้
function addScrollbarThumbScrolling(container) {
    const scrollIndicator = container.querySelector('.chart-scroll-indicator');
    const scrollThumb = container.querySelector('.chart-scroll-thumb');
    const chartContent = container.querySelector('.floating-chart-content');
    
    if (!scrollIndicator || !scrollThumb || !chartContent) return;
    
    let isDragging = false;
    let startY = 0;
    let startThumbTop = 0;
    
    // Touch events สำหรับ scrollbar thumb
    scrollThumb.addEventListener('touchstart', function(e) {
        isDragging = true;
        startY = e.touches[0].clientY;
        startThumbTop = parseInt(scrollThumb.style.top) || 0;
        e.preventDefault();
        e.stopPropagation();
    }, { passive: false });
    
    scrollThumb.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        
        const currentY = e.touches[0].clientY;
        const deltaY = currentY - startY;
        const newThumbTop = startThumbTop + deltaY;
        
        // จำกัดการเลื่อน thumb ไม่ให้เกินขอบเขต
        const maxThumbTop = scrollIndicator.offsetHeight - scrollThumb.offsetHeight;
        const clampedThumbTop = Math.max(0, Math.min(newThumbTop, maxThumbTop));
        
        scrollThumb.style.top = clampedThumbTop + 'px';
        
        // คำนวณตำแหน่งการเลื่อนของเนื้อหา
        const scrollRatio = clampedThumbTop / maxThumbTop;
        const maxScroll = chartContent.scrollHeight - chartContent.clientHeight;
        const newScrollTop = scrollRatio * maxScroll;
        
        chartContent.scrollTop = newScrollTop;
        
        e.preventDefault();
        e.stopPropagation();
    }, { passive: false });
    
    scrollThumb.addEventListener('touchend', function(e) {
        isDragging = false;
        e.preventDefault();
        e.stopPropagation();
    }, { passive: false });
    
    // Mouse events สำหรับ scrollbar thumb
    scrollThumb.addEventListener('mousedown', function(e) {
        isDragging = true;
        startY = e.clientY;
        startThumbTop = parseInt(scrollThumb.style.top) || 0;
        e.preventDefault();
        e.stopPropagation();
    });
    
    scrollThumb.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        const currentY = e.clientY;
        const deltaY = currentY - startY;
        const newThumbTop = startThumbTop + deltaY;
        
        const maxThumbTop = scrollIndicator.offsetHeight - scrollThumb.offsetHeight;
        const clampedThumbTop = Math.max(0, Math.min(newThumbTop, maxThumbTop));
        
        scrollThumb.style.top = clampedThumbTop + 'px';
        
        const scrollRatio = clampedThumbTop / maxThumbTop;
        const maxScroll = chartContent.scrollHeight - chartContent.clientHeight;
        const newScrollTop = scrollRatio * maxScroll;
        
        chartContent.scrollTop = newScrollTop;
        
        e.preventDefault();
        e.stopPropagation();
    });
    
    scrollThumb.addEventListener('mouseup', function(e) {
        isDragging = false;
        e.preventDefault();
        e.stopPropagation();
    });
    
    // เพิ่ม cursor pointer สำหรับ scrollbar thumb
    scrollThumb.style.cursor = 'pointer';
}

        async function fetchJson(url) {
            const res = await fetch(url, { cache: 'no-store' });
            if (!res.ok) throw new Error(url + ' -> HTTP ' + res.status);
            return await res.json();
        }

        function drawMarkers(layerGroup, list, strokeColor, boundsArray) {
    layerGroup.clearLayers();
    list.forEach(info => {
        if (!Number.isFinite(info.lat) || !Number.isFinite(info.lng)) return;
        const colorMetric = (info.pm !== null && info.pm !== undefined) ? info.pm : (info.pm25 !== null && info.pm25 !== undefined) ? info.pm25 : 0;
        
        // สร้าง custom icon แทน circleMarker
        const iconColor = pmColorEnabled ? pmColor(colorMetric) : '#3498db';
        const weatherIcon = L.divIcon({
            html: `
                <div style="
                    width: 28px; 
                    height: 28px; 
                    border-radius: 8px; 
                    background-color: ${iconColor}; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    position: relative;
                ">
                    <i class="fas fa-broadcast-tower" style="
                        color: white; 
                        font-size: 16px;
                    "></i>
                </div>
            `,
            className: 'custom-weather-icon',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
        
        const marker = L.marker([info.lat, info.lng], {
            icon: weatherIcon
        });
        
                
                const popupContent = `
                    <div class="popup-content" style="min-width: 320px; background: white; border-radius: 8px; overflow: hidden;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px; margin: -10px -10px 12px; border-radius: 8px 8px 0 0;">
                            <h4 style="margin: 0; font-size: 16px;">
                                <i class="fas fa-cloud-sun"></i> ${info.name || info.location || 'สถานีตรวจวัด'}
                            </h4>
                            <div style="font-size: 11px; opacity: 0.9; margin-top: 4px;">
                                ประเภท: ${info.source_table === 'weather_station1' ? 'Weather Station 1' : 'Weather Station 2'}
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                            ${info.temperature ? `<div><strong><i class="fas fa-thermometer-half" style="color: #e74c3c;"></i> อุณหภูมิ:</strong><br><span style="font-size: 15px; color: #e74c3c;">${Number(info.temperature).toFixed(1)}°C</span></div>` : '<div><strong><i class="fas fa-thermometer-half" style="color: #ccc;"></i> อุณหภูมิ:</strong><br><span style="font-size: 15px; color: #999;">ไม่มีข้อมูล</span></div>'}
                            ${info.humidity ? `<div><strong><i class="fas fa-tint" style="color: #3498db;"></i> ความชื้น:</strong><br><span style="font-size: 15px; color: #3498db;">${Number(info.humidity).toFixed(1)}%</span></div>` : '<div><strong><i class="fas fa-tint" style="color: #ccc;"></i> ความชื้น:</strong><br><span style="font-size: 15px; color: #999;">ไม่มีข้อมูล</span></div>'}
                            ${(info.pm !== null && info.pm !== undefined) || (info.pm25 !== null && info.pm25 !== undefined) ? `<div><strong><i class="fas fa-smog" style="color: #e67e22;"></i> ค่าฝุ่น:</strong><br><span style="font-size: 15px; color: ${pmColor(colorMetric)};">${Number(info.pm || info.pm25 || 0).toFixed(1)} µg/m³</span></div>` : '<div><strong><i class="fas fa-smog" style="color: #ccc;"></i> ค่าฝุ่น:</strong><br><span style="font-size: 15px; color: #999;">ไม่มีข้อมูล</span></div>'}
                            ${info.wind_speed ? `<div><strong><i class="fas fa-wind" style="color: #16a085;"></i> ความเร็วลม:</strong><br><span style="font-size: 15px; color: #16a085;">${Number(info.wind_speed).toFixed(1)} m/s</span></div>` : '<div><strong><i class="fas fa-wind" style="color: #ccc;"></i> ความเร็วลม:</strong><br><span style="font-size: 15px; color: #999;">ไม่มีข้อมูล</span></div>'}
                            ${info.rain ? `<div><strong><i class="fas fa-cloud-rain" style="color: #1abc9c;"></i> ฝน:</strong><br><span style="font-size: 15px; color: #1abc9c;">${Number(info.rain).toFixed(1)} mm</span></div>` : '<div><strong><i class="fas fa-cloud-rain" style="color: #ccc;"></i> ฝน:</strong><br><span style="font-size: 15px; color: #999;">ไม่มีข้อมูล</span></div>'}
                        </div>
                        <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; margin-bottom: 12px;">
                            <div style="font-size: 11px; color: #6c757d; margin-bottom: 4px;">
                                <strong><i class="fas fa-clock"></i> อัปเดต:</strong> ${info.timestamp ? new Date(info.timestamp).toLocaleString('th-TH') : 'ไม่ทราบ'}
                                <br><small style="color: #999;">🔄 </small>
                            </div>
                            <div style="font-size: 11px; color: #6c757d;">
                                <strong><i class="fas fa-map-marker-alt"></i> พิกัด:</strong> ${info.lat.toFixed(6)}, ${info.lng.toFixed(6)}
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; background: white; padding: 0 10px 10px;">
                            ${(info.pm !== null && info.pm !== undefined) || (info.pm25 !== null && info.pm25 !== undefined) ? `<button onclick="showStationChart('${info.eui}', 'pm')" style="padding: 8px; background: #e67e22; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-family: 'Sarabun', sans-serif; transition: all 0.3s;">
                                <i class="fas fa-smog"></i> กราฟค่าฝุ่น
                            </button>` : ''}
                            ${info.temperature ? `<button onclick="showStationChart('${info.eui}', 'temperature')" style="padding: 8px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-family: 'Sarabun', sans-serif; transition: all 0.3s;">
                                <i class="fas fa-thermometer-half"></i> กราฟอุณหภูมิ
                            </button>` : ''}
                            ${info.humidity ? `<button onclick="showStationChart('${info.eui}', 'humidity')" style="padding: 8px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-family: 'Sarabun', sans-serif; transition: all 0.3s;">
                                <i class="fas fa-tint"></i> กราฟความชื้น
                            </button>` : ''}
                            ${info.wind_speed ? `<button onclick="showStationChart('${info.eui}', 'wind_speed')" style="padding: 8px; background: #16a085; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-family: 'Sarabun', sans-serif; transition: all 0.3s;">
                                <i class="fas fa-wind"></i> กราฟลม
                            </button>` : ''}
                            ${info.rain ? `<button onclick="showStationChart('${info.eui}', 'rain')" style="padding: 8px; background: #1abc9c; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-family: 'Sarabun', sans-serif; transition: all 0.3s;">
                                <i class="fas fa-cloud-rain"></i> กราฟฝน
                            </button>` : ''}
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent, {
                    maxWidth: 320,
                    className: 'custom-popup'
                });
                marker.on('click', () => {
                    map.setView([info.lat, info.lng], 16);
                });
                marker.addTo(layerGroup);
                markers[info.eui] = marker;
                boundsArray.push([info.lat, info.lng]);
            });
            layerGroup.addTo(map);
        }

        // แก้ไขฟังก์ชัน loadAllMarkers สำหรับการจัดเก็บข้อมูลเรียลไทม์
       async function loadAllMarkers() {
    try {
        // ไม่โหลดสถานีเมื่ออยู่ในโหมดนำทาง
        if (isNavigationMode) {
            return;
        }
        
        // ตรวจสอบและรีเซ็ตข้อมูลเมื่อหมดวัน
        checkAndResetDailyData();

        // โหลดข้อมูล weather จาก endpoint เดียว
        const weatherData = await fetchJson(ENDPOINT_WEATHER_LATEST);
        let allData = [];
        
        if (weatherData && Array.isArray(weatherData)) {
            // แยกข้อมูลตาม source_table
            const w1Data = weatherData.filter(d => d.source_table === 'weather_station1');
            const w2Data = weatherData.filter(d => d.source_table === 'weather_station2');
            
            allData = allData.concat(w1Data.map(normalizeW1));
            allData = allData.concat(w2Data.map(normalizeW2));
        } else {
            console.warn('ไม่สามารถโหลดข้อมูลจาก ./weather_2.php ได้');
        }
        
        latestStationData = STATIONS.map(st => {
            const found = allData.find(d => d.eui === st.eui);
            if (found) {
                return {
                    ...st, ...found, timestamp: found?.timestamp ?? null
                };
            } else {
                // แสดงสถานีแม้ไม่มีข้อมูลล่าสุด
                return {
                    ...st, 
                    lat: st.lat, 
                    lng: st.lng, 
                    timestamp: null,
                    temperature: null,
                    humidity: null,
                    pm: null,
                    pm25: null,
                    pm10: null,
                    wind_speed: null,
                    wind_direct: null,
                    rain: null,
                    rainacc: null,
                    rssi: null,
                    snr: null,
                    source_table: st.eui.startsWith('0000fcc23d22') ? 'weather_station1' : 'weather_station2'
                };
            }
        });   

                // อัปเดตข้อมูลเรียลไทม์ - เก็บข้อมูลตลอดวัน
                latestStationData.forEach(station => {
                    if (station.timestamp) {
                        // สำหรับกราฟแต่ละสถานี - เก็บข้อมูลตลอดวัน
                        if (!dailyRealtimeData[station.eui]) dailyRealtimeData[station.eui] = [];
                        
                        const newDataPoint = {
                            timestamp: station.timestamp,
                            originalData: station
                        };
                        
                        // ตรวจสอบว่าข้อมูลนี้มีอยู่แล้วหรือไม่ (ป้องกันข้อมูลซ้ำ)
                        const exists = dailyRealtimeData[station.eui].some(d => d.timestamp === newDataPoint.timestamp);
                        if (!exists) {
                            dailyRealtimeData[station.eui].push(newDataPoint);
                            // เรียงลำดับตาม timestamp
                                                        dailyRealtimeData[station.eui].sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                        }

                        // สำหรับกราฟรวม 10 สถานี - เก็บข้อมูลตลอดวัน
                        if (!floatingRealtimeData[station.eui]) floatingRealtimeData[station.eui] = [];
                        
                        const floatingDataPoint = {
                            timestamp: station.timestamp,
                            originalData: station
                        };
                        
                        // ตรวจสอบว่าข้อมูลนี้มีอยู่แล้วหรือไม่
                        const floatingExists = floatingRealtimeData[station.eui].some(d => d.timestamp === floatingDataPoint.timestamp);
                        if (!floatingExists) {
                            floatingRealtimeData[station.eui].push(floatingDataPoint);
                            // เรียงลำดับตาม timestamp
                            floatingRealtimeData[station.eui].sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                        }
                    }
                });

                let boundsArray = [];
                // แยกข้อมูลตาม source_table
                const w1Data = latestStationData.filter(d => d.source_table === 'weather_station1');
                const w2Data = latestStationData.filter(d => d.source_table === 'weather_station2');
                
                console.log(`📊 สถานีทั้งหมด: ${latestStationData.length} สถานี`);
                console.log(`📊 Weather Station 1: ${w1Data.length} สถานี`);
                console.log(`📊 Weather Station 2: ${w2Data.length} สถานี`);
                
                drawMarkers(layerW1, w1Data, '#3498db', boundsArray);
                drawMarkers(layerW2, w2Data, '#333333', boundsArray);

                // อัปเดตกราฟแต่ละสถานีหากเปิดอยู่
                if (activeEui && floatingChartContainer.style.display !== 'none') {
                    const chartMode = document.querySelector('input[name="chartMode"]:checked').value;
                    if (chartMode === 'realtime') {
                        const chartType = document.getElementById('chartType').value;
                        const data = dailyRealtimeData[activeEui] || [];
                        
                        console.log(`อัปเดตกราฟเรียลไทม์สำหรับ ${activeEui}: ${data.length} จุดข้อมูล`);
                        
                        if (data.length === 0) {
                            console.log('ยังไม่มีข้อมูลเรียลไทม์ - กำลังรอข้อมูล...');
                            return;
                        }
                        
                        // จำกัดจำนวนจุดข้อมูลที่แสดง
                        const maxDataPoints = 50;
                        const step = Math.max(1, Math.floor(data.length / maxDataPoints));
                        const displayData = data.filter((_, index) => index % step === 0);
                        
                        console.log(`แสดงข้อมูล ${displayData.length} จุดจาก ${data.length} จุดข้อมูล`);
                        
                        const labels = displayData.map(d => {
                            const date = new Date(d.timestamp);
                            return `${date.toLocaleDateString('th-TH', { 
                                day: '2-digit', 
                                month: '2-digit' 
                            })} ${date.toLocaleTimeString('th-TH', { 
                                hour: '2-digit', 
                                minute: '2-digit' 
                            })}`;
                        });
                        const values = displayData.map(d => chartFieldMap[chartType].get(d.originalData));
                        
                        if (chartInstance) {
                            chartInstance.data.labels = labels;
                            chartInstance.data.datasets[0].data = values;
                            chartInstance.update('active');
                        }
                    }
                }
                
                // อัปเดตกราฟรวม 10 สถานีหากเปิดอยู่
                if (floatingLineChartContainer.style.display !== 'none') {
                    const floatingChartMode = document.querySelector('input[name="floatingChartMode"]:checked');
                    if (floatingChartMode && floatingChartMode.value === 'realtime') {
                        const dataType = document.getElementById('floatingDataType').value;
                        console.log(`อัปเดตกราฟรวมเรียลไทม์: ${dataType}`);
                        createFloatingLineChart(dataType, true);
                    }
                }
                
                console.log(`📊 โหลดข้อมูลสถานีทั้งหมด: ${latestStationData.length} สถานี`);
                console.log('📊 สถานี weather_station1:', latestStationData.filter(d => d.source_table === 'weather_station1').length);
                console.log('📊 สถานี weather_station2:', latestStationData.filter(d => d.source_table === 'weather_station2').length);
                console.log('⏰ เวลาการอัปเดต:', new Date().toLocaleString('th-TH'));
                
                // แสดงจำนวนข้อมูลเรียลไทม์ที่เก็บไว้
                const totalRealtimePoints = Object.values(dailyRealtimeData).reduce((sum, data) => sum + data.length, 0);
                console.log(`ข้อมูลเรียลไทม์ที่เก็บไว้: ${totalRealtimePoints} จุดข้อมูล`);
                
            } catch (e) {
                console.error('โหลดข้อมูลหมุดบนแผนที่ล้มเหลว:', e);
            }
        }

        // แก้ไขฟังก์ชัน renderStationList เพื่อเพิ่มปุ่มซูม
function renderStationList(stations) {
    const listElement = document.getElementById('station-list');
    listElement.innerHTML = '';
    
    stations.forEach(station => {
        const item = document.createElement('li');
        item.className = 'station-item';
        item.dataset.eui = station.eui;
        
        // สร้างโครงสร้าง HTML ที่มีชื่อสถานีและปุ่ม
        item.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <span style="flex: 1; cursor: pointer;" class="station-name">
                    ${station.name}
                </span>
                <button class="zoom-station-btn" title="ซูมไปที่สถานี" 
                        style="background: transparent; border: none; cursor: pointer; padding: 4px 8px; color: #667eea; font-size: 16px;">
                    <i class="fas fa-search-location"></i>
                </button>
            </div>
        `;
        
        // Event สำหรับคลิกที่ชื่อสถานี
        const nameSpan = item.querySelector('.station-name');
        nameSpan.addEventListener('click', () => {
            selectStation(station.eui);
        });
        
        // Event สำหรับคลิกปุ่มซูม
        const zoomBtn = item.querySelector('.zoom-station-btn');
        zoomBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // ป้องกันการ trigger event ของ parent
            selectStation(station.eui);
        });
        
        listElement.appendChild(item);
    });
}

        // แก้ไขฟังก์ชัน selectStation - ให้ซูมไปที่สถานีแทนการเปิดกราฟ
function selectStation(eui) {
    // ลบ active class จากทุก item
    document.querySelectorAll('.station-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // เพิ่ม active class ให้ item ที่เลือก
    const selectedItem = document.querySelector(`.station-item[data-eui="${eui}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }
    
    // หาสถานีที่เลือก
    const station = latestStationData.find(d => d.eui === eui);
    if (station && station.lat && station.lng) {
        console.log('🎯 Zooming to station:', station.name);
        
        // ซูมไปที่ตำแหน่งสถานี
        map.setView([station.lat, station.lng], 17, {
            animate: true,
            duration: 1.5, // ระยะเวลา animation
            easeLinearity: 0.5
        });
        
        // หา marker ของสถานีและเปิด popup
        setTimeout(() => {
            const marker = markers[eui];
            if (marker) {
                marker.openPopup();
                
                // เพิ่มเอฟเฟกต์กระพริบให้ marker
                const markerElement = marker.getElement();
                if (markerElement) {
                    markerElement.style.animation = 'markerBounce 1s ease-in-out 2';
                }
            } else {
                console.warn('⚠️ Marker not found for EUI:', eui);
            }
        }, 1000); // รอให้ zoom เสร็จก่อน
        
    } else {
        console.error('❌ Station coordinates not found:', eui);
        alert('ไม่พบพิกัดของสถานี');
    }
}
        // สีหมุดตามค่าฝุ่นถูกรวมเข้าไปใน toggle-weather แล้ว
        // ไม่ต้องจัดการ toggle-pm-color แยกอีก

        


        // เมื่อหมวดอื่นถูกเลือก (ที่ไม่ใช่ weather-layers) ให้ปิดสถานีและสีหมุดตามค่าฝุ่นอัตโนมัติ
        function autoDisableWeatherIfNeeded(layerActive) {
            // ปิดสถานีเฉพาะเมื่อเลือกหมวด route-finding (ระบบนำทาง)
            if (layerActive === 'route-finding') {
                isNavigationMode = true;
                if (pmColorEnabled) {
                    document.getElementById('toggle-weather').checked = false;
                    pmColorEnabled = false;
                    hideWeatherLegendFromMap();
                    // ซ่อนสถานีทั้งหมด
                    if (layerW1) map.removeLayer(layerW1);
                    if (layerW2) map.removeLayer(layerW2);
                }
            } else {
                isNavigationMode = false;
            }
        }

        // Category control logic
        document.querySelectorAll('.category-header').forEach(header => {
            header.addEventListener('click', function() {
                const targetId = this.dataset.target;
                autoDisableWeatherIfNeeded(targetId);
                const content = document.getElementById(targetId);
                if (content) {
                    content.classList.toggle('active');
                    this.classList.toggle('active');
                    const icon = this.querySelector('.category-icon');
                    if (icon) {
                        icon.classList.toggle('fa-chevron-down');
                        icon.classList.toggle('fa-chevron-up');
                    }
                }
            });
        });


        // Station list search
        renderStationList(STATIONS);
        const stationSearch = document.getElementById('station-search');
        if (stationSearch) {
            stationSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const filteredStations = STATIONS.filter(station => station.name.toLowerCase().includes(query));
                renderStationList(filteredStations);
            });
        }

        // เริ่มต้นระบบ - โหลดสถานีอัตโนมัติ
        loadAllMarkers(); // โหลดสถานีอัตโนมัติ
        
        // ตั้งค่า interval สำหรับการอัปเดตข้อมูล - ทำงานต่อเนื่อง
        setInterval(() => {
            // อัปเดตข้อมูลต่อเนื่อง
            loadAllMarkers();
            
            // แสดงสถานะการอัปเดต
            const updateTime = new Date().toLocaleString('th-TH');
            console.log('🔄 อัปเดตข้อมูลเรียลไทม์:', updateTime);
            
            // อัปเดตเวลาบนหน้าเว็บ
            const statusElement = document.getElementById('realtime-status');
            if (statusElement) {
                statusElement.textContent = `อัปเดตล่าสุด: ${updateTime}`;
            }
        }, REFRESH_MS);

        // เพิ่มการแสดงสถานะข้อมูลเรียลไทม์
        setInterval(() => {
            if (isPageVisible) {
                const totalPoints = Object.values(dailyRealtimeData).reduce((sum, data) => sum + data.length, 0);
                console.log(`สถานะข้อมูลเรียลไทม์: ${totalPoints} จุดข้อมูล | วันที่: ${currentDate}`);
            }
        }, 300000); // แสดงสถานะทุก 5 นาที

        // --- OLD TRANSPORTATION SYSTEM (Keep existing) ---

        // Bus Stop Data
        let dataY = [
            [window.busStopYellowLocations['01'].lat, window.busStopYellowLocations['01'].lng, "อาคารปฎิบัติการคณะวิศวกรรม"],
            [window.busStopYellowLocations['02'].lat, window.busStopYellowLocations['02'].lng, "ป้ายคณะวิทยาศาสตร์(สาขาเคมี)"],
            [window.busStopYellowLocations['03'].lat, window.busStopYellowLocations['03'].lng, "อาคารเอกาทศรถ"],
            [window.busStopYellowLocations['04'].lat, window.busStopYellowLocations['04'].lng, "QS"],
            [window.busStopYellowLocations['05'].lat, window.busStopYellowLocations['05'].lng, "คณะสาธารณสุขศาสตร์"],
            [window.busStopYellowLocations['06'].lat, window.busStopYellowLocations['06'].lng, "ป้ายอาคารคณะทันตแพทยศาสตร์"],
            [window.busStopYellowLocations['07'].lat, window.busStopYellowLocations['07'].lng, "ประตู6"],
            [window.busStopYellowLocations['08'].lat, window.busStopYellowLocations['08'].lng, "ลานสมเด็จ"],
            [window.busStopYellowLocations['09'].lat, window.busStopYellowLocations['09'].lng, "โดม"],
            [window.busStopYellowLocations['10'].lat, window.busStopYellowLocations['10'].lng, "อาคารปราบไตรจักร2"],
            [window.busStopYellowLocations['11'].lat, window.busStopYellowLocations['11'].lng, "คณะนิติศาสตร์"],
            [window.busStopYellowLocations['12'].lat, window.busStopYellowLocations['12'].lng, "สระว่ายนํ้าสุพรรณกัลยา"],
            [window.busStopYellowLocations['13'].lat, window.busStopYellowLocations['13'].lng, "มน.นิเวศน์"]
        ];
        
        let dataR = [
            [window.busStopRedLocations['01'].lat, window.busStopRedLocations['01'].lng, "ป้ายหน้าคณะวิศวกรรม"],
            [window.busStopRedLocations['02'].lat, window.busStopRedLocations['02'].lng, "คณะเกษตรศาสตร์"],
            [window.busStopRedLocations['03'].lat, window.busStopRedLocations['03'].lng, "CITCOMS"],  
            [window.busStopRedLocations['05'].lat, window.busStopRedLocations['05'].lng, "อาคารปราบไตรจักร1"],
            [window.busStopRedLocations['06'].lat, window.busStopRedLocations['06'].lng, "ป้ายอาคารมิ่งขวัญ"],
            [window.busStopRedLocations['07'].lat, window.busStopRedLocations['07'].lng, "คณะเภสัชศาสตร์"],
            [window.busStopRedLocations['08'].lat, window.busStopRedLocations['08'].lng, "QS"],
            [window.busStopRedLocations['09'].lat, window.busStopRedLocations['09'].lng, "สระเอกกษัตริย์"],
            [window.busStopRedLocations['10'].lat, window.busStopRedLocations['10'].lng, "คณะวิทยาศาสตร์(สาขาคณิตศาสตร์)"],
            [window.busStopRedLocations['11'].lat, window.busStopRedLocations['11'].lng, "ป้ายหน้าคณะวิทยาศาสตร์"],
            [window.busStopRedLocations['12'].lat, window.busStopRedLocations['12'].lng, " มน.นิเวศน์"]
        ];
        
        function addStationMarker(data, iconUrl, stationType, lineColor) {
            let markers = [];
            data.forEach(function(station) {
                var markerIcon = L.icon({ 
                    iconUrl: iconUrl, 
                    iconSize: [32, 32], 
                    iconAnchor: [16, 32], 
                    popupAnchor: [0, -32] 
                });
                
                // Get destination info and full name from the station data
                const stationCode = station[2]; // เช่น "NU YL - 01"
                const stationId = stationCode.split(' - ')[1]; // ดึง "01" จาก "NU YL - 01"
                let destination = '';
                let fullName = stationCode; // Default แสดงรหัสป้าย
                
                console.log(`Processing station: ${stationCode}, ID: ${stationId}, Type: ${stationType}`);
                
                if (stationType === 'สายเหลือง' && window.busStopYellowLocations) {
                    if (window.busStopYellowLocations[stationId]) {
                        destination = window.busStopYellowLocations[stationId].destination || '';
                        fullName = window.busStopYellowLocations[stationId].name || stationCode;
                        console.log(`✓ Found yellow station: ${fullName}`);
                    } else {
                        console.warn(`⚠️ Yellow station ${stationId} not found in busStopYellowLocations`);
                    }
                } else if (stationType === 'สายแดง' && window.busStopRedLocations) {
                    if (window.busStopRedLocations[stationId]) {
                        destination = window.busStopRedLocations[stationId].destination || '';
                        fullName = window.busStopRedLocations[stationId].name || stationCode;
                        console.log(`✓ Found red station: ${fullName}`);
                    } else {
                        console.warn(`⚠️ Red station ${stationId} not found in busStopRedLocations`);
                    }
                }
                
                var marker = L.marker([station[0], station[1]], { icon: markerIcon })
                    .bindPopup(`
                        <div class="popup-content" style="min-width: 300px; max-width: 400px;">
                            <div style="background: linear-gradient(135deg, ${lineColor} 0%, ${lineColor}dd 100%); color: white; padding: 12px; margin: -10px -10px 12px; border-radius: 6px 6px 0 0;">
                                <h4 style="margin: 0; font-size: 16px; font-weight: 600;">
                                    <i class="fas fa-bus"></i> ${fullName}
                            </h4>
                                <div style="font-size: 12px; opacity: 0.9; margin-top: 4px;">
                                    <i class="fas fa-route"></i> ${stationType}${destination ? ` - ${destination}` : ''}
                            </div>
                            </div>
                            <div style="padding: 0 8px;">
                                <div style="font-size: 11px; color: #6c757d; border-top: 1px solid #e9ecef; padding-top: 8px;">
                                <div><strong><i class="fas fa-globe"></i> พิกัด:</strong> ${station[0].toFixed(6)}, ${station[1].toFixed(6)}</div>
                                </div>
                            </div>
                        </div>
                    `)
                    .on('click', () => map.flyTo([station[0], station[1]], 17));
                
                markers.push(marker);
                // ปิดการสร้างตัวหนังสือที่ลอยบนแผนที่สำหรับป้ายรถเมล์
            });
            return L.layerGroup(markers); 
        }

        var yellowMarkers = addStationMarker(dataY, './busstop_y.png', 'สายเหลือง', '#FBBC05');     
        var redMarkers = addStationMarker(dataR, './busstop_r.png', 'สายแดง', '#EA4335');     

// ข้อมูลป้ายรถเมล์ถูกกำหนดไว้แล้วในส่วนต้นของไฟล์
// โหลด dropdown อีกครั้งหลังจากเตรียมข้อมูลป้ายรถ


        // GeoJSON data from roads.php
        var geoJsonData_red = <?php echo $geoJsonData_red; ?>;
        var geoJsonData_yellow = <?php echo $geoJsonData_yellow; ?>;
        var geoJsonData_blue = <?php echo $geoJsonData_blue; ?>;
        
        var geoJsonLayer_red = L.geoJSON(geoJsonData_red, {
            style: function (feature) {
                return { color: "red", weight: 4, opacity: 1 };
            }
        });

        var geoJsonLayer_yellow = L.geoJSON(geoJsonData_yellow, {
            style: function (feature) {
                return { color: "yellow", weight: 4, opacity: 1 };
            }
        });

        var geoJsonLayer_blue = L.geoJSON(geoJsonData_blue, {
            style: function (feature) {
                return { color: "blue", weight: 4, opacity: 1 };
            }
        });

         // Real-time Train Layer - ระบบติดตามรถไฟฟ้าแบบเรียลไทม์
        let trainMarkers = L.layerGroup();
        let currentTrainMarkers = [];

        function getTrainIconByPlate(plateNumber, cog) {
            let carImageUrl = './busb.png'; // สีน้ำเงิน (default)
            
            // รถไฟฟ้าสีเหลือง (Yellow Line)
            if (plateNumber.includes('9)40-0202 พิษณุโลก') || plateNumber.includes('(7)40-0203 พิษณุโลก') || 
                plateNumber.includes('(12)40-0193 พิษณุโลก') || plateNumber.includes('(14)40-0198 พิษณุโลก') || 
                plateNumber.includes('(11)40-0192 พิษณุโลก') || plateNumber.includes('(13)40-0197 พิษณุโลก') || 
                plateNumber.includes('(10)40-0205 พิษณุโลก') || plateNumber.includes('(10)40-0206 พิษณุโลก') || 
                plateNumber.includes('(16)40-0206 พิษณุโลก') || plateNumber.includes('(9)40-0202  พิษณุโลก') || 
                plateNumber.includes('(7)40-0203  พิษณุโลก') || plateNumber.includes('(15)40-0200 พิษณุโลก')) {
                carImageUrl = './busy.png'; // รถสีเหลือง
            } 
            // รถไฟฟ้าสีแดง (Red Line)
            else if (plateNumber.includes('(1)40-0191 พิษณุโลก') || plateNumber.includes('(3)40-0199 พิษณุโลก') || 
                      plateNumber.includes('(4)40-0204 พิษณุโลก') || plateNumber.includes('(5)40-0196 พิษณุโลก') || 
                      plateNumber.includes('(2)40-0195 พิษณุโลก') || plateNumber.includes('(8)40-0201 พิษณุโลก')) {
                carImageUrl = './busr.png'; // รถสีแดง
            }
            // รถไฟฟ้าสีน้ำเงิน (Blue Line - EVT) จะใช้ default carB.png
            
            return L.divIcon({
                html: `<img src="${carImageUrl}" style="width: 32px; height: 32px; transform: rotate(${cog}deg); filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));">`,
                className: 'train-icon',
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
        }
        // ฟังก์ชันโหลดข้อมูลรถไฟฟ้าแบบเรียลไทม์
        function loadTrainData() {
            fetch('./carapi.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Train data received:', data);
                    
                    currentTrainMarkers.forEach(m => trainMarkers.removeLayer(m));
                    currentTrainMarkers = [];

                    if (data && Array.isArray(data)) {
                        data.forEach(car => {
                            const lat = parseFloat(car.Latitude);
                            const lng = parseFloat(car.Longitude);
                            const plate = car.plateNumber;
                            const cog = car.COG || 0;
                            const speed = car.Speed || 0;
                            const timestamp = car.DateTime || 'ไม่ทราบ';

                            if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                                const icon = getTrainIconByPlate(plate, cog);
                                const marker = L.marker([lat, lng], { icon: icon })
                                    .bindPopup(`
                                        <div class="popup-content">
                                            <h4 style="margin: 0 0 10px 0; color: #1e3c72;">
                                                <i class="fas fa-train"></i> รถไฟฟ้า NU
                                            </h4>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                                                <div><strong><i class="fas fa-id-card"></i> ทะเบียนรถไฟฟ้า:</strong><br>${plate}</div>
                                                <div><strong><i class="fas fa-compass"></i> ทิศทางรถไฟฟ้า:</strong><br>${cog}° (หมุนตามทิศทาง)</div>
                                                <div><strong><i class="fas fa-tachometer-alt"></i> ความเร็วรถไฟฟ้า:</strong><br>${speed} km/h</div>
                                                <div><strong><i class="fas fa-signal"></i> สถานะรถไฟฟ้า:</strong><br>รถไฟฟ้าออนไลน์</div>
                                            </div>
                                            <div style="font-size: 12px; color: #6c757d; border-top: 1px solid #e9ecef; padding-top: 8px;">
                                                <div><strong><i class="fas fa-clock"></i> เวลาอัปเดตรถไฟฟ้า:</strong> ${timestamp}</div>
                                                <div><strong><i class="fas fa-globe"></i> พิกัดรถไฟฟ้า:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
                                            </div>
                                        </div>
                                    `);

                                trainMarkers.addLayer(marker);
                                currentTrainMarkers.push(marker);
                            }
                        });
                        
                        console.log(`แสดงรถไฟฟ้า ${currentTrainMarkers.length} ขบวน`);
                    } else {
                        console.log('ไม่มีข้อมูลรถไฟฟ้า หรือข้อมูลไม่ถูกต้อง');
                    }
                })
                .catch(error => {
                    console.error('Error fetching train data:', error);
                });
        }

        loadTrainData();
        setInterval(loadTrainData, 1000);







        // Layer control logic
        const layerToggles = {
            'toggle-yellow-stops': yellowMarkers,
            'toggle-red-stops': redMarkers,
            'toggle-yellow-route': geoJsonLayer_yellow,
            'toggle-red-route': geoJsonLayer_red,
            'toggle-blue-route': geoJsonLayer_blue,
            'toggle-live-vehicles': trainMarkers,
            'toggle-weather': [layerW1, layerW2]
        };
        
        for (const toggleId in layerToggles) {
            const checkbox = document.getElementById(toggleId);
            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    const layers = Array.isArray(layerToggles[toggleId]) ? layerToggles[toggleId] : [layerToggles[toggleId]];
                    if (this.checked) {
                        layers.forEach(layer => map.addLayer(layer));
                        if (toggleId === 'toggle-weather') {
                            // เปิดสถานีและสีหมุดตามค่าฝุ่นพร้อมกัน
                            pmColorEnabled = true;
                            showWeatherLegendOnMap();
                            loadAllMarkers();
                        }
                    } else {
                        layers.forEach(layer => map.removeLayer(layer));
                        if (toggleId === 'toggle-weather') {
                            // ปิดสถานีและสีหมุดตามค่าฝุ่นพร้อมกัน
                            pmColorEnabled = false;
                            hideWeatherLegendFromMap();
                            // ซ่อนสถานีทั้งหมด
                            if (layerW1) map.removeLayer(layerW1);
                            if (layerW2) map.removeLayer(layerW2);
                        }
                    }
                });
                // เปิดการแสดง layer อัตโนมัติสำหรับสถานีตรวจสอบสภาพอากาศ
                if (checkbox.checked && toggleId === 'toggle-weather') {
                    const layers = Array.isArray(layerToggles[toggleId]) ? layerToggles[toggleId] : [layerToggles[toggleId]];
                    layers.forEach(layer => map.addLayer(layer));
                }
            }
        }


        // ==================== ระบบแสดงข้อมูลแต่ละประเภททั้ง 10 สถานี ====================

        // ฟังก์ชันสำหรับสร้าง labels แสดงค่าบนหมุด
        // ฟังก์ชันสำหรับสร้าง labels แสดงค่าบนหมุด (คลิกได้)
function createValueLabel(value, unit, color) {
    // แปลงค่าเป็น number และจัดรูปแบบเป็นทศนิยม 1 ตำแหน่ง
    const numValue = Number(value);
    const formattedValue = numValue.toFixed(1);
    
    return L.divIcon({
        html: `
            <div style="
                background: ${color};
                color: white;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                border: 2px solid white;
                cursor: pointer;
            ">
                ${formattedValue}${unit}
            </div>
        `,
        className: 'value-label-marker',
        iconSize: [60, 24],
        iconAnchor: [30, 12]
    });
}

// ฟังก์ชันสร้างไอคอนลูกศรทิศทางลม
function createWindDirectionIcon(windSpeed, windDirection, color) {
    // แปลงทิศทางลมเป็นองศา (ถ้าจำเป็น)
    let angle = windDirection;
    if (windDirection === null || windDirection === undefined) {
        angle = 0;
    }
    
    // สร้างลูกศร SVG ที่หมุนตามทิศทางลม
    const arrowSvg = `
        <div style="
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: ${color};
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            cursor: pointer;
        ">
            <svg width="20" height="20" viewBox="0 0 20 20" style="transform: rotate(${angle}deg);">
                <path d="M10 2 L10 18 M6 6 L10 2 L14 6" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    `;
    
    return L.divIcon({
        html: arrowSvg,
        className: 'wind-direction-marker',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
}
        

        // Layer สำหรับแสดงค่าต่างๆ
        let pmViewLayer = null;
        let tempViewLayer = null;
        let humidityViewLayer = null;
        let windViewLayer = null;
        let rainViewLayer = null;
        let idwLayer = null;

        // ฟังก์ชันแสดงค่า PM ทุกสถานี
        // ฟังก์ชันแสดงค่า PM ทุกสถานี - เพิ่มปุ่มดูกราฟใน popup
function showPMValues() {
    if (pmViewLayer) {
        map.removeLayer(pmViewLayer);
        pmViewLayer = null;
    }
    
    const markers = [];
    latestStationData.forEach(station => {
        if (station.lat && station.lng) {
            const pmValue = (station.pm !== null && station.pm !== undefined) ? station.pm : (station.pm25 !== null && station.pm25 !== undefined) ? station.pm25 : 0;
            if (pmValue != null) {
                const marker = L.marker([station.lat, station.lng], {
                    icon: createValueLabel(
                        Number(pmValue).toFixed(1),
                        ' µg/m³',
                        pmColor(pmValue)
                    )
                }).bindPopup(`
                    <div style="text-align: center; min-width: 180px;">
                        <strong style="display: block; margin-bottom: 8px;">${station.name}</strong>
                        <div style="font-size: 16px; color: ${pmColor(pmValue)}; margin-bottom: 10px;">
                            ค่าฝุ่น PM2.5: ${Number(pmValue).toFixed(1)} µg/m³
                        </div>
                        <button onclick="showStationChart('${station.eui}', 'pm')" style="
                            width: 100%;
                            padding: 8px;
                            background: #e67e22;
                            color: white;
                            border: none;
                            border-radius: 6px;
                            cursor: pointer;
                            font-family: 'Sarabun', sans-serif;
                            font-size: 12px;
                            transition: all 0.3s;
                        " onmouseover="this.style.background='#d35400'" onmouseout="this.style.background='#e67e22'">
                            <i class="fas fa-chart-line"></i> ดูกราฟค่าฝุ่น
                        </button>
                    </div>
                `);
                markers.push(marker);
            }
        }
    });
    
    pmViewLayer = L.layerGroup(markers);
    map.addLayer(pmViewLayer);
    
    // แสดงคำอธิบายสี PM2.5
    showPMLegend();
}

// ฟังก์ชันแสดงคำอธิบายสี PM2.5
function showPMLegend() {
    // ลบ legend เก่าถ้ามี
    const existingLegend = document.getElementById('pm-legend');
    if (existingLegend) {
        existingLegend.remove();
    }
    
    // สร้าง legend ใหม่บนแผนที่
    const legendEl = document.createElement('div');
    legendEl.id = 'pm-legend';
    legendEl.innerHTML = `
        <div><strong>คำอธิบายสีค่าฝุ่น PM2.5</strong></div>
        <div class="row"><span class="swatch" style="background:#007BFF"></span> ฟ้า - ดีมาก (0-15 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#28A745"></span> เขียว - ดี (15.1-25 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#FFC107"></span> เหลือง - ปานกลาง (25.1-37.5 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#FD7E14"></span> ส้ม - เริ่มมีผลกระทบ (37.6-75 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#DC3545"></span> แดง - อันตราย (>75.0 µg/m³ µg/m³)</div>
       
    `;
    
    // เพิ่ม legend ลงในแผนที่
    if (window.map) {
        const legendControl = L.control({position: 'bottomright'});
        legendControl.onAdd = function(map) {
            // จัดการ margin สำหรับมือถือ
            const updateMargin = () => {
                if (window.innerWidth <= 768) {
                    legendEl.style.marginBottom = '60px';
                    legendEl.style.marginRight = '10px';
                } else {
                    legendEl.style.marginBottom = '20px';
                    legendEl.style.marginRight = '10px';
                }
            };
            
            // เรียกใช้ทันที
            updateMargin();
            
            // เพิ่ม event listener สำหรับ resize
            window.addEventListener('resize', updateMargin);
            
            return legendEl;
        };
        window.map.addControl(legendControl);
    }
}

// ฟังก์ชันซ่อนคำอธิบายสี PM2.5
function hidePMLegend() {
    const pmLegend = document.getElementById('pm-legend');
    if (pmLegend) {
        pmLegend.remove();
    }
}

// ฟังก์ชันแสดงอุณหภูมิทุกสถานี - เพิ่มปุ่มดูกราฟ
function showTemperatureValues() {
    if (tempViewLayer) {
        map.removeLayer(tempViewLayer);
        tempViewLayer = null;
    }
    
    const markers = [];
    latestStationData.forEach(station => {
        if (station.lat && station.lng && station.temperature != null) {
            const temp = Number(station.temperature);
            const color = temp > 35 ? '#e74c3c' : temp > 30 ? '#f39c12' : '#3498db';
            
            const marker = L.marker([station.lat, station.lng], {
                icon: createValueLabel(temp.toFixed(1), '°C', color)
            }).bindPopup(`
                <div style="text-align: center; min-width: 180px;">
                    <strong style="display: block; margin-bottom: 8px;">${station.name}</strong>
                    <div style="font-size: 16px; color: ${color}; margin-bottom: 10px;">
                        อุณหภูมิ: ${temp.toFixed(1)}°C
                    </div>
                    <button onclick="showStationChart('${station.eui}', 'temperature')" style="
                        width: 100%;
                        padding: 8px;
                        background: #e74c3c;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-family: 'Sarabun', sans-serif;
                        font-size: 12px;
                        transition: all 0.3s;
                    " onmouseover="this.style.background='#c0392b'" onmouseout="this.style.background='#e74c3c'">
                        <i class="fas fa-chart-line"></i> ดูกราฟอุณหภูมิ
                    </button>
                </div>
            `);
            markers.push(marker);
        }
    });
    
    tempViewLayer = L.layerGroup(markers);
    map.addLayer(tempViewLayer);
}

// ฟังก์ชันแสดงความชื้นทุกสถานี - แก้ไขให้เพิ่มปุ่มดูกราฟ
function showHumidityValues() {
    if (humidityViewLayer) {
        map.removeLayer(humidityViewLayer);
        humidityViewLayer = null;
    }
    
    const markers = [];
    latestStationData.forEach(station => {
        if (station.lat && station.lng && station.humidity != null) {
            const humidity = Number(station.humidity);
            const color = humidity > 80 ? '#3498db' : humidity > 60 ? '#5dade2' : '#85c1e9';
            
            const marker = L.marker([station.lat, station.lng], {
                icon: createValueLabel(humidity.toFixed(1), '%', color)
            }).bindPopup(`
                <div style="text-align: center; min-width: 180px;">
                    <strong style="display: block; margin-bottom: 8px;">${station.name}</strong>
                    <div style="font-size: 16px; color: ${color}; margin-bottom: 10px;">
                        ความชื้น: ${humidity.toFixed(1)}%
                    </div>
                    <button onclick="showStationChart('${station.eui}', 'humidity')" style="
                        width: 100%;
                        padding: 8px;
                        background: #3498db;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-family: 'Sarabun', sans-serif;
                        font-size: 12px;
                        transition: all 0.3s;
                    " onmouseover="this.style.background='#2980b9'" onmouseout="this.style.background='#3498db'">
                        <i class="fas fa-chart-line"></i> ดูกราฟความชื้น
                    </button>
                </div>
            `);
            markers.push(marker);
        }
    });
    
    humidityViewLayer = L.layerGroup(markers);
    map.addLayer(humidityViewLayer);
}

// ฟังก์ชันแสดงทิศทางลมทุกสถานี - ใช้ลูกศรทิศทางลม
function showWindSpeedValues() {
    if (windViewLayer) {
        map.removeLayer(windViewLayer);
        windViewLayer = null;
    }
    
    const markers = [];
    latestStationData.forEach(station => {
        if (station.lat && station.lng && station.wind_speed != null) {
            const windSpeed = Number(station.wind_speed);
            const windDirection = Number(station.wind_direct) || 0;
            const color = windSpeed > 5 ? '#e74c3c' : windSpeed > 2 ? '#f39c12' : '#16a085';
            
            const marker = L.marker([station.lat, station.lng], {
                icon: createWindDirectionIcon(windSpeed, windDirection, color)
            }).bindPopup(`
                <div style="text-align: center; min-width: 200px;">
                    <strong style="display: block; margin-bottom: 8px;">${station.name}</strong>
                    <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <div style="
                            width: 30px; 
                            height: 30px; 
                            background: ${color}; 
                            border-radius: 50%; 
                            display: flex; 
                            align-items: center; 
                            justify-content: center; 
                            margin-right: 10px;
                            border: 2px solid white;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                        ">
                            <svg width="20" height="20" viewBox="0 0 20 20" style="transform: rotate(${windDirection}deg);">
                                <path d="M10 2 L10 18 M6 6 L10 2 L14 6" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div style="font-size: 16px; color: ${color};">
                            <div><strong>ความเร็วลม:</strong> ${windSpeed.toFixed(1)} m/s</div>
                            <div><strong>ทิศทางลม:</strong> ${windDirection.toFixed(0)}°</div>
                        </div>
                    </div>
                    <button onclick="showStationChart('${station.eui}', 'wind_speed')" style="
                        width: 100%;
                        padding: 8px;
                        background: #16a085;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-family: 'Sarabun', sans-serif;
                        font-size: 12px;
                        transition: all 0.3s;
                    " onmouseover="this.style.background='#138d75'" onmouseout="this.style.background='#16a085'">
                        <i class="fas fa-chart-line"></i> ดูกราฟความเร็วลม
                    </button>
                </div>
            `);
            markers.push(marker);
        }
    });
    
    windViewLayer = L.layerGroup(markers);
    map.addLayer(windViewLayer);
}

// ฟังก์ชันแสดงปริมาณฝนทุกสถานี - เพิ่มปุ่มดูกราฟ
function showRainValues() {
    if (rainViewLayer) {
        map.removeLayer(rainViewLayer);
        rainViewLayer = null;
    }
    
    const markers = [];
    latestStationData.forEach(station => {
        if (station.lat && station.lng && station.rain != null) {
            const rain = Number(station.rain);
            const color = rain > 10 ? '#3498db' : rain > 5 ? '#5dade2' : '#1abc9c';
            
            const marker = L.marker([station.lat, station.lng], {
                icon: createValueLabel(rain.toFixed(1), ' mm', color)
            }).bindPopup(`
                <div style="text-align: center; min-width: 180px;">
                    <strong style="display: block; margin-bottom: 8px;">${station.name}</strong>
                    <div style="font-size: 16px; color: ${color}; margin-bottom: 10px;">
                        ปริมาณฝน: ${rain.toFixed(1)} mm
                    </div>
                    <button onclick="showStationChart('${station.eui}', 'rain')" style="
                        width: 100%;
                        padding: 8px;
                        background: #1abc9c;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-family: 'Sarabun', sans-serif;
                        font-size: 12px;
                        transition: all 0.3s;
                    " onmouseover="this.style.background='#16a085'" onmouseout="this.style.background='#1abc9c'">
                        <i class="fas fa-chart-line"></i> ดูกราฟปริมาณฝน
                    </button>
                </div>
            `);
            markers.push(marker);
        }
    });
    
    rainViewLayer = L.layerGroup(markers);
    map.addLayer(rainViewLayer);
}

// ฟังก์ชันแสดง IDW Interpolation สำหรับ PM2.5
function showIDWInterpolation() {
    if (idwLayer) {
        map.removeLayer(idwLayer);
        idwLayer = null;
    }
    
    // ตรวจสอบว่า turf.js โหลดแล้วหรือไม่
    if (typeof turf === 'undefined') {
        alert('Turf.js library ยังไม่โหลดเสร็จ กรุณารอสักครู่แล้วลองใหม่');
        return;
    }
    
   
    
    // เก็บข้อมูล PM2.5 จากสถานีที่มีข้อมูล
    const pm25Data = [];
    latestStationData.forEach(station => {
        if (station.lat && station.lng && ((station.pm !== null && station.pm !== undefined) || (station.pm25 !== null && station.pm25 !== undefined))) {
            const pmValue = Number(station.pm || station.pm25 || 0);
            if (!isNaN(pmValue)) {
                pm25Data.push({
                    lat: station.lat,
                    lng: station.lng,
                    pm25: pmValue,
                    name: station.name,
                    eui: station.eui
                });
            }
        }
    });
    
    if (pm25Data.length < 2) {
        showIDWStatus('ต้องการข้อมูลจากอย่างน้อย 2 สถานี', 'error');
        alert('ต้องการข้อมูลจากอย่างน้อย 2 สถานีเพื่อทำ IDW Interpolation');
        return;
    }
    
    try {
        // สร้าง FeatureCollection สำหรับ turf.js
        const pointFeatures = pm25Data.map(point => 
            turf.point([point.lng, point.lat], { 
                pm: point.pm25, 
                name: point.name,
                eui: point.eui 
            })
        );
        const featuresFC = turf.featureCollection(pointFeatures);
        
        // สร้าง hexagonal grid
        const hexGrid = buildHexGrid(featuresFC, 0.05); // 0.05 km cell size
        if (!hexGrid) {
            showIDWStatus('ไม่สามารถสร้าง grid', 'error');
            return;
        }
        
        // คำนวณ PM2.5 สำหรับแต่ละ hexagon ใช้ IDW
        for (const cell of hexGrid.features) {
            const center = turf.centerOfMass(cell).geometry.coordinates;
            const pmVal = idwAtCoord(center, featuresFC, 2, 3); // power=2, maxDistance=3km
            cell.properties = cell.properties || {};
            cell.properties.pm = (pmVal !== null && Number.isFinite(pmVal)) ? pmVal : null;
            
            // นับจำนวนสถานีที่มีอิทธิพล
            let contributors = 0;
            for (const s of pointFeatures) {
                const d = turf.distance(turf.point(center), s, {units:'kilometers'});
                if (d <= 3) contributors++;
            }
            cell.properties._contributors = contributors;
        }
        
        // แสดงผล hexagonal grid
        renderHexGrid(hexGrid);
        
        // แสดงสถานีต้นฉบับ
        renderStations(pm25Data);
        
        // แสดง legend
        showIDWLegend();
        
        
    } catch (error) {
        console.error('Error in IDW interpolation:', error);
        showIDWStatus('เกิดข้อผิดพลาด: ' + error.message, 'error');
        alert('เกิดข้อผิดพลาดในการคำนวณ IDW Interpolation: ' + error.message);
    }
}

// ฟังก์ชันสร้าง hexagonal grid
function buildHexGrid(featuresFC, cellSideKm) {
    const bbox = turf.bbox(featuresFC);
    if (!bbox || bbox.length !== 4) return null;
    
    // เพิ่ม padding ให้ bbox
    const padLng = (bbox[2] - bbox[0]) * 0.05 || 0.001;
    const padLat = (bbox[3] - bbox[1]) * 0.05 || 0.001;
    const bboxP = [bbox[0]-padLng, bbox[1]-padLat, bbox[2]+padLng, bbox[3]+padLat];
    
    const hex = turf.hexGrid(bboxP, cellSideKm, {units:'kilometers'});
    return hex;
}

// ฟังก์ชันคำนวณ IDW ที่จุดพิกัด
function idwAtCoord(coord, featuresFC, power=2, maxDistanceKm=0) {
    const pts = featuresFC.features.filter(f => Number.isFinite(f.properties.pm));
    if (!pts.length) return null;
    
    // ตรวจสอบว่ามีจุดที่ตรงกันหรือไม่
    for (const s of pts) {
        const d0 = turf.distance(turf.point(coord), s, {units:'kilometers'});
        if (d0 === 0) return s.properties.pm;
    }
    
    let num = 0, den = 0;
    for (const s of pts) {
        const d = turf.distance(turf.point(coord), s, {units:'kilometers'});
        if (maxDistanceKm && d > maxDistanceKm) continue;
        
        // หลีกเลี่ยงการหารด้วยศูนย์
        const w = 1 / Math.pow(Math.max(d, 1e-6), power);
        num += w * s.properties.pm;
        den += w;
    }
    
    if (den === 0) return null;
    return num / den;
}

// ฟังก์ชันแสดงผล hexagonal grid
function renderHexGrid(hexFC) {
    if (idwLayer) {
        map.removeLayer(idwLayer);
    }
    
    idwLayer = L.layerGroup();
    
    if (!hexFC || !hexFC.features || hexFC.features.length === 0) return;
    
    hexFC.features.forEach(feature => {
        const pm = feature.properties.pm;
        if (pm !== null && !isNaN(pm)) {
            const color = getIDWColor(pm);
            
            const hexPolygon = L.polygon(
                feature.geometry.coordinates[0].map(coord => [coord[1], coord[0]]),
                {
                    fillColor: color,
                    color: '#555',
                    weight: 0.3,
                    fillOpacity: 0.75,
                    className: 'idw-hexagon'
                }
            );
            
            const cnt = feature.properties._contributors || 0;
            const txt = `<b>PM2.5: </b>${Math.round(pm)} µg/m³ ${cnt}`;
            hexPolygon.bindPopup(txt);
            
            idwLayer.addLayer(hexPolygon);
        }
    });
    
    map.addLayer(idwLayer);
}

// ฟังก์ชันแสดงสถานีต้นฉบับ
function renderStations(stations) {
    stations.forEach(station => {
        const marker = L.circleMarker([station.lat, station.lng], {
            radius: 6,
            fillColor: getIDWColor(station.pm25),
            color: '#222',
            weight: 1,
            fillOpacity: 0.95,
            className: 'idw-station-marker'
        }).bindPopup(`
            <b>${station.name}</b><br>
            PM2.5: ${Math.round(station.pm25)} µg/m³<br>
            lat: ${station.lat.toFixed(5)} lng: ${station.lng.toFixed(5)}
        `);
        
        idwLayer.addLayer(marker);
    });
}

// ฟังก์ชันกำหนดสีตามค่าฝุ่น PM2.5 (ตามมาตรฐานไทย)
function getIDWColor(v) {
    if (v === null || isNaN(v)) return '#999999';
    
    if (v > 75.0) return '#ff0000'; // แดง
    if (v > 37.5) return '#ff9900'; // ส้ม
    if (v > 25.0) return '#ffff00'; // เหลือง
    if (v > 15.0) return '#00b050'; // เขียว
    return '#00b0f0'; // ฟ้า
}

// ฟังก์ชันแสดงสถานะ IDW
function showIDWStatus(message, type) {
    let statusEl = document.getElementById('idw-status');
    if (!statusEl) {
        statusEl = document.createElement('div');
        statusEl.id = 'idw-status';
        document.body.appendChild(statusEl);
    }
    
    statusEl.innerHTML = `
        <span class="status-indicator ${type}"></span>
        ${message}
    `;
    statusEl.className = `show`;
}

// ฟังก์ชันแสดง legend บนแผนที่
function showIDWLegend() {
    // ลบ legend เก่าถ้ามี
    const existingLegend = document.getElementById('idw-legend');
    if (existingLegend) {
        existingLegend.remove();
    }
    
    // สร้าง legend ใหม่บนแผนที่
    const legendEl = document.createElement('div');
    legendEl.id = 'idw-legend';
    legendEl.innerHTML = `
        <div class="legend-title">PM2.5 Hexagonal Grid (µg/m³)</div>
        <div class="legend-scale">
            <div style="background:#00b0f0"></div>
            <div style="background:#00b050"></div>
            <div style="background:#ffff00"></div>
            <div style="background:#ff9900"></div>
            <div style="background:#ff0000"></div>
        </div>
        <div class="legend-labels">
            <span>0</span><span>15.1</span><span>25.1</span><span>37.6</span><span>75.1+</span>
        </div>
        <div class="legend-info">
            ค่าประมาณจาก IDW Interpolation
        </div>
    `;
    
    // เพิ่ม legend ลงในแผนที่
    if (window.map) {
        const legendControl = L.control({position: 'bottomright'});
        legendControl.onAdd = function(map) {
            return legendEl;
        };
        window.map.addControl(legendControl);
    }
    
    // จัดการตำแหน่ง Weather Legend เมื่อ IDW เปิดอยู่
    const weatherLegend = document.getElementById('weather-legend');
    if (weatherLegend) {
        weatherLegend.style.left = '370px';
        weatherLegend.style.bottom = '20px';
        weatherLegend.style.position = 'fixed';
        weatherLegend.style.zIndex = '2000';
    }
}

// ฟังก์ชันแสดง Weather Legend บนแผนที่
function showWeatherLegendOnMap() {
    // ลบ legend เก่าถ้ามี
    const existingLegend = document.getElementById('weather-legend');
    if (existingLegend) {
        existingLegend.remove();
    }
    
    // สร้าง legend ใหม่บนแผนที่
    const legendEl = document.createElement('div');
    legendEl.id = 'weather-legend';
    legendEl.innerHTML = `
        <div><strong>สีหมุดตามค่าฝุ่น PM2.5</strong></div>
        <div class="row"><span class="swatch" style="background:#007BFF"></span> ดีมาก (0-15.0 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#28A745"></span> ดี (15.1-25 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#FFC107"></span> ปานกลาง (25.1-37.5 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#FD7E14"></span> เริ่มมีผล (37.6-75 µg/m³)</div>
        <div class="row"><span class="swatch" style="background:#DC3545"></span> อันตราย (>75.0 µg/m³)</div>
        
    `;
    
    // เพิ่ม legend ลงในแผนที่
    if (window.map) {
        const legendControl = L.control({position: 'bottomleft'});
        legendControl.onAdd = function(map) {
            // จัดการ margin สำหรับมือถือ
            const updateMargin = () => {
                if (window.innerWidth <= 768) {
                    legendEl.style.marginBottom = '80px';
                } else {
                    legendEl.style.marginBottom = '20px';
                }
            };
            
            // เรียกใช้ทันที
            updateMargin();
            
            // เพิ่ม event listener สำหรับ resize
            window.addEventListener('resize', updateMargin);
            
            return legendEl;
        };
        window.map.addControl(legendControl);
    }
}

// ฟังก์ชันซ่อน Weather Legend จากแผนที่
function hideWeatherLegendFromMap() {
    const weatherLegend = document.getElementById('weather-legend');
    if (weatherLegend) {
        weatherLegend.remove();
    }
}


        // Event Listeners สำหรับสวิตช์แต่ละประเภท
        const pmViewToggle = document.getElementById('toggle-pm-view');
        if (pmViewToggle) {
            pmViewToggle.addEventListener('change', function() {
                if (this.checked) {
                    showPMValues();
                } else {
                    if (pmViewLayer) {
                        map.removeLayer(pmViewLayer);
                        pmViewLayer = null;
                    }
                    // ซ่อนคำอธิบายสี PM2.5
                    hidePMLegend();
                }
            });
        }

        const tempViewToggle = document.getElementById('toggle-temp-view');
        if (tempViewToggle) {
            tempViewToggle.addEventListener('change', function() {
                if (this.checked) {
                    showTemperatureValues();
                } else {
                    if (tempViewLayer) {
                        map.removeLayer(tempViewLayer);
                        tempViewLayer = null;
                    }
                }
            });
        }

        const humidityViewToggle = document.getElementById('toggle-humidity-view');
        if (humidityViewToggle) {
            humidityViewToggle.addEventListener('change', function() {
                if (this.checked) {
                    showHumidityValues();
                } else {
                    if (humidityViewLayer) {
                        map.removeLayer(humidityViewLayer);
                        humidityViewLayer = null;
                    }
                }
            });
        }

        const windViewToggle = document.getElementById('toggle-wind-view');
        if (windViewToggle) {
            windViewToggle.addEventListener('change', function() {
                if (this.checked) {
                    showWindSpeedValues();
                } else {
                    if (windViewLayer) {
                        map.removeLayer(windViewLayer);
                        windViewLayer = null;
                    }
                }
            });
        }

        const rainViewToggle = document.getElementById('toggle-rain-view');
        if (rainViewToggle) {
            rainViewToggle.addEventListener('change', function() {
                if (this.checked) {
                    showRainValues();
                } else {
                    if (rainViewLayer) {
                        map.removeLayer(rainViewLayer);
                        rainViewLayer = null;
                    }
                }
            });
        }

        // IDW Interpolation Event Listener
        const idwToggle = document.getElementById('toggle-idw');
        if (idwToggle) {
            idwToggle.addEventListener('change', function() {
            if (this.checked) {
                // ปิดสถานีและสีหมุดตามค่าฝุ่นเมื่อเปิดแผนที่ PM2.5 (IDW)
                if (pmColorEnabled) {
                    document.getElementById('toggle-weather').checked = false;
                    pmColorEnabled = false;
                    hideWeatherLegendFromMap();
                    // ซ่อนสถานีทั้งหมด
                    if (layerW1) map.removeLayer(layerW1);
                    if (layerW2) map.removeLayer(layerW2);
                    loadAllMarkers();
                }
                showIDWInterpolation();
            } else {
                if (idwLayer) {
                    map.removeLayer(idwLayer);
                    idwLayer = null;
                }
                // ซ่อน IDW legend
                const idwLegend = document.getElementById('idw-legend');
                if (idwLegend) {
                    idwLegend.remove();
                }
                // คืนตำแหน่ง Weather Legend
                resetWeatherLegendPosition();
            }
            });
        }


        // อัปเดตค่าทุกครั้งที่มีข้อมูลใหม่ (เชื่อมกับ wrapper ปัจจุบันของ loadAllMarkers)
        const originalLoadAllMarkersForValues = loadAllMarkers;
        loadAllMarkers = async function() {
            await originalLoadAllMarkersForValues();
            
            // อัปเดตค่าที่แสดงอยู่ตามสวิตช์
            const pmViewToggle = document.getElementById('toggle-pm-view');
            const tempViewToggle = document.getElementById('toggle-temp-view');
            const humidityViewToggle = document.getElementById('toggle-humidity-view');
            const windViewToggle = document.getElementById('toggle-wind-view');
            const rainViewToggle = document.getElementById('toggle-rain-view');
            
            if (pmViewToggle && pmViewToggle.checked) showPMValues();
            if (tempViewToggle && tempViewToggle.checked) showTemperatureValues();
            if (humidityViewToggle && humidityViewToggle.checked) showHumidityValues();
            if (windViewToggle && windViewToggle.checked) showWindSpeedValues();
            if (rainViewToggle && rainViewToggle.checked) showRainValues();
        };

        // Search functionality for layers
        const searchInput = document.querySelector('.search-bar input');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const layerItems = document.querySelectorAll('.layer-item');
            layerItems.forEach(item => {
                const titleTh = item.querySelector('.layer-title-th').textContent.toLowerCase();
                const titleEn = item.querySelector('.layer-title-en').textContent.toLowerCase();
                if (titleTh.includes(searchTerm) || titleEn.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = searchTerm === '' ? 'flex' : 'none';
                }
            });
        });

        // Add controls
        L.control.scale().addTo(map);

        // Fullscreen button
        const fullscreenBtn = L.Control.extend({
            onAdd: function(map) {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                container.style.backgroundColor = 'white';
                container.style.backgroundImage = "url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBkPSJNNyAxNEg1djVoNXYtMkg3di0zem0tMi00aDJWN2gzVjVINXY1em0xMiA3aC0zdjJoNXYtNWgtMnYzek0xNCA1djJoM3YzaDJWNWgtNXoiLz48L3N2Zz4=')";
                container.style.backgroundSize = '16px 16px';
                container.style.backgroundPosition = 'center';
                container.style.backgroundRepeat = 'no-repeat';
                container.style.width = '30px';
                container.style.height = '30px';
                container.style.cursor = 'pointer';
                container.title = 'เต็มหน้าจอ';
                container.onclick = function() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                };
                return container;
            }
        });
        fullscreenBtn().addTo(map);

        // Make functions global
        window.selectStation = selectStation;
        window.hideFloatingChart = hideFloatingChart;

    </script>
    
    
    <!-- เพิ่ม HTML elements สำหรับแสดงสถานะ -->
    <div class="realtime-status" id="realtimeStatus" style="display: none;">
        <span class="realtime-indicator"></span>
        <span id="statusText">ระบบออนไลน์</span>
    </div>
    
    <div class="data-storage-info" id="dataStorageInfo">
        <h5><i class="fas fa-database"></i> ข้อมูลที่เก็บไว้</h5>
        <div id="storageDetails"></div>
    </div>
    
    <div class="reset-notification" id="resetNotification">
        <i class="fas fa-refresh"></i> รีเซ็ตข้อมูลเรียลไทม์สำหรับวันใหม่แล้ว
    </div>

    <script>
        // เพิ่มฟังก์ชันสำหรับจัดการสถานะและการแสดงผล
        
        // ฟังก์ชันแสดง/ซ่อนข้อมูลสถานะ
        function toggleDataStorageInfo() {
            const info = document.getElementById('dataStorageInfo');
            const btn = document.getElementById('statusToggleBtn');
            
            if (info.classList.contains('show')) {
                info.classList.remove('show');
                btn.innerHTML = '<i class="fas fa-info-circle"></i> สถานะข้อมูล';
            } else {
                updateDataStorageDisplay();
                info.classList.add('show');
                btn.innerHTML = '<i class="fas fa-times"></i> ปิด';
            }
        }

        // ฟังก์ชันอัปเดตการแสดงข้อมูลที่เก็บไว้
        function updateDataStorageDisplay() {
            const details = document.getElementById('storageDetails');
            let html = '';
            
            STATIONS.forEach(station => {
                const count = (dailyRealtimeData[station.eui] || []).length;
                const shortName = station.name.length > 20 ? station.name.substring(0, 20) + '...' : station.name;
                html += `<div class="storage-item">
                    <span>${shortName}</span>
                    <span>${count} จุด</span>
                </div>`;
            });
            
            const totalPoints = Object.values(dailyRealtimeData).reduce((sum, data) => sum + data.length, 0);
            html += `<div class="storage-item" style="font-weight: bold; border-top: 2px solid #1e3c72; margin-top: 8px; padding-top: 8px;">
                <span>รวมทั้งหมด</span>
                <span>${totalPoints} จุด</span>
            </div>`;
            
            details.innerHTML = html;
        }

        // ฟังก์ชันอัปเดตสถานะการเชื่อมต่อ
        function updateConnectionStatus(status, message) {
            const statusElement = document.getElementById('connectionStatus');
            const realtimeStatus = document.getElementById('realtimeStatus');

            // ถ้าไม่มี element แสดงสถานะ ให้ข้ามเพื่อไม่ให้ error และ log ไว้
            if (!statusElement) {
                console.debug('[updateConnectionStatus]', status, message || '');
                if (realtimeStatus) {
                    if (status === 'online') realtimeStatus.classList.add('active');
                    if (status === 'offline') realtimeStatus.classList.remove('active');
                }
                return;
            }

            statusElement.className = `connection-status ${status}`;
            
            switch(status) {
                case 'online':
                    statusElement.innerHTML = '<i class="fas fa-wifi"></i> เชื่อมต่อแล้ว';
                    if (realtimeStatus) realtimeStatus.classList.add('active');
                    const st = document.getElementById('statusText');
                    if (st) st.textContent = message || 'ระบบทำงานปกติ';
                    break;
                case 'offline':
                    statusElement.innerHTML = '<i class="fas fa-wifi-slash"></i> ขาดการเชื่อมต่อ';
                    if (realtimeStatus) realtimeStatus.classList.remove('active');
                    break;
                case 'loading':
                    statusElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังโหลด';
                    const st2 = document.getElementById('statusText');
                    if (st2) st2.textContent = 'กำลังดึงข้อมูล...';
                    break;
            }
        }

        // ฟังก์ชันแสดงการแจ้งเตือนการรีเซ็ต
        function showResetNotification() {
            const notification = document.getElementById('resetNotification');
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // แก้ไขฟังก์ชัน checkAndResetDailyData เพื่อแสดงการแจ้งเตือน
        function checkAndResetDailyData() {
            const today = new Date().toDateString();
            if (currentDate !== today) {
                console.log('วันใหม่ - รีเซ็ตข้อมูลเรียลไทม์');
                
                // แสดงการแจ้งเตือนการรีเซ็ต
                if (isPageVisible) {
                    showResetNotification();
                }
                
                // รีเซ็ตข้อมูลทั้งหมด
                Object.keys(dailyRealtimeData).forEach(eui => {
                    dailyRealtimeData[eui] = [];
                });
                Object.keys(floatingRealtimeData).forEach(eui => {
                    floatingRealtimeData[eui] = [];
                });
                currentDate = today;
                
                // อัปเดตการแสดงผล
                if (document.getElementById('dataStorageInfo').classList.contains('show')) {
                    updateDataStorageDisplay();
                }
            }
        }

        // แก้ไขฟังก์ชัน loadAllMarkers เพื่อจัดการสถานะการเชื่อมต่อ
        const originalLoadAllMarkers = loadAllMarkers;
        loadAllMarkers = async function() {
            try {
                updateConnectionStatus('loading');
                await originalLoadAllMarkers();
                
                const totalPoints = Object.values(dailyRealtimeData).reduce((sum, data) => sum + data.length, 0);
                updateConnectionStatus('online', `อัปเดตข้อมูล ${latestStationData.length} สถานี (${totalPoints} จุดข้อมูล)`);
                
                                // อัปเดตการแสดงข้อมูลสถานะหากเปิดอยู่
                if (document.getElementById('dataStorageInfo').classList.contains('show')) {
                    updateDataStorageDisplay();
                }
                
            } catch (error) {
                console.error('เกิดข้อผิดพลาดในการโหลดข้อมูล:', error);
                updateConnectionStatus('offline', 'ไม่สามารถเชื่อมต่อได้');
            }
        };

        // เพิ่มฟังก์ชันสำหรับจัดการข้อมูลในกราฟ
        function addChartDataInfo(chartContainer, dataCount) {
            // ลบข้อมูลเก่าก่อน
            const existingInfo = chartContainer.querySelector('.chart-data-info');
            if (existingInfo) {
                existingInfo.remove();
            }
            
            // เพิ่มข้อมูลใหม่
            const infoDiv = document.createElement('div');
            infoDiv.className = 'chart-data-info';
            infoDiv.textContent = `${dataCount} จุดข้อมูล`;
            chartContainer.style.position = 'relative';
            chartContainer.appendChild(infoDiv);
        }

        // แก้ไขฟังก์ชัน createChart/createFloatingLineChart (ห่อครั้งเดียว) เพื่อเพิ่มข้อมูลจำนวนจุด และแจ้งเตือนโหลดเสร็จ
        if (!window._originalCreateChart) {
            window._originalCreateChart = createChart;
            createChart = function(canvas, labels, values, chartType, chartLabel, isRealtime = false) {
                window._originalCreateChart(canvas, labels, values, chartType, chartLabel, isRealtime);
                // แสดงจำนวนจุดข้อมูลในกราฟย่อย
                const chartContainer = canvas.parentElement;
                addChartDataInfo(chartContainer, values.length);
                // แจ้งเตือนเมื่อกราฟโหลด (ถ้ามีฟังก์ชัน)
                if (typeof announceChartLoaded === 'function') {
                    const label = chartFieldMap[chartType]?.label || chartType;
                    announceChartLoaded(label, chartLabel);
                }
            };
        }

        if (!window._originalCreateFloatingLineChart) {
            window._originalCreateFloatingLineChart = createFloatingLineChart;
            createFloatingLineChart = function(dataType, isRealtime, historyData = null) {
                window._originalCreateFloatingLineChart(dataType, isRealtime, historyData);
                // คำนวณจำนวนข้อมูลทั้งหมด
                let totalDataPoints = 0;
                if (isRealtime) {
                    totalDataPoints = Object.values(floatingRealtimeData).reduce((sum, data) => sum + data.length, 0);
                } else if (historyData) {
                    totalDataPoints = Object.values(historyData).reduce((sum, data) => sum + data.length, 0);
                } else {
                    totalDataPoints = latestStationData.length;
                }
                // แสดงจำนวนจุดข้อมูลในกราฟรวม
                const chartContainer = document.querySelector('.floating-chart-content');
                addChartDataInfo(chartContainer, totalDataPoints);
                // แจ้งเตือนเมื่อกราฟโหลด (ถ้ามีฟังก์ชัน)
                if (typeof announceChartLoaded === 'function') {
                    const label = chartFieldMap[dataType]?.label || dataType;
                    announceChartLoaded(label, 'รวม 10 สถานี');
                }
            };
        }

        // เพิ่มฟังก์ชันสำหรับการจัดการหน่วยความจำ
        function cleanupOldData() {
            const maxDataPointsPerStation = 1000; // จำกัดจำนวนข้อมูลต่อสถานี
            
            Object.keys(dailyRealtimeData).forEach(eui => {
                if (dailyRealtimeData[eui].length > maxDataPointsPerStation) {
                    // เก็บเฉพาะข้อมูลล่าสุด
                    dailyRealtimeData[eui] = dailyRealtimeData[eui].slice(-maxDataPointsPerStation);
                }
            });
            
            Object.keys(floatingRealtimeData).forEach(eui => {
                if (floatingRealtimeData[eui].length > maxDataPointsPerStation) {
                    floatingRealtimeData[eui] = floatingRealtimeData[eui].slice(-maxDataPointsPerStation);
                }
            });
        }

        // เรียกใช้ cleanup ทุก 30 นาที
        setInterval(cleanupOldData, 1800000);

        // เพิ่มฟังก์ชันสำหรับการส่งออกข้อมูล
        function exportRealtimeData(format = 'json') {
            const exportData = {
                date: currentDate,
                timestamp: new Date().toISOString(),
                stations: STATIONS.map(station => ({
                    ...station,
                    realtimeData: dailyRealtimeData[station.eui] || []
                }))
            };
            
            if (format === 'json') {
                const dataStr = JSON.stringify(exportData, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});
                const url = URL.createObjectURL(dataBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `weather_realtime_data_${currentDate.replace(/\s+/g, '_')}.json`;
                link.click();
                URL.revokeObjectURL(url);
            }
        }

        

        // เพิ่มฟังก์ชันสำหรับการแสดงสถิติข้อมูล
        function showDataStatistics() {
            const stats = {
                totalStations: STATIONS.length,
                activeStations: latestStationData.filter(s => s.timestamp).length,
                totalDataPoints: Object.values(dailyRealtimeData).reduce((sum, data) => sum + data.length, 0),
                dataByStation: STATIONS.map(station => ({
                    name: station.name,
                    count: (dailyRealtimeData[station.eui] || []).length,
                    lastUpdate: dailyRealtimeData[station.eui] && dailyRealtimeData[station.eui].length > 0 
                        ? dailyRealtimeData[station.eui][dailyRealtimeData[station.eui].length - 1].timestamp 
                        : null
                }))
            };
            
            console.table(stats.dataByStation);
            console.log('สถิติรวม:', {
                'จำนวนสถานีทั้งหมด': stats.totalStations,
                'สถานีที่มีข้อมูล': stats.activeStations,
                'จุดข้อมูลทั้งหมด': stats.totalDataPoints,
                'วันที่': currentDate
            });
        }

        // เพิ่มฟังก์ชันสำหรับการตรวจสอบคุณภาพข้อมูล
        function checkDataQuality() {
            const qualityReport = {
                missingData: [],
                duplicateData: [],
                invalidData: []
            };
            
            STATIONS.forEach(station => {
                const data = dailyRealtimeData[station.eui] || [];
                
                // ตรวจสอบข้อมูลที่ขาดหาย
                if (data.length === 0) {
                    qualityReport.missingData.push(station.name);
                }
                
                // ตรวจสอบข้อมูลซ้ำ
                const timestamps = data.map(d => d.timestamp);
                const uniqueTimestamps = [...new Set(timestamps)];
                if (timestamps.length !== uniqueTimestamps.length) {
                    qualityReport.duplicateData.push(station.name);
                }
                
                // ตรวจสอบข้อมูลที่ไม่ถูกต้อง
                const invalidCount = data.filter(d => {
                    const temp = d.originalData.temperature;
                    const humidity = d.originalData.humidity;
                    const pm = d.originalData.pm || d.originalData.pm25;
                    
                    return (temp && (temp < -50 || temp > 70)) ||
                           (humidity && (humidity < 0 || humidity > 100)) ||
                           (pm && (pm < 0 || pm > 1000));
                }).length;
                
                if (invalidCount > 0) {
                    qualityReport.invalidData.push({
                        station: station.name,
                        invalidCount: invalidCount
                    });
                }
            });
            
            console.log('รายงานคุณภาพข้อมูล:', qualityReport);
            return qualityReport;
        }

        // เพิ่มคำสั่งในคอนโซลสำหรับการจัดการข้อมูล
        window.weatherSystem = {
            exportData: exportRealtimeData,
            showStats: showDataStatistics,
            checkQuality: checkDataQuality,
            clearData: () => {
                if (confirm('คุณต้องการล้างข้อมูลเรียลไทม์ทั้งหมดหรือไม่?')) {
                    Object.keys(dailyRealtimeData).forEach(eui => {
                        dailyRealtimeData[eui] = [];
                    });
                    Object.keys(floatingRealtimeData).forEach(eui => {
                        floatingRealtimeData[eui] = [];
                    });
                    console.log('ล้างข้อมูลเรียลไทม์แล้ว');
                    updateDataStorageDisplay();
                }
            },
            getData: (eui) => {
                if (eui) {
                    return dailyRealtimeData[eui] || [];
                }
                return dailyRealtimeData;
            }
        };

        // เริ่มต้นระบบเมื่อโหลดหน้าเว็บเสร็จ
        document.addEventListener('DOMContentLoaded', function() {
            // เพิ่มปุ่มส่งออกข้อมูล
            addExportButton();
            
            // แสดงสถานะเริ่มต้น
            updateConnectionStatus('loading', 'กำลังเริ่มต้นระบบ...');
            
            // ตั้งค่าการแสดงสถานะ
            setTimeout(() => {
                document.getElementById('realtimeStatus').classList.add('active');
            }, 2000);
            
            console.log('ระบบตรวจสอบสภาพอากาศเรียลไทม์เริ่มทำงานแล้ว');
            console.log('ใช้คำสั่ง weatherSystem ในคอนโซลเพื่อจัดการข้อมูล');
        });

        
        // เพิ่มการจัดการ Notification API
        function requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('อนุญาตการแจ้งเตือนแล้ว');
                    }
                });
            }
        }

        // เรียกใช้การขออนุญาตแจ้งเตือน
        setTimeout(requestNotificationPermission, 5000);

        // ฟังก์ชันแจ้งเตือนเมื่อค่าผิดปกติ
        function checkAbnormalValues() {
            latestStationData.forEach(station => {
                const pm = (station.pm !== null && station.pm !== undefined) ? station.pm : (station.pm25 !== null && station.pm25 !== undefined) ? station.pm25 : 0;
                const temp = station.temperature;
                
                if (pm && pm > 75) { // ค่าฝุ่นสูงมาก
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification(`เตือน: ค่าฝุ่นสูง`, {
                            body: `${station.name}: PM2.5 = ${pm.toFixed(1)} µg/m³`,
                            icon: './weather.png'
                        });
                    }
                }
                
                if (temp && (temp > 40 || temp < 10)) { // อุณหภูมิผิดปกติ
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification(`เตือน: อุณหภูมิผิดปกติ`, {
                            body: `${station.name}: อุณหภูมิ = ${temp.toFixed(1)}°C`,
                            icon: './weather.png'
                        });
                    }
                }
            });
        }

        // ตรวจสอบค่าผิดปกติทุก 5 นาที
        setInterval(checkAbnormalValues, 300000);
        // เพิ่มฟังก์ชันสำหรับ backdrop
function createChartBackdrop() {
    let backdrop = document.getElementById('chartBackdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'chartBackdrop';
        backdrop.className = 'chart-backdrop';
        backdrop.onclick = function() {
            hideGraph();
            hideFloatingChart();
        };
        document.body.appendChild(backdrop);
    }
    return backdrop;
}

// แก้ไขฟังก์ชัน hideGraph
// แก้ไขฟังก์ชัน hideGraph
function hideGraph() {
    const backdrop = document.getElementById('chartBackdrop');
    if (backdrop) {
        backdrop.classList.remove('show');
    }
    
    floatingChartContainer.classList.add('chart-container-exit');
    
    setTimeout(() => {
        floatingChartContainer.style.display = 'none';
        floatingChartContainer.classList.remove('chart-container-exit');
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
        activeEui = null;
    }, 300);
}

// แก้ไขฟังก์ชัน hideFloatingChart
function hideFloatingChart() {
    const backdrop = document.getElementById('chartBackdrop');
    if (backdrop) {
        backdrop.classList.remove('show');
    }
    
    floatingLineChartContainer.classList.add('chart-container-exit');
    
    setTimeout(() => {
        floatingLineChartContainer.style.display = 'none';
        floatingLineChartContainer.classList.remove('chart-container-exit');
        if (floatingChartInstance) {
            floatingChartInstance.destroy();
            floatingChartInstance = null;
        }
    }, 300);
}

// แก้ไขฟังก์ชัน showGraph เพิ่มเติม
async function showGraph(eui) {
    const station = latestStationData.find(s => s.eui === eui);
    if (!station) {
        console.error('ไม่พบข้อมูลสถานี');
        hideGraph();
        return;
    }
    
    // สร้าง backdrop
    const backdrop = createChartBackdrop();
    backdrop.classList.add('show');
    
    if (chartInstance) chartInstance.destroy();
    
    const contentHTML = `
        <div class="chart-header">
            <div style="font-size: 16px; font-weight: 600; color: #1e3c72;">${station.name}</div>
            <div class="close-button" onclick="hideGraph()">&times;</div>
        </div>
        <div class="chart-controls">
            <div class="chart-mode-select">
                <label><input type="radio" name="chartMode" value="realtime" checked> เรียลไทม์</label>
                <label><input type="radio" name="chartMode" value="history"> ย้อนหลัง</label>
            </div>
            <select id="chartType">
                ${station.source_table === 'weather_station1' ? `
                    <option value="pm">ค่าฝุ่น (PM)</option>
                    <option value="temperature">อุณหภูมิ (°C)</option>
                    <option value="humidity">ความชื้น (%)</option>
                    <option value="wind_speed">ความเร็วลม (m/s)</option>
                    <option value="rain">ปริมาณฝน (mm)</option>
                ` : `
                    <option value="pm25">ค่าฝุ่น PM2.5 (µg/m³)</option>
                    <option value="pm10">ค่าฝุ่น PM10 (µg/m³)</option>
                    <option value="temperature">อุณหภูมิ (°C)</option>
                    <option value="humidity">ความชื้น (%)</option>
                `}
            </select>
            <div id="historyControls" style="display: none;">
                <div class="date-inputs">
                    <input type="date" id="startDate">
                    <input type="date" id="endDate">
                </div>
                <button id="fetchHistory">ดูข้อมูลย้อนหลัง</button>
            </div>
        </div>
        <canvas id="weatherChart"></canvas>
    `;
    
    floatingChartContainer.innerHTML = contentHTML;
    
    // บังคับให้กราฟอยู่ด้านบนและตรงกลาง
    floatingChartContainer.style.display = 'block';
    floatingChartContainer.style.position = 'fixed';
    floatingChartContainer.style.top = '100px';
    floatingChartContainer.style.left = '50%';
    floatingChartContainer.style.transform = 'translateX(-50%)';
    floatingChartContainer.style.zIndex = '10000';
    floatingChartContainer.style.maxHeight = '70vh';
    floatingChartContainer.style.overflow = 'auto';
    
    // เพิ่ม animation
    floatingChartContainer.classList.add('chart-container-enter');
    setTimeout(() => {
        floatingChartContainer.classList.remove('chart-container-enter');
    }, 300);
    
    activeEui = eui;
    const chartTypeSelect = document.getElementById('chartType');
    const fetchHistoryBtn = document.getElementById('fetchHistory');
    const historyControls = document.getElementById('historyControls');
    const chartCanvas = document.getElementById('weatherChart');
    window.hideGraph = hideGraph;

    // ฟังก์ชันแสดงข้อมูลเรียลไทม์
    const showRealtime = () => {
        const chartType = chartTypeSelect.value;
        const data = dailyRealtimeData[eui] || [];
        
        const maxDataPoints = 50;
        const step = Math.max(1, Math.floor(data.length / maxDataPoints));
        const displayData = data.filter((_, index) => index % step === 0);
        
        const labels = displayData.map(d => {
            const date = new Date(d.timestamp);
            return `${date.toLocaleDateString('th-TH', { 
                day: '2-digit', 
                month: '2-digit' 
            })} ${date.toLocaleTimeString('th-TH', { 
                hour: '2-digit', 
                minute: '2-digit' 
            })}`;
        });
        const values = displayData.map(d => chartFieldMap[chartType].get(d.originalData));
        createChart(chartCanvas, labels, values, chartType, station.name, true);
    };

    const showHistory = async () => {
        const chartType = chartTypeSelect.value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (!startDate || !endDate) return;
        
        const url = getHistoryEndpoint(station, startDate, endDate);
        
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const data = await response.json();
            
            const historyData = data.map(d => {
                return station.source_table === 'weather_station1' ? normalizeW1(d) : normalizeW2(d);
            }).filter(d => d && chartFieldMap[chartType].get(d) != null);

            if (historyData.length === 0) {
                alert('ไม่พบข้อมูลในช่วงเวลาที่เลือก');
                if (chartInstance) chartInstance.destroy();
                return;
            }
            historyData.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
            
            const maxHistoryPoints = 100;
            const step = Math.max(1, Math.floor(historyData.length / maxHistoryPoints));
            const displayData = historyData.filter((_, index) => index % step === 0);
            
            const labels = displayData.map(d => {
                const date = new Date(d.timestamp);
                return `${date.toLocaleDateString()} ${date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`;
            });
            const values = displayData.map(d => chartFieldMap[chartType].get(d));
            createChart(chartCanvas, labels, values, chartType, station.name, false);
        } catch (err) {
            console.error('Failed to fetch history data:', err);
            alert('เกิดข้อผิดพลาดในการดึงข้อมูลย้อนหลัง: ' + err.message);
        }
    };

    document.querySelectorAll('input[name="chartMode"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'realtime') {
                historyControls.style.display = 'none';
                showRealtime();
            } else {
                historyControls.style.display = 'block';
                if (chartInstance) chartInstance.destroy();
            }
        });
    });

    chartTypeSelect.addEventListener('change', () => {
        const chartMode = document.querySelector('input[name="chartMode"]:checked').value;
        if (chartMode === 'realtime') {
            showRealtime();
        } else if (document.getElementById('startDate').value && document.getElementById('endDate').value) {
            showHistory();
        }
    });
    fetchHistoryBtn.addEventListener('click', showHistory);
    showRealtime();
}

// แก้ไขฟังก์ชัน showFloatingChart
function showFloatingChart() {
    // สร้าง backdrop
    const backdrop = createChartBackdrop();
    backdrop.classList.add('show');
    
    floatingLineChartContainer.style.display = 'block';
    
    // บังคับตำแหน่งกราฟรวม 10 สถานี
    floatingLineChartContainer.style.position = 'fixed';
    floatingLineChartContainer.style.top = '100px';
    floatingLineChartContainer.style.left = '50%';
    floatingLineChartContainer.style.transform = 'translateX(-50%)';
    floatingLineChartContainer.style.zIndex = '10000';
    floatingLineChartContainer.style.maxHeight = '80vh';
    floatingLineChartContainer.style.overflow = 'auto';
    
    // เพิ่ม animation
    floatingLineChartContainer.classList.add('chart-container-enter');
    setTimeout(() => {
        floatingLineChartContainer.classList.remove('chart-container-enter');
    }, 300);
    
    const dataType = document.getElementById('floating-chart-data-type').value;
    const isRealtime = document.querySelector('input[name="floatingChartMode"]:checked').value === 'realtime';
    createFloatingLineChart(dataType, isRealtime);
    
    updateFloatingHistoryControls();
}

// เพิ่มฟังก์ชันสำหรับปรับตำแหน่งกราฟเมื่อ scroll
function adjustChartPosition() {
    const charts = [
        document.getElementById('floating-chart-container'),
        document.getElementById('floating-line-chart-container')
    ];
    
    charts.forEach(chart => {
        if (chart && chart.style.display !== 'none') {
            // คงตำแหน่ง fixed ไว้
            chart.style.position = 'fixed';
            chart.style.top = '100px';
            chart.style.left = '50%';
            chart.style.transform = 'translateX(-50%)';
            chart.style.zIndex = '10000';
        }
    });
}

// เพิ่ม event listener สำหรับ scroll และ resize
window.addEventListener('scroll', adjustChartPosition);
window.addEventListener('resize', adjustChartPosition);

// เพิ่มฟังก์ชันสำหรับปิดกราฟด้วย ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (floatingChartContainer.style.display !== 'none') {
            hideGraph();
        }
        if (floatingLineChartContainer.style.display !== 'none') {
            hideFloatingChart();
        }
    }
});

// ป้องกันการ scroll ของ body เมื่อกราฟเปิดอยู่
function preventBodyScroll(prevent) {
    if (prevent) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}

// แก้ไขฟังก์ชัน hideGraph และ hideFloatingChart เพิ่มเติม
const originalHideGraph = hideGraph;
hideGraph = function() {
    preventBodyScroll(false);
    originalHideGraph();
};

const originalHideFloatingChart = hideFloatingChart;
hideFloatingChart = function() {
    preventBodyScroll(false);
    originalHideFloatingChart();
};

// แก้ไขฟังก์ชัน showGraph และ showFloatingChart เพิ่มเติม
const originalShowGraph = showGraph;
showGraph = async function(eui) {
    preventBodyScroll(true);
    await originalShowGraph(eui);
};

const originalShowFloatingChart = showFloatingChart;
showFloatingChart = function() {
    preventBodyScroll(true);
    originalShowFloatingChart();
};
// ฟังก์ชันตรวจสอบและแก้ไขตำแหน่งกราฟ
function ensureChartPosition() {
    const charts = [
        { element: document.getElementById('floating-chart-container'), width: '400px' },
        { element: document.getElementById('floating-line-chart-container'), width: '80%' }
    ];
    
    charts.forEach(chart => {
        if (chart.element && chart.element.style.display !== 'none') {
            // รีเซ็ตตำแหน่ง
            chart.element.style.position = 'fixed';
            chart.element.style.top = '100px';
            chart.element.style.left = '50%';
            chart.element.style.transform = 'translateX(-50%)';
            chart.element.style.zIndex = '10000';
            chart.element.style.width = chart.width;
            chart.element.style.maxWidth = window.innerWidth < 768 ? '90vw' : chart.width;
            chart.element.style.maxHeight = '80vh';
            chart.element.style.overflow = 'auto';
            
            // ตรวจสอบว่ากราฟอยู่ในขอบเขตหน้าจอ
            const rect = chart.element.getBoundingClientRect();
            if (rect.top < 0) {
                chart.element.style.top = '20px';
            }
            if (rect.bottom > window.innerHeight) {
                chart.element.style.maxHeight = (window.innerHeight - 40) + 'px';
            }
        }
    });
}

// เรียกใช้ฟังก์ชันตรวจสอบตำแหน่งทุก 1 วินาที
setInterval(ensureChartPosition, 1000);

// เพิ่มฟังก์ชันสำหรับการจัดการ responsive
function handleChartResize() {
    const charts = document.querySelectorAll('#floating-chart-container, #floating-line-chart-container');
    
    charts.forEach(chart => {
        if (chart.style.display !== 'none') {
            if (window.innerWidth <= 768) {
                // Mobile
                chart.style.top = '20px';
                chart.style.left = '10px';
                chart.style.right = '10px';
                chart.style.transform = 'none';
                chart.style.width = 'auto';
                chart.style.minWidth = 'auto';
                chart.style.maxWidth = 'none';
            } else {
                // Desktop
                chart.style.top = '100px';
                chart.style.left = '50%';
                chart.style.right = 'auto';
                chart.style.transform = 'translateX(-50%)';
                
                if (chart.id === 'floating-chart-container') {
                    chart.style.width = '400px';
                    chart.style.maxWidth = '90vw';
                } else {
                    chart.style.width = '80%';
                    chart.style.minWidth = '800px';
                    chart.style.maxWidth = '1200px';
                }
            }
        }
    });
}

// เพิ่ม event listener สำหรับ resize
window.addEventListener('resize', handleChartResize);

// ฟังก์ชันสำหรับการจัดการ focus trap ในกราฟ
function setupFocusTrap(container) {
    const focusableElements = container.querySelectorAll(
        'button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusableElements.length === 0) return;
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    container.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        }
    });
    
    // Focus ที่ปุ่มปิดเมื่อเปิดกราฟ
    const closeButton = container.querySelector('.close-button');
    if (closeButton) {
        setTimeout(() => closeButton.focus(), 100);
    }
}

// แก้ไขฟังก์ชัน showGraph เพิ่ม focus trap
const originalShowGraphWithFocus = showGraph;
showGraph = async function(eui) {
    await originalShowGraphWithFocus(eui);
    setupFocusTrap(floatingChartContainer);
};

// แก้ไขฟังก์ชัน showFloatingChart เพิ่ม focus trap
const originalShowFloatingChartWithFocus = showFloatingChart;
showFloatingChart = function() {
    originalShowFloatingChartWithFocus();
    setupFocusTrap(floatingLineChartContainer);
};

// เพิ่มฟังก์ชันสำหรับการแจ้งเตือนเมื่อกราฟโหลดเสร็จ
function announceChartLoaded(chartType, stationName) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.style.position = 'absolute';
    announcement.style.left = '-10000px';
    announcement.style.width = '1px';
    announcement.style.height = '1px';
    announcement.style.overflow = 'hidden';
    
    const message = stationName ? 
        `กราฟ${chartType}ของ${stationName}โหลดเสร็จแล้ว` : 
        `กราฟ${chartType}โหลดเสร็จแล้ว`;
    
    announcement.textContent = message;
    document.body.appendChild(announcement);
    
    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// เพิ่มการแจ้งเตือนใน createChart และ createFloatingLineChart (รวมเข้ากับ wrapper เดิม)
if (!window._createChartAnnounced) {
    window._createChartAnnounced = true;
    const prevCreateChart = createChart;
    createChart = function(canvas, labels, values, chartType, chartLabel, isRealtime = false) {
        prevCreateChart(canvas, labels, values, chartType, chartLabel, isRealtime);
        if (typeof announceChartLoaded === 'function') {
            const label = chartFieldMap[chartType]?.label || chartType;
            announceChartLoaded(label, chartLabel);
        }
    };

    const prevCreateFloatingLineChart = createFloatingLineChart;
    createFloatingLineChart = function(dataType, isRealtime, historyData = null) {
        prevCreateFloatingLineChart(dataType, isRealtime, historyData);
        if (typeof announceChartLoaded === 'function') {
            const label = chartFieldMap[dataType]?.label || dataType;
            announceChartLoaded(label, 'รวม 10 สถานี');
        }
    };
}

// เพิ่มฟังก์ชันสำหรับการบันทึกสถานะกราฟ
function saveChartState() {
    const state = {
        activeChart: null,
        floatingChart: null
    };
    
    if (floatingChartContainer.style.display !== 'none') {
        state.activeChart = {
            eui: activeEui,
            chartType: document.getElementById('chartType')?.value,
            mode: document.querySelector('input[name="chartMode"]:checked')?.value
        };
    }
    
    if (floatingLineChartContainer.style.display !== 'none') {
        state.floatingChart = {
            dataType: document.getElementById('floating-chart-data-type')?.value,
            mode: document.querySelector('input[name="floatingChartMode"]:checked')?.value
        };
    }
    
    sessionStorage.setItem('chartState', JSON.stringify(state));
}

// เพิ่มฟังก์ชันสำหรับการกู้คืนสถานะกราฟ
function restoreChartState() {
    const savedState = sessionStorage.getItem('chartState');
    if (!savedState) return;
    
    try {
        const state = JSON.parse(savedState);
        
        if (state.activeChart && state.activeChart.eui) {
            setTimeout(() => {
                showGraph(state.activeChart.eui);
                if (state.activeChart.chartType) {
                    setTimeout(() => {
                        const chartTypeSelect = document.getElementById('chartType');
                        if (chartTypeSelect) {
                            chartTypeSelect.value = state.activeChart.chartType;
                            chartTypeSelect.dispatchEvent(new Event('change'));
                        }
                    }, 500);
                }
            }, 1000);
        }
        
        if (state.floatingChart) {
            setTimeout(() => {
                document.getElementById('toggle-floating-chart').checked = true;
                showFloatingChart();
                if (state.floatingChart.dataType) {
                    setTimeout(() => {
                        const dataTypeSelect = document.getElementById('floating-chart-data-type');
                        if (dataTypeSelect) {
                            dataTypeSelect.value = state.floatingChart.dataType;
                            dataTypeSelect.dispatchEvent(new Event('change'));
                        }
                    }, 500);
                }
            }, 1000);
        }
    } catch (error) {
        console.warn('ไม่สามารถกู้คืนสถานะกราฟได้:', error);
    }
}

// บันทึกสถานะเมื่อมีการเปลี่ยนแปลง
window.addEventListener('beforeunload', saveChartState);

// กู้คืนสถานะเมื่อโหลดหน้าเว็บ
window.addEventListener('load', () => {
    setTimeout(restoreChartState, 2000);
});

console.log('Chart positioning system loaded successfully');
// ฟังก์ชันใหม่สำหรับแสดงกราฟเฉพาะข้อมูลที่เลือก
function showStationChart(eui, dataType) {
    const station = latestStationData.find(s => s.eui === eui);
    if (!station) {
        alert('ไม่พบข้อมูลสถานี');
        return;
    }
    
    // ปิด popup แผนที่
    map.closePopup();
    
    // เปิดกราฟแบบเดิมแต่เลือกประเภทข้อมูลที่ต้องการ
    showGraph(eui);
    
    // รอให้กราฟโหลดแล้วเลือกประเภทข้อมูล
    setTimeout(() => {
        const chartTypeSelect = document.getElementById('chartType');
        if (chartTypeSelect) {
            // แปลง dataType เป็นค่าที่ถูกต้องสำหรับแต่ละสถานี
            let mappedType = dataType;
            if (dataType === 'pm' && station.source_table === 'weather_station2') {
                mappedType = 'pm25'; // W2 ใช้ pm25 แทน pm
            }
            
            chartTypeSelect.value = mappedType;
            chartTypeSelect.dispatchEvent(new Event('change'));
        }
    }, 500);
}

// ทำให้ฟังก์ชันเป็น global
window.showStationChart = showStationChart;












</script>
</body>
</html>
            