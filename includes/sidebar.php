<div class="sidebar">

<h3 class="text-center mt-3">
GPS Tracker
</h3>

<hr>

<a href="../dashboard/index.php">
<i class="bi bi-speedometer2"></i>
Dashboard
</a>

<a href="../passengers/index.php">
    <i class="bi bi-people-fill"></i>
    Passenger Management
</a>

<li class="nav-item">
    <a class="nav-link" href="../manifest/index.php">
        <i class="bi bi-person-check-fill"></i>
        Passenger Manifest
    </a>
</li>

<li class="nav-item">
    <a href="../trip_history/index.php" class="nav-link">
        <i class="bi bi-clock-history"></i>
        <span>Trip History</span>
    </a>
</li>

<a href="/gps_tracker/live_tracking/index.php">
<i class="bi bi-geo-alt-fill"></i>
Live Tracking
</a>

<a href="#">
<i class="bi bi-people-fill"></i>
Passenger Monitoring
</a>

<a href="#">
<i class="bi bi-car-front-fill"></i>
Vehicles
</a>

<a href="#">
<i class="bi bi-graph-up"></i>
Analytics
</a>

<?php if($_SESSION['role']=="SUPER_ADMIN"){ ?>

<a href="../users/index.php">
<i class="bi bi-person-fill-gear"></i>
User Management
</a>

<a href="#">
<i class="bi bi-gear-fill"></i>
Settings
</a>

<?php } ?>

<a href="../logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>