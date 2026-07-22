<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

// Check for active trip
$tripQuery = mysqli_query($conn,"
SELECT *
FROM trips
WHERE status='ON GOING'
LIMIT 1
");

$trip = mysqli_fetch_assoc($tripQuery);

?>

<div class="container-fluid">

<?php if(isset($_GET['added'])){ ?>

<div class="alert alert-success alert-dismissible fade show">
    Passenger successfully added to the trip.
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>

<?php if(isset($_GET['removed'])){ ?>

<div class="alert alert-danger alert-dismissible fade show">
    Passenger removed from the trip.
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>

<?php if(isset($_GET['exists'])){ ?>

<div class="alert alert-warning alert-dismissible fade show">
    Passenger is already on this trip.
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>

<?php

if(!$trip){

?>

<div class="alert alert-warning">

<h4>No Active Trip</h4>

<p>Please start a trip first.</p>

<a href="../dashboard/index.php" class="btn btn-primary">

Back to Dashboard

</a>

</div>

<?php

include("../includes/footer.php");

exit();

}

?>

<div class="d-flex justify-content-between align-items-center mb-3">

<h3>

Passenger Manifest

</h3>

<a href="add.php" class="btn btn-success">

<i class="bi bi-person-plus-fill"></i>

Add Passenger

</a>

</div>

<div class="card mb-3">

<div class="card-header bg-primary text-white">

Current Trip

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">

<strong>Trip No</strong><br>

<?= $trip['trip_no']; ?>

</div>

<div class="col-md-3">

<strong>Captain</strong><br>

<?= $trip['captain']; ?>

</div>

<div class="col-md-3">

<strong>Departure</strong><br>

<?= $trip['departure']; ?>

</div>

<div class="col-md-3">

<strong>Destination</strong><br>

<?= $trip['destination']; ?>

</div>

</div>

</div>

</div>

<?php

$query = mysqli_query($conn,"

SELECT

trip_passengers.id,

trip_passengers.time_boarded,

trip_passengers.status,

passengers.fullname,

passengers.gender,

passengers.passenger_type

FROM trip_passengers

INNER JOIN passengers

ON passengers.id=trip_passengers.passenger_id

WHERE trip_passengers.trip_id=".$trip['id']."

ORDER BY trip_passengers.time_boarded ASC

");

?>

<div class="card">

<div class="card-header">

Passenger List

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>#</th>

<th>Passenger</th>

<th>Type</th>

<th>Gender</th>

<th>Boarded Time</th>

<th>Status</th>

<th width="120">

Action

</th>

</tr>

</thead>

<tbody>

<?php

$count = 1;

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

?>

<tr>

<td><?= $count++; ?></td>

<td><?= htmlspecialchars($row['fullname']); ?></td>

<td><?= htmlspecialchars($row['passenger_type']); ?></td>

<td><?= htmlspecialchars($row['gender']); ?></td>

<td><?= date("h:i A",strtotime($row['time_boarded'])); ?></td>

<td>

<span class="badge bg-success">

<?= $row['status']; ?>

</span>

</td>

<td>

<a
href="remove.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Remove passenger from this trip?')">

Remove

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center">

No passengers added.

</td>

</tr>

<?php } ?>

</tbody>

<tfoot>

<tr>

<th colspan="6">

Total Passengers

</th>

<th>

<?= mysqli_num_rows($query); ?>

</th>

</tr>

</tfoot>

</table>

</div>

</div>

</div>

<?php include("../includes/footer.php"); ?>