<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

mysqli_query($conn,"
UPDATE trips
SET
arrival_time = NOW(),
status='COMPLETED'
WHERE status='ON GOING'
");

header("Location: ../dashboard/index.php?trip_ended=1");
exit();