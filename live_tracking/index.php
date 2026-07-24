<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    crossorigin=""
>

<style>
    #liveMap {
        width: 100%;
        height: 600px;
        border-radius: 6px;
    }

    .tracking-value {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .coordinate-value {
        font-size: 1.35rem;
        font-weight: 600;
        word-break: break-word;
    }

    .map-status {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 500;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 5px;
        padding: 7px 10px;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        font-size: 13px;
    }

    .map-container {
        position: relative;
    }

    @media (max-width: 768px) {
        #liveMap {
            height: 450px;
        }
    }
</style>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">

        <div class="mb-2">
            <h3 class="mb-1">Live Boat Tracking</h3>

            <p class="text-muted mb-0">
                Real-time GPS location and active-trip route
            </p>
        </div>

        <span
            id="connectionBadge"
            class="badge bg-secondary"
        >
            Waiting for GPS...
        </span>

    </div>

    <!-- GPS SUMMARY -->

    <div class="row mb-4">

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        GPS Status
                    </small>

                    <div
                        id="gpsStatus"
                        class="tracking-value"
                    >
                        Waiting...
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Speed
                    </small>

                    <div class="tracking-value">
                        <span id="boatSpeed">0.00</span> km/h
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Satellites
                    </small>

                    <div
                        id="satelliteCount"
                        class="tracking-value"
                    >
                        0
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-3 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Last Update
                    </small>

                    <div
                        id="lastUpdate"
                        class="tracking-value"
                    >
                        No data
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- GPS DETAILS -->

    <div class="row mb-4">

        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Latitude
                    </h6>

                    <div
                        id="latitude"
                        class="coordinate-value"
                    >
                        0.000000
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Longitude
                    </h6>

                    <div
                        id="longitude"
                        class="coordinate-value"
                    >
                        0.000000
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-4 col-md-6 mb-3">

            <div class="card shadow-sm h-100">
                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Altitude
                    </h6>

                    <div class="coordinate-value">
                        <span id="altitude">0.00</span> m
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- MAP -->

    <div class="card shadow mb-4">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

            <strong>Boat Location and Route</strong>

            <small id="routePointCount">
                Route points: 0
            </small>

        </div>

        <div class="card-body p-2">

            <div class="map-container">

                <div
                    id="mapStatus"
                    class="map-status"
                >
                    Loading route...
                </div>

                <div id="liveMap"></div>

            </div>

        </div>

    </div>

</div>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    crossorigin=""
></script>

<script>
"use strict";

/*
|--------------------------------------------------------------------------
| Map initialization
|--------------------------------------------------------------------------
*/

const defaultPosition = [18.3564, 121.6410];

const liveMap = L.map("liveMap").setView(
    defaultPosition,
    13
);

L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
        maxZoom: 22,
        attribution: "&copy; OpenStreetMap contributors"
    }
).addTo(liveMap);

/*
|--------------------------------------------------------------------------
| Map variables
|--------------------------------------------------------------------------
*/

let boatMarker = null;
let startMarker = null;
let routeLine = null;

let routeCoordinates = [];

let firstValidLocation = true;
let trackingRequestRunning = false;

/*
|--------------------------------------------------------------------------
| Utility functions
|--------------------------------------------------------------------------
*/

function setConnectionStatus(status, message) {

    const badge = document.getElementById("connectionBadge");
    const gpsStatus = document.getElementById("gpsStatus");

    badge.textContent = message;
    gpsStatus.textContent = status;

    if (status === "CONNECTED") {
        badge.className = "badge bg-success";
    } else if (status === "NO ACTIVE TRIP") {
        badge.className = "badge bg-warning text-dark";
    } else if (status === "DISCONNECTED") {
        badge.className = "badge bg-danger";
    } else {
        badge.className = "badge bg-secondary";
    }
}

function updateRoutePointCount() {

    document.getElementById(
        "routePointCount"
    ).textContent = "Route points: " + routeCoordinates.length;
}

function isValidCoordinate(latitude, longitude) {

    return (
        Number.isFinite(latitude) &&
        Number.isFinite(longitude) &&
        latitude >= -90 &&
        latitude <= 90 &&
        longitude >= -180 &&
        longitude <= 180
    );
}

