<?php
require_once("../config/database.php");

header("Content-Type: application/json");

$sql = "
SELECT
    d.device_name,
    d.device_code,
    d.status,
    d.last_seen,
    g.latitude,
    g.longitude,
    g.altitude,
    g.speed,
    g.satellites,
    g.gps_status,
    g.recorded_at
FROM gps_logs g
INNER JOIN devices d
ON d.id = g.device_id
ORDER BY g.recorded_at DESC
LIMIT 1
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    echo json_encode($result->fetch_assoc());

} else {

    echo json_encode([
        "status"=>"empty",
        "message"=>"No GPS data found."
    ]);

}