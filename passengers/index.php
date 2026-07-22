<?php
if(isset($_GET['success']))
{
?>

<div class="alert alert-success">

Passenger registered successfully.

</div>

<?php
}
?>

<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>
<?php

$sql = "SELECT * FROM passengers ORDER BY fullname ASC";

$result = mysqli_query($conn, $sql);

?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h3>Passenger Management</h3>

    <a href="create.php" class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Register Passenger

    </a>

</div>

<div class="card">

<div class="card-body">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Full Name</th>

<th>Gender</th>

<th>Age</th>

<th>Passenger Type</th>

<th>Contact</th>

<th width="180">Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['fullname']); ?></td>

<td><?= htmlspecialchars($row['gender']); ?></td>

<td><?= $row['age']; ?></td>

<td><?= htmlspecialchars($row['passenger_type']); ?></td>

<td><?= htmlspecialchars($row['contact_no']); ?></td>

<td>

<a
href="edit.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a
href="delete.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this passenger?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<?php

include("../includes/footer.php");

?>