<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM trip_passengers WHERE id=?");

$stmt->bind_param("i",$id);

$stmt->execute();

header("Location: index.php?removed=1");

exit();

?>