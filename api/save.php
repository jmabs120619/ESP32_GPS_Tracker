<?php

require_once("../config/database.php");

date_default_timezone_set("Asia/Manila");
header("Content-Type: application/json");

/*
|--------------------------------------------------------------------------
| LOG FUNCTION
|--------------------------------------------------------------------------
*/

function writeLog($message)
{
    file_put_contents(
        __DIR__ . "/log.txt",
        "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL,
        FILE_APPEND
    );
}

/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "POST method required."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| GET POST DATA
|--------------------------------------------------------------------------
*/

$device_code = trim($_POST['device_code'] ?? '');
$latitude    = trim($_POST['latitude'] ?? '');
$longitude   = trim($_POST['longitude'] ?? '');
$altitude    = floatval($_POST['altitude'] ?? 0);
$speed       = floatval($_POST['speed'] ?? 0);
$satellites  = intval($_POST['satellites'] ?? 0);
$gps_status  = trim($_POST['gps_status'] ?? 'UNKNOWN');

/*
|--------------------------------------------------------------------------
| LOG REQUEST
|--------------------------------------------------------------------------
*/

writeLog(json_encode($_POST));

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (
    empty($device_code) ||
    $latitude === '' ||
    $longitude === ''
) {

    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| FIND DEVICE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id FROM devices WHERE device_code=? LIMIT 1"
);

$stmt->bind_param("s", $device_code);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    http_response_code(404);

    echo json_encode([
        "status" => "error",
        "message" => "Device not found."
    ]);

    exit;
}

$device = $result->fetch_assoc();

$device_id = $device['id'];

/*
|--------------------------------------------------------------------------
| SAVE GPS LOG
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
INSERT INTO gps_logs
(
device_id,
latitude,
longitude,
altitude,
speed,
satellites,
gps_status
)
VALUES
(
?,
?,
?,
?,
?,
?,
?
)
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

if (!$stmt->execute()) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Unable to save GPS data."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE DEVICE
|--------------------------------------------------------------------------
*/

$update = $conn->prepare("
UPDATE devices
SET
status='ONLINE',
last_seen=NOW()
WHERE id=?
");

$update->bind_param("i", $device_id);
$update->execute();

/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

echo json_encode([
    "status" => "success",
    "message" => "GPS data saved successfully.",
    "device" => $device_code,
    "time" => date("Y-m-d H:i:s")
]);