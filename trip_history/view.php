<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

/* ===========================================================
   VALIDATE TRIP ID
=========================================================== */

if(!isset($_GET['id'])){
    header("Location:index.php");
    exit();
}

$trip_id = (int)$_GET['id'];

/* ===========================================================
   GET TRIP INFORMATION
=========================================================== */

$stmt = $conn->prepare("
SELECT *
FROM trips
WHERE id=?
");

$stmt->bind_param("i",$trip_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows==0){

    echo '
    <div class="container mt-4">
        <div class="alert alert-danger">
            Trip not found.
        </div>
    </div>';

    include("../includes/footer.php");
    exit();

}

$trip = $result->fetch_assoc();

/* ===========================================================
   GET PASSENGER MANIFEST
=========================================================== */

$stmtPassengers = $conn->prepare("
SELECT

p.fullname,
p.passenger_type,
p.gender,

tp.time_boarded,
tp.time_departed,
tp.status

FROM trip_passengers tp

INNER JOIN passengers p
ON p.id=tp.passenger_id

WHERE tp.trip_id=?

ORDER BY tp.time_boarded ASC

");

$stmtPassengers->bind_param("i",$trip_id);
$stmtPassengers->execute();

$passengers = $stmtPassengers->get_result();

/* ===========================================================
   GET TRIP STATISTICS
=========================================================== */

$stats = mysqli_fetch_assoc(mysqli_query($conn,"

SELECT

COUNT(*) AS gps_points,

ROUND(AVG(speed),2) AS avg_speed,

ROUND(MAX(speed),2) AS max_speed

FROM gps_logs

WHERE trip_id='$trip_id'

"));

/* ===========================================================
   GET GPS ROUTE
=========================================================== */

/* ===========================================================
   HAVERSINE DISTANCE FUNCTION
=========================================================== */

function haversineDistance($lat1, $lon1, $lat2, $lon2){

    $earthRadius = 6371; // kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) *
        cos(deg2rad($lat2)) *
        sin($dLon/2) *
        sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $earthRadius * $c;

}

$route = [];

$routeQuery = mysqli_query($conn,"
SELECT
    latitude,
    longitude,
    speed,
    recorded_at
FROM gps_logs
WHERE trip_id='$trip_id'
ORDER BY recorded_at ASC
");

$totalDistance = 0;
$previousPoint = null;

while($row = mysqli_fetch_assoc($routeQuery)){

    $route[] = $row;

    if($previousPoint){

        $totalDistance += haversineDistance(
            $previousPoint['latitude'],
            $previousPoint['longitude'],
            $row['latitude'],
            $row['longitude']
        );

    }

    $previousPoint = $row;
}

$tripDuration = "-";

if(!empty($trip['arrival_time'])){

    $start = strtotime($trip['departure_time']);
    $end = strtotime($trip['arrival_time']);

    $seconds = $end - $start;

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if($hours > 0){
    $tripDuration = "{$hours} hr {$minutes} min";
}else{
    $tripDuration = "{$minutes} min";
}
}

/* ===========================================================
   GET TRIP TIMELINE
=========================================================== */

$timeline = [];

/* Trip Started */
$timeline[] = [
    "icon" => "🚤",
    "title" => "Trip Started",
    "time" => $trip['departure_time']
];

/* GPS Records */
$routeQuery2 = mysqli_query($conn,"
SELECT recorded_at
FROM gps_logs
WHERE trip_id='$trip_id'
ORDER BY recorded_at ASC
");

while($gps = mysqli_fetch_assoc($routeQuery2)){

    $timeline[] = [
        "icon" => "📍",
        "title" => "GPS Position Recorded",
        "time" => $gps['recorded_at']
    ];

}

/* Trip Ended */
if(!empty($trip['arrival_time'])){

    $timeline[] = [
        "icon" => "🏁",
        "title" => "Trip Completed",
        "time" => $trip['arrival_time']
    ];

}

?>

<div class="container-fluid mt-4">

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>
        🚢 Trip Details
    </h2>

    <a href="index.php" class="btn btn-secondary">

        ← Back to Trip History

    </a>

</div>

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

Trip Information

</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3 mb-3">

<strong>Trip No</strong><br>

<?= htmlspecialchars($trip['trip_no']); ?>

</div>

<div class="col-md-3 mb-3">

<strong>Captain</strong><br>

<?= htmlspecialchars($trip['captain']); ?>

</div>

<div class="col-md-3 mb-3">

<strong>Departure</strong><br>

<?= htmlspecialchars($trip['departure']); ?>

</div>

<div class="col-md-3 mb-3">

<strong>Destination</strong><br>

<?= htmlspecialchars($trip['destination']); ?>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-3">

<strong>Departure Time</strong><br>

<?= $trip['departure_time']; ?>

</div>

<div class="col-md-3">

<strong>Arrival Time</strong><br>

<?= !empty($trip['arrival_time']) ? $trip['arrival_time'] : "Still Ongoing"; ?>

</div>

<div class="col-md-3">

<strong>Status</strong><br>

<?php if($trip['status']=="ON GOING"){ ?>

<span class="badge bg-success">

ON GOING

</span>

<?php }else{ ?>

<span class="badge bg-secondary">

COMPLETED

</span>

<?php } ?>

</div>

<div class="col-md-3">

<strong>Remarks</strong><br>

<?= htmlspecialchars($trip['remarks']); ?>

</div>

</div>

</div>

</div>

<!-- ==========================================================
     PASSENGER MANIFEST
=========================================================== -->

<div class="card shadow mt-4">

    <div class="card-header bg-success text-white d-flex justify-content-between">

        <h5 class="mb-0">

            Passenger Manifest

        </h5>

        <span class="badge bg-light text-dark">

            <?= $passengers->num_rows; ?> Passenger(s)

        </span>

    </div>

    <div class="card-body">

        <?php if($passengers->num_rows > 0){ ?>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-success">

                    <tr>

                        <th width="60">#</th>
                        <th>Passenger Name</th>
                        <th>Type</th>
                        <th>Gender</th>
                        <th>Boarded</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                $i = 1;

                while($row = $passengers->fetch_assoc()){

                ?>

                <tr>

                    <td><?= $i++; ?></td>

                    <td>

                        <strong>

                            <?= htmlspecialchars($row['fullname']); ?>

                        </strong>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['passenger_type']); ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['gender']); ?>

                    </td>

                    <td>

                        <?php

                        if(!empty($row['time_boarded'])){

                            echo date("h:i A",strtotime($row['time_boarded']));

                        }else{

                            echo "-";

                        }

                        ?>

                    </td>

                    <td>

                        <?php

                        switch($row['status']){

                            case "ON BOARD":

                                echo '<span class="badge bg-success">ON BOARD</span>';

                            break;

                            case "DISEMBARKED":

                                echo '<span class="badge bg-secondary">DISEMBARKED</span>';

                            break;

                            default:

                                echo '<span class="badge bg-warning text-dark">'.$row['status'].'</span>';

                        }

                        ?>

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

        <?php }else{ ?>

        <div class="alert alert-warning mb-0">

            No passengers were recorded for this trip.

        </div>

        <?php } ?>

    </div>

</div>

<!-- ==========================================================
     TRIP STATISTICS
=========================================================== -->

<div class="card shadow mt-4">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            Trip Statistics

        </h5>

    </div>

    <div class="card-body">

        <div class="row text-center">

            <div class="col-md-4">

                <h2>

                    <?= $stats['gps_points']; ?>

                </h2>

                <small class="text-muted">

                    GPS Records

                </small>

            </div>

            <div class="col-md-4">

                <h2>

                    <?= $stats['avg_speed']; ?>

                </h2>

                <small class="text-muted">

                    Average Speed (km/h)

                </small>

            </div>

            <div class="col-md-4">

                <h2>

                    <?= $stats['max_speed']; ?>

                </h2>

                <small class="text-muted">

                    Maximum Speed (km/h)

                </small>

            </div>

        </div>

    </div>

</div>
<!-- ==========================================================
     TRIP ROUTE
=========================================================== -->

<div class="card shadow mt-4">

    <div class="card-header bg-primary text-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                🗺 Trip Route
            </h5>

            <span class="badge bg-light text-dark">

                <?= count($route); ?> GPS Points

            </span>

        </div>

    </div>

    <div class="card-body">

        <?php if(count($route)>0){ ?>

            <div id="tripMap"
                 style="height:550px;border-radius:10px;">
            </div>

        <?php }else{ ?>

            <div class="alert alert-warning mb-0">

                No GPS records were found for this trip.

            </div>

        <?php } ?>

    </div>

</div>

<link rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

let gpsRoute = <?= json_encode($route); ?>;

if(gpsRoute.length>0){

    let first = gpsRoute[0];

    let map = L.map("tripMap").setView(
        [
            parseFloat(first.latitude),
            parseFloat(first.longitude)
        ],
        16
    );

    L.tileLayer(

        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",

        {

            attribution:"© OpenStreetMap",

            maxZoom:22

        }

    ).addTo(map);

    let coordinates=[];

    gpsRoute.forEach(function(item){

        coordinates.push([

            parseFloat(item.latitude),

            parseFloat(item.longitude)

        ]);

    });

    if(coordinates.length>1){

        let routeLine=L.polyline(

            coordinates,

            {

                color:"#0d6efd",

                weight:6,

                opacity:.8

            }

        ).addTo(map);

        map.fitBounds(routeLine.getBounds());

    }else{

        map.setView(coordinates[0],18);

    }

    /* START */

    L.marker(coordinates[0])

    .addTo(map)

    .bindPopup("<b>🚤 Trip Started</b>");

    /* END */

    L.marker(coordinates[coordinates.length-1])

    .addTo(map)

    .bindPopup("<b>🏁 Trip Ended</b>");

}

</script>

<div class="card shadow mt-4">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">
            🕒 Trip Timeline
        </h5>

    </div>

    <div class="card-body">

        <?php foreach($timeline as $event){ ?>

            <div class="d-flex mb-3">

                <div style="width:45px;font-size:24px;">

                    <?= $event['icon']; ?>

                </div>

                <div>

                    <strong>

                        <?= htmlspecialchars($event['title']); ?>

                    </strong>

                    <br>

                    <small class="text-muted">

                        <?= date("F d, Y h:i:s A", strtotime($event['time'])); ?>

                    </small>

                </div>

            </div>

            <hr>

        <?php } ?>

    </div>

</div>
<?php include("../includes/footer.php"); ?>