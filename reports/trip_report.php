<?php

require_once("../includes/auth.php");
require_once("../config/database.php");

/* Validate trip ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../trip_history/index.php");
    exit();
}

$trip_id = (int) $_GET['id'];

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Trip Report</title>

    <link
        href="../assets/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            margin: 30px;
        }

        @media print {

            .no-print {
                display: none !important;
            }

            body {
                margin: 20px;
            }

        }

    </style>

</head>

<body>

<div class="mb-3 no-print">

    <button
        type="button"
        class="btn btn-primary"
        onclick="printReport();">

        🖨 Print Report

    </button>

    <a
        href="../trip_history/view.php?id=<?= $trip_id; ?>"
        class="btn btn-secondary">

        ← Back

    </a>

</div>

<!-- REPORT CONTENT HERE -->

<script>

function printReport() {
    window.print();
}

</script>

</body>

</html>