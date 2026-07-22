<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

$sql = "
SELECT
    t.*,
    (
        SELECT COUNT(*)
        FROM trip_passengers tp
        WHERE tp.trip_id = t.id
    ) AS total_passengers
FROM trips t
ORDER BY t.id DESC
";

$result = mysqli_query($conn, $sql);

?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3>Trip History</h3>

    </div>

    <div class="card shadow">

        <div class="card-body">

            <table class="table table-bordered table-striped table-hover">

                <thead class="table-dark">

                    <tr>

                        <th>Trip No</th>
                        <th>Captain</th>
                        <th>Departure</th>
                        <th>Destination</th>
                        <th>Passengers</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="120">Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php while($row = mysqli_fetch_assoc($result)){ ?>

                    <tr>

                        <td><?= htmlspecialchars($row['trip_no']); ?></td>

                        <td><?= htmlspecialchars($row['captain']); ?></td>

                        <td><?= htmlspecialchars($row['departure']); ?></td>

                        <td><?= htmlspecialchars($row['destination']); ?></td>

                        <td><?= $row['total_passengers']; ?></td>

                        <td>

                            <?php if($row['status']=="ON GOING"){ ?>

                                <span class="badge bg-success">

                                    ON GOING

                                </span>

                            <?php } else { ?>

                                <span class="badge bg-secondary">

                                    COMPLETED

                                </span>

                            <?php } ?>

                        </td>

                        <td>

                            <?= date("M d, Y",strtotime($row['created_at'])); ?>

                        </td>

                        <td>

                            <a
                                href="view.php?id=<?= $row['id']; ?>"
                                class="btn btn-primary btn-sm">

                                View

                            </a>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>