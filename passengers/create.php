<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

if(isset($_POST['save']))
{

    $fullname           = trim($_POST['fullname']);
    $gender             = $_POST['gender'];
    $age                = $_POST['age'];
    $passenger_type     = $_POST['passenger_type'];
    $address            = trim($_POST['address']);
    $contact_no         = trim($_POST['contact_no']);
    $emergency_contact  = trim($_POST['emergency_contact']);
    $emergency_number   = trim($_POST['emergency_number']);

    $stmt = $conn->prepare("

        INSERT INTO passengers
        (
            fullname,
            gender,
            age,
            passenger_type,
            address,
            contact_no,
            emergency_contact,
            emergency_number
        )

        VALUES
        (
            ?,?,?,?,?,?,?,?
        )

    ");

    $stmt->bind_param(
        "ssisssss",
        $fullname,
        $gender,
        $age,
        $passenger_type,
        $address,
        $contact_no,
        $emergency_contact,
        $emergency_number
    );

    if($stmt->execute())
    {
        header("Location: index.php?success=1");
        exit();
    }
    else
    {
        $error = "Unable to save passenger.";
    }

}

include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/navbar.php");

?>

<div class="card">

<div class="card-header">

<h3>Register Passenger</h3>

</div>

<div class="card-body">

<?php
if(isset($error))
{
?>

<div class="alert alert-danger">

<?= $error ?>

</div>

<?php
}
?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label>Full Name</label>

<input
type="text"
name="fullname"
class="form-control"
required>

</div>

<div class="col-md-3 mb-3">

<label>Gender</label>

<select
name="gender"
class="form-control"
required>

<option value="">Select</option>

<option value="MALE">Male</option>

<option value="FEMALE">Female</option>

</select>

</div>

<div class="col-md-3 mb-3">

<label>Age</label>

<input
type="number"
name="age"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Passenger Type</label>

<select
name="passenger_type"
class="form-control">

<option value="ADULT">Adult</option>

<option value="CHILD">Child</option>

<option value="SENIOR">Senior Citizen</option>

<option value="PWD">PWD</option>

</select>

</div>

<div class="col-md-8 mb-3">

<label>Address</label>

<input
type="text"
name="address"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label>Contact Number</label>

<input
type="text"
name="contact_no"
class="form-control" limit ="11>

</div>

<div class="col-md-6 mb-3">

<label>Emergency Contact Name</label>

<input
type="text"
name="emergency_contact"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label>Emergency Contact Number</label>

<input5
type="text"
name="emergency_number"
class="form-control" limit="11">

</div>

</div>

<button
type="submit"
name="save"
class="btn btn-success">

Save Passenger

</button>

<a
href="index.php"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

<?php

include("../includes/footer.php");

?>