function isNewRoutePosition(latitude, longitude) {

    const previousPosition =
        routeCoordinates[routeCoordinates.length - 1];

    if (!previousPosition) {
        return true;
    }

    return (
        Math.abs(previousPosition[0] - latitude) > 0.000001 ||
        Math.abs(previousPosition[1] - longitude) > 0.000001
    );
}

function drawRoute() {

    if (routeCoordinates.length === 0) {
        return;
    }

    if (routeLine === null) {

        routeLine = L.polyline(
            routeCoordinates,
            {
                color: "#0d6efd",
                weight: 5,
                opacity: 0.85
            }
        ).addTo(liveMap);

    } else {

        routeLine.setLatLngs(routeCoordinates);

    }

    updateRoutePointCount();
}

function createStartMarker() {

    if (routeCoordinates.length === 0) {
        return;
    }

    const startPosition = routeCoordinates[0];

    if (startMarker === null) {

        startMarker = L.circleMarker(
            startPosition,
            {
                radius: 8,
                color: "#198754",
                fillColor: "#198754",
                fillOpacity: 1,
                weight: 3
            }
        )
        .addTo(liveMap)
        .bindPopup(
            "<strong>Trip Starting Point</strong>"
        );

    } else {

        startMarker.setLatLng(startPosition);

    }
}

function updateBoatMarker(
    position,
    latitude,
    longitude,
    speed,
    altitude,
    satellites,
    recordedAt
) {

    if (boatMarker === null) {

        boatMarker = L.marker(position)
            .addTo(liveMap);

    } else {

        boatMarker.setLatLng(position);

    }

    boatMarker.setPopupContent(
        "<strong>Current Boat Location</strong><br>" +
        "Latitude: " + latitude.toFixed(6) + "<br>" +
        "Longitude: " + longitude.toFixed(6) + "<br>" +
        "Speed: " + speed.toFixed(2) + " km/h<br>" +
        "Altitude: " + altitude.toFixed(2) + " m<br>" +
        "Satellites: " + satellites + "<br>" +
        "Updated: " + recordedAt
    );
}

/*
|--------------------------------------------------------------------------
| Load the saved route
|--------------------------------------------------------------------------
*/

async function loadExistingRoute() {

    const mapStatus =
        document.getElementById("mapStatus");

    mapStatus.textContent = "Loading saved route...";

    try {

        const response = await fetch(
            "../api/active_route.php",
            {
                cache: "no-store"
            }
        );

        if (!response.ok) {
            throw new Error(
                "Unable to load the saved route."
            );
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(
                data.message || "Unable to load route."
            );
        }

        if (
            !Array.isArray(data.route) ||
            data.route.length === 0
        ) {

            routeCoordinates = [];

            updateRoutePointCount();

            mapStatus.textContent =
                data.trip_id
                    ? "Active trip has no route points yet."
                    : "No active trip.";

            if (!data.trip_id) {
                setConnectionStatus(
                    "NO ACTIVE TRIP",
                    "No Active Trip"
                );
            }

            return;
        }

        routeCoordinates = data.route
            .map(function(point) {

                return [
                    parseFloat(point.latitude),
                    parseFloat(point.longitude)
                ];

            })
            .filter(function(point) {

                return isValidCoordinate(
                    point[0],
                    point[1]
                );

            });

        if (routeCoordinates.length === 0) {

            mapStatus.textContent =
                "No valid coordinates found.";

            return;
        }

        drawRoute();
        createStartMarker();

        if (
            routeLine &&
            routeCoordinates.length > 1
        ) {

            liveMap.fitBounds(
                routeLine.getBounds(),
                {
                    padding: [30, 30]
                }
            );

        } else {

            liveMap.setView(
                routeCoordinates[0],
                16
            );

        }

        firstValidLocation = false;

        mapStatus.textContent =
            "Saved route loaded.";

        console.log(
            "Saved route points:",
            routeCoordinates.length
        );

    } catch (error) {

        console.error(
            "Route loading error:",
            error
        );

        mapStatus.textContent =
            "Unable to load route.";

    }
}

/*
|--------------------------------------------------------------------------
| Retrieve the latest GPS location
|--------------------------------------------------------------------------
*/

