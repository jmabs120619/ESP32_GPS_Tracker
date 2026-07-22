<?php
require_once("../config/database.php");


require_once("../includes/auth.php");

require_once("../config/database.php");

include("../includes/header.php");

include("../includes/sidebar.php");

include("../includes/navbar.php");

if(isset($_GET['trip_started'])){ ?>

<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Success!</strong> Trip started successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>

<?php if(isset($_GET['trip_ended'])){ ?>

<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Trip Ended!</strong> The trip has been completed successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>

<?php if(isset($_GET['trip_exists'])){ ?>

<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong>Warning!</strong> There is already an ongoing trip.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>




<?php
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
$countQuery = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM trip_passengers tp
INNER JOIN trips t
ON t.id = tp.trip_id
WHERE t.status='ON GOING'
");

$count = mysqli_fetch_assoc($countQuery);

$result = $conn->query($sql);
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>GPS Tracking Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>

#map{
    width:100%;
    height:500px;
    border-radius:15px;
    margin-top:20px;
}

body{

background:#f5f5f5;

}

.card{

border-radius:15px;

box-shadow:0 3px 10px rgba(0,0,0,.15);

}

.value{

font-size:24px;

font-weight:bold;

color:#0d6efd;

}

</style>

</head>

<body>

<div class="container mt-4">

<h2 class="text-center mb-4">
GPS Tracking Dashboard
</h2>

<div class="row">

    <!-- Device -->
    <div class="col-md-2">

        <div class="card p-3">

            <h5>Boat</h5>

            <div class="value">
                <?= htmlspecialchars($data['device_name']); ?>
            </div>

        </div>

    </div>

    <!-- GPS Status -->
    <div class="col-md-2">

        <div class="card p-3">

            <h5>GPS Status</h5>

            <div class="value">
                <?= htmlspecialchars($data['status']); ?>
            </div>

        </div>

    </div>

    <!-- Passenger Count -->
    <div class="col-md-2">

        <div class="card p-3">

            <h5>Passengers</h5>

            <div class="value">
                <?= $count['total']; ?>
            </div>

        </div>

    </div>

    <!-- Speed -->
    <div class="col-md-2">

        <div class="card p-3">

            <h5>Speed</h5>

            <div class="value">
                <?= number_format($data['speed'],2); ?> km/h
            </div>

        </div>

    </div>

    <!-- Satellites -->
    <div class="col-md-2">

        <div class="card p-3">

            <h5>Satellites</h5>

            <div class="value">
                <?= $data['satellites']; ?>
            </div>

        </div>

    </div>

    <!-- Last Update -->
    <div class="col-md-2">

        <div class="card p-3">

            <h5>Last Update</h5>

            <div class="value">
                <?= date("h:i:s A", strtotime($data['recorded_at'])); ?>
            </div>

        </div>

    </div>

</div>

<div class="col-md-4">

<div class="card p-3">

<h5>Last Update</h5>

<div class="value">

<?= $data['recorded_at']; ?>

</div>

</div>

</div>

</div>


<br>
<?php

$tripQuery = mysqli_query($conn,"
SELECT *
FROM trips
WHERE status='ON GOING'
LIMIT 1
");

$activeTrip = mysqli_fetch_assoc($tripQuery);

?>

<div class="card mb-3">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            🚢 Current Trip
        </h5>

    </div>

    <div class="card-body">

        <?php if($activeTrip){ ?>

            <div class="row">

                <div class="col-md-3">
                    <strong>Trip No</strong><br>
                    <?= $activeTrip['trip_no']; ?>
                </div>

                <div class="col-md-3">
                    <strong>Captain</strong><br>
                    <?= $activeTrip['captain']; ?>
                </div>

                <div class="col-md-3">
                    <strong>Departure</strong><br>
                    <?= $activeTrip['departure']; ?>
                </div>

                <div class="col-md-3">
                    <strong>Destination</strong><br>
                    <?= $activeTrip['destination']; ?>
                </div>

            </div>

            <hr>

            <span class="badge bg-success">
                ON GOING
            </span>

            <a href="../trips/end_trip.php"
               class="btn btn-danger float-end"
               onclick="return confirm('End this trip?')">

                End Trip

            </a>

        <?php } else { ?>

            <div class="alert alert-warning">

                <strong>No Active Trip</strong>

            </div>

            <button
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#startTripModal">

                Start Trip

            </button>

        <?php } ?>

    </div>

</div>

<div class="row">

<div class="col-md-6">

<div class="card p-3">

<h5>Latitude</h5>

<div class="value">

<?= $data['latitude']; ?>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card p-3">

<h5>Longitude</h5>

<div class="value">

<?= $data['longitude']; ?>

</div>

</div>

</div>

</div>

<br>

<div class="row">

<div class="col-md-4">

<div class="card p-3">

<h5>Speed</h5>

<div class="value">

<?= number_format($data['speed'],2); ?>

km/h

</div>

</div>

</div>

<div class="col-md-4">

<div class="card p-3">

<h5>Altitude</h5>

<div class="value">

<?= number_format($data['altitude'],2); ?>

m

</div>

</div>

</div>

<div class="col-md-4">

<div class="card p-3">

<h5>Satellites</h5>

<div class="value">

<?= $data['satellites']; ?>

</div>

</div>

</div>
<div class="card p-3 mt-4">
    <h4>Live GPS Location</h4>

    <div id="map"></div>
</div>

</div>

</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

// Initial coordinates from PHP
var latitude = <?= $data['latitude']; ?>;
var longitude = <?= $data['longitude']; ?>;

// Create map
var map = L.map('map').setView([latitude, longitude], 18);

// OpenStreetMap Layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 22,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// Current Location Marker
var marker = L.marker([latitude, longitude]).addTo(map);

marker.bindPopup(
    "<b><?= $data['device_name']; ?></b><br>" +
    "Latitude: <?= $data['latitude']; ?><br>" +
    "Longitude: <?= $data['longitude']; ?><br>" +
    "Speed: <?= number_format($data['speed'],2); ?> km/h"
).openPopup();

// Route Line
var routeLine = L.polyline([], {
    color: 'blue',
    weight: 5
}).addTo(map);

// =========================
// Load Route History
// =========================
function loadRoute()
{
    fetch('../api/route.php')
    .then(response => response.json())
    .then(data => {

        let points = [];

        data.forEach(function(item){

            points.push([
                parseFloat(item.latitude),
                parseFloat(item.longitude)
            ]);

        });

        routeLine.setLatLngs(points);

    })
    .catch(error => {
        console.log("Route Error:", error);
    });
}

// Load route immediately
loadRoute();

// =========================
// Live Update
// =========================
setInterval(function(){

    fetch('../api/latest.php')
    .then(response => response.json())
    .then(data => {

        // Update Dashboard Cards
        document.getElementById("latitude").innerHTML = data.latitude;
        document.getElementById("longitude").innerHTML = data.longitude;
        document.getElementById("speed").innerHTML = parseFloat(data.speed).toFixed(2);
        document.getElementById("altitude").innerHTML = parseFloat(data.altitude).toFixed(2);
        document.getElementById("satellites").innerHTML = data.satellites;
        document.getElementById("status").innerHTML = data.status;
        document.getElementById("last_update").innerHTML = data.recorded_at;

        // Move marker
        marker.setLatLng([
            parseFloat(data.latitude),
            parseFloat(data.longitude)
        ]);

        // Update popup
        marker.setPopupContent(
            "<b>" + data.device_name + "</b><br>" +
            "Latitude: " + data.latitude + "<br>" +
            "Longitude: " + data.longitude + "<br>" +
            "Speed: " + parseFloat(data.speed).toFixed(2) + " km/h"
        );

        // Smoothly follow marker
        map.flyTo(
            [parseFloat(data.latitude), parseFloat(data.longitude)],
            map.getZoom(),
            {
                animate: true,
                duration: 1
            }
        );

        // Refresh route
        loadRoute();

    })
    .catch(error => {
        console.log("Live Update Error:", error);
    });

}, 3000);

</script>

</body>

</html>
<div class="modal fade"
id="startTripModal"
tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<form action="../trips/start_trip.php" method="POST">

<div class="modal-header">

<h5>Start New Trip</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<div class="mb-3">

<label>Captain</label>

<input
type="text"
name="captain"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Departure</label>

<input
type="text"
name="departure"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Destination</label>

<input
type="text"
name="destination"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Remarks</label>

<textarea
name="remarks"
class="form-control">
</textarea>

</div>

</div>

<div class="modal-footer">

<button
class="btn btn-success">

Start Trip

</button>

</div>

</form>

</div>

</div>

</div>
<?php include("../includes/footer.php"); ?>