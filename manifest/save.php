<?php if(isset($_GET['added'])){ ?>

<div class="alert alert-success alert-dismissible fade show">

    Passenger successfully added to the trip.

    <button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<?php if(isset($_GET['exists'])){ ?>

<div class="alert alert-warning alert-dismissible fade show">

    Passenger is already in this trip.

    <button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

// Check required parameters
if (!isset($_GET['trip']) || !isset($_GET['passenger'])) {
    header("Location: index.php");
    exit();
}

$trip_id = (int) $_GET['trip'];
$passenger_id = (int) $_GET['passenger'];

// Check if passenger is already added to this trip
$check = $conn->prepare("
    SELECT id
    FROM trip_passengers
    WHERE trip_id = ?
    AND passenger_id = ?
");

$check->bind_param("ii", $trip_id, $passenger_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    header("Location: index.php?exists=1");
    exit();
}

// Insert passenger into trip
$stmt = $conn->prepare("
    INSERT INTO trip_passengers
    (
        trip_id,
        passenger_id,
        time_boarded,
        status
    )
    VALUES
    (
        ?,
        ?,
        NOW(),
        'ON BOARD'
    )
");

$stmt->bind_param("ii", $trip_id, $passenger_id);

if ($stmt->execute()) {
    header("Location: index.php?added=1");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>