async function updateTracking() {

    /*
     * Avoid overlapping requests if the API takes longer than
     * three seconds to respond.
     */
    if (trackingRequestRunning) {
        return;
    }

    trackingRequestRunning = true;

    const mapStatus =
        document.getElementById("mapStatus");

    try {

        const response = await fetch(
            "../api/latest.php",
            {
                cache: "no-store"
            }
        );

        if (!response.ok) {
            throw new Error(
                "Unable to retrieve GPS data."
            );
        }

        const data = await response.json();

        console.log(
            "Latest GPS data:",
            data
        );

        /*
         * These fields assume latest.php returns a flat JSON object:
         *
         * {
         *   "latitude": "...",
         *   "longitude": "...",
         *   "speed": "...",
         *   "altitude": "...",
         *   "satellites": "...",
         *   "gps_status": "...",
         *   "recorded_at": "..."
         * }
         */

        const latitude =
            parseFloat(data.latitude);

        const longitude =
            parseFloat(data.longitude);

        const speed =
            parseFloat(data.speed || 0);

        const altitude =
            parseFloat(data.altitude || 0);

        const satellites =
            parseInt(data.satellites || 0, 10);

        const gpsStatus =
            data.gps_status || "VALID";

        const recordedAt =
            data.recorded_at ||
            data.created_at ||
            data.timestamp ||
            "No update time";

        if (!isValidCoordinate(latitude, longitude)) {
            throw new Error(
                "The API returned invalid GPS coordinates."
            );
        }

        const currentPosition = [
            latitude,
            longitude
        ];

        /*
        |--------------------------------------------------------------------------
        | Update displayed values
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            "gpsStatus"
        ).textContent = gpsStatus;

        document.getElementById(
            "boatSpeed"
        ).textContent = speed.toFixed(2);

        document.getElementById(
            "satelliteCount"
        ).textContent = satellites;

        document.getElementById(
            "lastUpdate"
        ).textContent = recordedAt;

        document.getElementById(
            "latitude"
        ).textContent = latitude.toFixed(6);

        document.getElementById(
            "longitude"
        ).textContent = longitude.toFixed(6);

        document.getElementById(
            "altitude"
        ).textContent = altitude.toFixed(2);

        setConnectionStatus(
            "CONNECTED",
            "GPS Connected"
        );

        /*
        |--------------------------------------------------------------------------
        | Create or move the current boat marker
        |--------------------------------------------------------------------------
        */

        updateBoatMarker(
            currentPosition,
            latitude,
            longitude,
            speed,
            altitude,
            satellites,
            recordedAt
        );

        /*
        |--------------------------------------------------------------------------
        | Add the live position to the route
        |--------------------------------------------------------------------------
        */

        if (
            isNewRoutePosition(
                latitude,
                longitude
            )
        ) {

            routeCoordinates.push(
                currentPosition
            );

            /*
             * Prevent the browser from storing an unlimited
             * number of route points.
             */
            if (routeCoordinates.length > 2000) {
                routeCoordinates.shift();
            }

            drawRoute();
            createStartMarker();

        }

        /*
        |--------------------------------------------------------------------------
        | Center the map on the first valid live position
        |--------------------------------------------------------------------------
        */

        if (firstValidLocation) {

            liveMap.setView(
                currentPosition,
                16
            );

            firstValidLocation = false;

        }

        mapStatus.textContent =
            "Live tracking active";

    } catch (error) {

        console.error(
            "Live tracking error:",
            error
        );

        setConnectionStatus(
            "DISCONNECTED",
            "GPS Disconnected"
        );

        mapStatus.textContent =
            "Unable to retrieve live GPS data.";

    } finally {

        trackingRequestRunning = false;

    }
}

/*
|--------------------------------------------------------------------------
| Start tracking
|--------------------------------------------------------------------------
*/

async function initializeTracking() {

    await loadExistingRoute();

    await updateTracking();

    setInterval(
        updateTracking,
        3000
    );
}

initializeTracking();

/*
 * Fix Leaflet sizing when the page layout/sidebar finishes loading.
 */
setTimeout(function() {
    liveMap.invalidateSize();
}, 500);

</script>

<?php include("../includes/footer.php"); ?>