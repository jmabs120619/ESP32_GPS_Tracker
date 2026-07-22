<?php
require_once("../config/database.php");

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo "POST method required.";
    exit;
}

// Get data from ESP32
$device_code = $_POST['device_code'] ?? '';
$latitude    = $_POST['latitude'] ?? '';
$longitude   = $_POST['longitude'] ?? '';
$altitude    = $_POST['altitude'] ?? 0;
$speed       = $_POST['speed'] ?? 0;
$satellites  = $_POST['satellites'] ?? 0;
$gps_status  = $_POST['gps_status'] ?? 'UNKNOWN';

// Validate required fields
if (empty($device_code) || empty($latitude) || empty($longitude)) {
    http_response_code(400);
    echo "Missing required fields.";
    exit;
}

// Find the device
$stmt = $conn->prepare("SELECT id FROM devices WHERE device_code = ?");
$stmt->bind_param("s", $device_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(404);
    echo "Device not found.";
    exit;
}

$device = $result->fetch_assoc();
$device_id = $device['id'];

// Save GPS log
$stmt = $conn->prepare("
INSERT INTO gps_logs
(device_id, latitude, longitude, altitude, speed, satellites, gps_status)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iddddis",
    $device_id,
    $latitude,
    $longitude,
    $altitude,
    $speed,
    $satellites,
    $gps_status
);

if ($stmt->execute()) {

   // Update device status
$update = $conn->prepare("
UPDATE devices
SET
    status='ONLINE',
    last_seen=NOW()
WHERE id=?
");

$update->bind_param("i",$device_id);
$update->execute();

echo "SUCCESS";
} else {
    echo "FAILED";
}