<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

date_default_timezone_set("Asia/Manila");

/* =========================================================
   VALIDATE TRIP ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../trip_history/index.php");
    exit();
}

$trip_id = (int) $_GET['id'];

/* =========================================================
   GET TRIP INFORMATION
========================================================= */

$stmtTrip = $conn->prepare("
    SELECT *
    FROM trips
    WHERE id = ?
    LIMIT 1
");

$stmtTrip->bind_param("i", $trip_id);
$stmtTrip->execute();

$tripResult = $stmtTrip->get_result();

if ($tripResult->num_rows === 0) {
    die("Trip not found.");
}

$trip = $tripResult->fetch_assoc();

/* =========================================================
   GET PASSENGER MANIFEST
========================================================= */

$stmtPassengers = $conn->prepare("
    SELECT
        p.fullname,
        p.passenger_type,
        p.gender,
        p.age,
        p.address,
        p.contact_no,
        tp.time_boarded,
        tp.time_departed,
        tp.status
    FROM trip_passengers tp
    INNER JOIN passengers p
        ON p.id = tp.passenger_id
    WHERE tp.trip_id = ?
    ORDER BY tp.time_boarded ASC
");

$stmtPassengers->bind_param("i", $trip_id);
$stmtPassengers->execute();

$passengers = $stmtPassengers->get_result();
$totalPassengers = $passengers->num_rows;

/* =========================================================
   GET GPS STATISTICS
========================================================= */

$stmtStats = $conn->prepare("
    SELECT
        COUNT(*) AS gps_points,
        COALESCE(ROUND(AVG(speed), 2), 0) AS avg_speed,
        COALESCE(ROUND(MAX(speed), 2), 0) AS max_speed,
        COALESCE(ROUND(AVG(satellites), 2), 0) AS avg_satellites
    FROM gps_logs
    WHERE trip_id = ?
");

$stmtStats->bind_param("i", $trip_id);
$stmtStats->execute();

$stats = $stmtStats->get_result()->fetch_assoc();

/* =========================================================
   HAVERSINE DISTANCE
========================================================= */

function haversineDistance(
    float $lat1,
    float $lon1,
    float $lat2,
    float $lon2
): float {
    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a =
        sin($dLat / 2) ** 2 +
        cos(deg2rad($lat1)) *
        cos(deg2rad($lat2)) *
        sin($dLon / 2) ** 2;

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

/* =========================================================
   GET GPS ROUTE AND CALCULATE DISTANCE
========================================================= */

$stmtRoute = $conn->prepare("
    SELECT
        latitude,
        longitude,
        speed,
        satellites,
        recorded_at
    FROM gps_logs
    WHERE trip_id = ?
    ORDER BY recorded_at ASC
");

$stmtRoute->bind_param("i", $trip_id);
$stmtRoute->execute();

$routeResult = $stmtRoute->get_result();

$route = [];
$totalDistance = 0;
$previousPoint = null;

while ($point = $routeResult->fetch_assoc()) {
    $route[] = $point;

    if ($previousPoint !== null) {
        $segmentDistance = haversineDistance(
            (float) $previousPoint['latitude'],
            (float) $previousPoint['longitude'],
            (float) $point['latitude'],
            (float) $point['longitude']
        );

        /*
         * Ignore unrealistic jumps larger than 5 km between
         * consecutive GPS updates.
         */
        if ($segmentDistance <= 5) {
            $totalDistance += $segmentDistance;
        }
    }

    $previousPoint = $point;
}

/* =========================================================
   CALCULATE TRIP DURATION
========================================================= */

$tripDuration = "Still ongoing";

if (!empty($trip['departure_time'])) {
    $startTime = strtotime($trip['departure_time']);

    $endTime = !empty($trip['arrival_time'])
        ? strtotime($trip['arrival_time'])
        : time();

    $durationSeconds = max(0, $endTime - $startTime);

    $hours = floor($durationSeconds / 3600);
    $minutes = floor(($durationSeconds % 3600) / 60);

    if ($hours > 0) {
        $tripDuration = $hours . " hr " . $minutes . " min";
    } else {
        $tripDuration = $minutes . " min";
    }
}

/* =========================================================
   REPORT DETAILS
========================================================= */

$printedBy = $_SESSION['fullname'] ?? "System Administrator";
$printedAt = date("F d, Y h:i A");

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Trip Report - <?= htmlspecialchars($trip['trip_no']); ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>

        body {
            background: #e9ecef;
            color: #212529;
            font-family: Arial, sans-serif;
        }

        .report-page {
            max-width: 1100px;
            margin: 25px auto;
            padding: 35px;
            background: #ffffff;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        }

        .report-header {
            text-align: center;
            border-bottom: 3px solid #212529;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .report-header h2,
        .report-header h3,
        .report-header p {
            margin: 3px 0;
        }

        .report-title {
            margin-top: 18px !important;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .section-title {
            background: #212529;
            color: #ffffff;
            padding: 9px 12px;
            font-size: 17px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .information-table th {
            width: 20%;
            background: #f1f3f5;
        }

        .statistics-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            height: 100%;
        }

        .statistics-card h3 {
            margin: 0;
            font-weight: bold;
        }

        .statistics-card small {
            color: #6c757d;
        }

        #reportMap {
            width: 100%;
            height: 430px;
            border: 1px solid #adb5bd;
        }

        .signature-area {
            margin-top: 70px;
        }

        .signature-line {
            width: 80%;
            margin: 55px auto 5px;
            border-top: 1px solid #000000;
            text-align: center;
            padding-top: 5px;
        }

        .report-footer {
            border-top: 1px solid #adb5bd;
            margin-top: 35px;
            padding-top: 10px;
            color: #6c757d;
            font-size: 12px;
            text-align: center;
        }

        @media print {

            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            body {
                background: #ffffff;
                font-size: 11px;
            }

            .no-print {
                display: none !important;
            }

            .report-page {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .section-title {
                color: #000000 !important;
                background: #e9ecef !important;
                border: 1px solid #000000;
            }

            .table {
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 5px;
            }

            .statistics-card {
                break-inside: avoid;
            }

            #reportMap {
                height: 350px;
                break-inside: avoid;
            }

            .passenger-section {
                break-before: auto;
            }

            .signature-area {
                break-inside: avoid;
            }
        }

    </style>

</head>

<body>

<div class="container-fluid no-print py-3">

    <div class="d-flex justify-content-center gap-2">

        <button
            type="button"
            class="btn btn-primary"
            onclick="printReport()">

            🖨 Print Report

        </button>

        <a
            href="../trip_history/view.php?id=<?= $trip_id; ?>"
            class="btn btn-secondary">

            ← Back to Trip Details

        </a>

    </div>

</div>

<div class="report-page">

    <!-- REPORT HEADER -->

    <div class="report-header">

        <!--
        Add your logo later using:

        <img
            src="../assets/images/csu-logo.png"
            alt="CSU Logo"
            style="width:80px;">
        -->

        <h2>CAGAYAN STATE UNIVERSITY</h2>

        <h4>Aparri Campus</h4>

        <p>College of Information and Computing Sciences</p>

        <h3 class="report-title">
            BOAT PASSENGER MONITORING SYSTEM
        </h3>

        <h4>TRIP REPORT</h4>

    </div>

    <!-- TRIP INFORMATION -->

    <div class="section-title">
        I. Trip Information
    </div>

    <div class="table-responsive">

        <table class="table table-bordered information-table">

            <tbody>

            <tr>

                <th>Trip Number</th>

                <td>
                    <?= htmlspecialchars($trip['trip_no']); ?>
                </td>

                <th>Status</th>

                <td>
                    <?= htmlspecialchars($trip['status']); ?>
                </td>

            </tr>

            <tr>

                <th>Captain</th>

                <td>
                    <?= htmlspecialchars($trip['captain']); ?>
                </td>

                <th>Passenger Count</th>

                <td>
                    <?= $totalPassengers; ?>
                </td>

            </tr>

            <tr>

                <th>Departure</th>

                <td>
                    <?= htmlspecialchars($trip['departure']); ?>
                </td>

                <th>Destination</th>

                <td>
                    <?= htmlspecialchars($trip['destination']); ?>
                </td>

            </tr>

            <tr>

                <th>Departure Time</th>

                <td>
                    <?= date(
                        "F d, Y h:i A",
                        strtotime($trip['departure_time'])
                    ); ?>
                </td>

                <th>Arrival Time</th>

                <td>

                    <?php if (!empty($trip['arrival_time'])) { ?>

                        <?= date(
                            "F d, Y h:i A",
                            strtotime($trip['arrival_time'])
                        ); ?>

                    <?php } else { ?>

                        Still ongoing

                    <?php } ?>

                </td>

            </tr>

            <tr>

                <th>Remarks</th>

                <td colspan="3">

                    <?= !empty($trip['remarks'])
                        ? nl2br(htmlspecialchars($trip['remarks']))
                        : "None"; ?>

                </td>

            </tr>

            </tbody>

        </table>

    </div>

    <!-- TRIP STATISTICS -->

    <div class="section-title">
        II. Trip Statistics
    </div>

    <div class="row g-3">

        <div class="col-6 col-md-3">

            <div class="statistics-card">

                <h3>
                    <?= number_format($totalDistance, 2); ?>
                </h3>

                <small>Distance Travelled (km)</small>

            </div>

        </div>

        <div class="col-6 col-md-3">

            <div class="statistics-card">

                <h3>
                    <?= htmlspecialchars($tripDuration); ?>
                </h3>

                <small>Trip Duration</small>

            </div>

        </div>

        <div class="col-6 col-md-3">

            <div class="statistics-card">

                <h3>
                    <?= number_format((float) $stats['avg_speed'], 2); ?>
                </h3>

                <small>Average Speed (km/h)</small>

            </div>

        </div>

        <div class="col-6 col-md-3">

            <div class="statistics-card">

                <h3>
                    <?= number_format((float) $stats['max_speed'], 2); ?>
                </h3>

                <small>Maximum Speed (km/h)</small>

            </div>

        </div>

    </div>

    <div class="row g-3 mt-1">

        <div class="col-6">

            <div class="statistics-card">

                <h3>
                    <?= (int) $stats['gps_points']; ?>
                </h3>

                <small>Total GPS Records</small>

            </div>

        </div>

        <div class="col-6">

            <div class="statistics-card">

                <h3>
                    <?= number_format(
                        (float) $stats['avg_satellites'],
                        2
                    ); ?>
                </h3>

                <small>Average Satellites</small>

            </div>

        </div>

    </div>

    <!-- PASSENGER MANIFEST -->

    <div class="section-title passenger-section">
        III. Passenger Manifest
    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped align-middle">

            <thead class="table-light">

            <tr>

                <th style="width:45px;">#</th>

                <th>Passenger Name</th>

                <th>Type</th>

                <th>Gender</th>

                <th>Age</th>

                <th>Boarded Time</th>

                <th>Status</th>

            </tr>

            </thead>

            <tbody>

            <?php if ($totalPassengers > 0) { ?>

                <?php $number = 1; ?>

                <?php while ($passenger = $passengers->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <?= $number++; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $passenger['fullname']
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $passenger['passenger_type']
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $passenger['gender']
                            ); ?>
                        </td>

                        <td>
                            <?= (int) $passenger['age']; ?>
                        </td>

                        <td>

                            <?php if (!empty($passenger['time_boarded'])) { ?>

                                <?= date(
                                    "h:i A",
                                    strtotime($passenger['time_boarded'])
                                ); ?>

                            <?php } else { ?>

                                -

                            <?php } ?>

                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $passenger['status']
                            ); ?>
                        </td>

                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted">

                        No passengers were recorded for this trip.

                    </td>

                </tr>

            <?php } ?>

            </tbody>

            <tfoot>

            <tr>

                <th colspan="6" class="text-end">
                    Total Passengers
                </th>

                <th>
                    <?= $totalPassengers; ?>
                </th>

            </tr>

            </tfoot>

        </table>

    </div>

    <!-- GPS ROUTE -->

    <div class="section-title">
        IV. GPS Route
    </div>

    <?php if (count($route) > 0) { ?>

        <div id="reportMap"></div>

        <p class="small text-muted mt-2">

            The route above was generated from
            <?= count($route); ?> GPS records associated with this trip.

        </p>

    <?php } else { ?>

        <div class="alert alert-warning">

            No GPS route records were found for this trip.

        </div>

    <?php } ?>

    <!-- SIGNATURES -->

    <div class="section-title">
        V. Certification
    </div>

    <div class="row signature-area">

        <div class="col-4 text-center">

            <div class="signature-line">

                <?= htmlspecialchars($printedBy); ?><br>

                <small>Prepared By</small>

            </div>

        </div>

        <div class="col-4 text-center">

            <div class="signature-line">

                Boat Captain<br>

                <small>Verified By</small>

            </div>

        </div>

        <div class="col-4 text-center">

            <div class="signature-line">

                Authorized Personnel<br>

                <small>Approved By</small>

            </div>

        </div>

    </div>

    <div class="report-footer">

        Report generated on <?= htmlspecialchars($printedAt); ?>.

        <br>

        Boat Passenger Monitoring System with Real-Time GPS Tracking

    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

function printReport() {
    window.print();
}

const routeData = <?= json_encode(
    $route,
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
); ?>;

if (routeData.length > 0) {

    const firstPoint = routeData[0];

    const reportMap = L.map("reportMap").setView(
        [
            parseFloat(firstPoint.latitude),
            parseFloat(firstPoint.longitude)
        ],
        15
    );

    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 22,
            attribution: "&copy; OpenStreetMap contributors"
        }
    ).addTo(reportMap);

    const coordinates = routeData.map(function (point) {
        return [
            parseFloat(point.latitude),
            parseFloat(point.longitude)
        ];
    });

    if (coordinates.length > 1) {

        const routeLine = L.polyline(
            coordinates,
            {
                weight: 5,
                opacity: 0.85
            }
        ).addTo(reportMap);

        reportMap.fitBounds(
            routeLine.getBounds(),
            {
                padding: [20, 20]
            }
        );

    } else {

        reportMap.setView(coordinates[0], 18);

    }

    L.marker(coordinates[0])
        .addTo(reportMap)
        .bindPopup("<strong>Trip Started</strong>");

    L.marker(coordinates[coordinates.length - 1])
        .addTo(reportMap)
        .bindPopup("<strong>Trip Ended</strong>");

    /*
     * Leaflet sometimes needs its size recalculated after the
     * report page finishes loading.
     */
    setTimeout(function () {
        reportMap.invalidateSize();
    }, 300);

}

</script>

</body>
</html>