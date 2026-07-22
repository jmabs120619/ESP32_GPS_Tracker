<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

// Prevent multiple active trips
$check = mysqli_query($conn, "SELECT id FROM trips WHERE status='ON GOING' LIMIT 1");

if(mysqli_num_rows($check) > 0)
{
    header("Location: ../dashboard/index.php?trip_exists=1");
    exit();
}

// Generate Trip Number
$result = mysqli_query($conn, "SELECT id FROM trips ORDER BY id DESC LIMIT 1");

if(mysqli_num_rows($result) > 0)
{
    $row = mysqli_fetch_assoc($result);
    $next = $row['id'] + 1;
}
else
{
    $next = 1;
}

$trip_no = "TRIP-" . str_pad($next, 4, "0", STR_PAD_LEFT);

$captain = $_POST['captain'];
$departure = $_POST['departure'];
$destination = $_POST['destination'];
$remarks = $_POST['remarks'];

$stmt = $conn->prepare("
INSERT INTO trips
(
trip_no,
captain,
departure,
destination,
departure_time,
status,
remarks
)
VALUES
(
?,
?,
?,
?,
NOW(),
'ON GOING',
?
)
");

$stmt->bind_param(
"sssss",
$trip_no,
$captain,
$departure,
$destination,
$remarks
);

if($stmt->execute())
{
    header("Location: ../dashboard/index.php?trip_started=1");
}
else
{
    echo "Error creating trip.";
}