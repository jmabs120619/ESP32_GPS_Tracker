<?php
session_start();
require_once("config/database.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND status='ACTIVE'");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard/index.php");
            exit();

        }else{

            $error = "Invalid password.";

        }

    }else{

        $error = "User not found.";

    }

}
?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>GPS Tracking System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{

background:#0d6efd;
height:100vh;
display:flex;
justify-content:center;
align-items:center;

}

.card{

width:400px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.25);

}

</style>

</head>

<body>

<div class="card p-4">

<h2 class="text-center mb-4">
GPS Tracking System
</h2>

<?php if($error!=""){ ?>

<div class="alert alert-danger">
<?= $error ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button class="btn btn-primary w-100">
Login
</button>

</form>

</div>

</body>
</html>