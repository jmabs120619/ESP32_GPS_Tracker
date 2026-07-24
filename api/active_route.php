<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

header("Content-Type: application/json; charset=UTF-8");

$response = [
    "success" => false,
    "trip_id" => null,
    "route" => [],
    "message" => ""
];

/*
|--------------------------------------------------------------------------
| Find the currently active trip
|--------------------------------------------------------------------------
|
| Change 'ON GOING' below if your database uses a different value,
| such as:
|
| ONGOING
| Ongoing
| ACTIVE
| Active
|
*/

$tripSql = "
    SELECT id
    FROM trips
    WHERE status = 'ON GOING'
    ORDER BY id DESC
    LIMIT 1
";

$tripResult = mysqli_query($conn, $tripSql);

if (!$tripResult) {

    http_response_code(500);

    $response["message"] = "Unable to retrieve the active trip.";
    $response["error"] = mysqli_error($conn);

    echo json_encode($response);
    exit();
}

if (mysqli_num_rows($tripResult) === 0) {

    $response["success"] = true;
    $response["message"] = "No active trip found.";

    echo json_encode($response);
    exit();
}

$trip = mysqli_fetch_assoc($tripResult);
$tripId = (int) $trip["id"];

$stmt = mysqli_prepare(
    $conn,
    "
        SELECT
            latitude,
            longitude,
            speed,
            altitude,
            satellites,
            recorded_at
        FROM gps_logs
        WHERE trip_id = ?
          AND latitude IS NOT NULL
          AND longitude IS NOT NULL
        ORDER BY recorded_at ASC, id ASC
    "
);

if (!$stmt) {

    http_response_code(500);

    $response["message"] = "Unable to prepare the route query.";
    $response["error"] = mysqli_error($conn);

    echo json_encode($response);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $tripId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$route = [];

while ($row = mysqli_fetch_assoc($result)) {

    $latitude = (float) $row["latitude"];
    $longitude = (float) $row["longitude"];

    if (
        $latitude < -90 ||
        $latitude > 90 ||
        $longitude < -180 ||
        $longitude > 180
    ) {
        continue;
    }

    $route[] = [
        "latitude" => $latitude,
        "longitude" => $longitude,
        "speed" => (float) ($row["speed"] ?? 0),
        "altitude" => (float) ($row["altitude"] ?? 0),
        "satellites" => (int) ($row["satellites"] ?? 0),
        "recorded_at" => $row["recorded_at"] ?? null
    ];
}

mysqli_stmt_close($stmt);

$response["success"] = true;
$response["trip_id"] = $tripId;
$response["route"] = $route;
$response["message"] = count($route) . " route points loaded.";

echo json_encode($response);