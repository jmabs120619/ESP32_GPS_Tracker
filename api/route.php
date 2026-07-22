<?php

require_once("../config/database.php");

header("Content-Type: application/json");

$sql = "
SELECT
latitude,
longitude,
recorded_at
FROM gps_logs
ORDER BY recorded_at ASC
";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc())
{
    $data[] = $row;
}

echo json_encode($data);