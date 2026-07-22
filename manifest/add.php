<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

// Check if there is an active trip
$tripQuery = mysqli_query($conn,"
SELECT *
FROM trips
WHERE status='ON GOING'
LIMIT 1
");

$trip = mysqli_fetch_assoc($tripQuery);

if(!$trip){
?>

<div class="container-fluid">

    <div class="alert alert-danger">

        <h4>No Active Trip</h4>

        <p>Please start a trip first before adding passengers.</p>

        <a href="../dashboard/index.php" class="btn btn-primary">
            Back to Dashboard
        </a>

    </div>

</div>

<?php
include("../includes/footer.php");
exit();
}

$trip_id = $trip['id'];

// Get all passengers not yet added to this trip
$sql = "
SELECT *
FROM passengers
WHERE id NOT IN
(
    SELECT passenger_id
    FROM trip_passengers
    WHERE trip_id='$trip_id'
)
ORDER BY fullname ASC
";

$result = mysqli_query($conn,$sql);

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>
            <i class="bi bi-person-plus-fill"></i>
            Add Passenger to Current Trip
        </h3>

        <a href="index.php" class="btn btn-secondary">
            Back
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

                    <?= htmlspecialchars($trip['trip_no']); ?>

                </div>

                <div class="col-md-3">

                    <strong>Captain</strong><br>

                    <?= htmlspecialchars($trip['captain']); ?>

                </div>

                <div class="col-md-3">

                    <strong>Departure</strong><br>

                    <?= htmlspecialchars($trip['departure']); ?>

                </div>

                <div class="col-md-3">

                    <strong>Destination</strong><br>

                    <?= htmlspecialchars($trip['destination']); ?>

                </div>

            </div>

        </div>

    </div>

    <div class="card mb-3">

        <div class="card-body">

            <input
                type="text"
                id="searchPassenger"
                class="form-control"
                placeholder="Search Passenger...">

        </div>

    </div>

    <div class="card">

        <div class="card-header">

            Registered Passengers

        </div>

        <div class="card-body">

            <table
                class="table table-striped table-hover"
                id="passengerTable">

                <thead class="table-dark">

                    <tr>

                        <th>Name</th>

                        <th>Passenger Type</th>

                        <th>Gender</th>

                        <th>Age</th>

                        <th width="120">Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($result)>0){

                    while($row=mysqli_fetch_assoc($result)){

                ?>

                    <tr>

                        <td><?= htmlspecialchars($row['fullname']); ?></td>

                        <td><?= htmlspecialchars($row['passenger_type']); ?></td>

                        <td><?= htmlspecialchars($row['gender']); ?></td>

                        <td><?= $row['age']; ?></td>

                        <td>

                            <a
                                href="save.php?trip=<?= $trip_id ?>&passenger=<?= $row['id']; ?>"
                                class="btn btn-success btn-sm">

                                <i class="bi bi-plus-circle"></i>

                                Add

                            </a>

                        </td>

                    </tr>

                <?php

                    }

                }else{

                ?>

                    <tr>

                        <td colspan="5" class="text-center">

                            <strong>No available passengers.</strong>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>

document.getElementById("searchPassenger").addEventListener("keyup", function(){

    let value = this.value.toLowerCase();

    let rows = document.querySelectorAll("#passengerTable tbody tr");

    rows.forEach(function(row){

        row.style.display = row.innerText.toLowerCase().includes(value)
            ? ""
            : "none";

    });

});

</script>

<?php include("../includes/footer.php"); ?>