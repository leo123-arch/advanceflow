<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

/* ----------------------
   CATEGORY I: TEACHING
------------------------*/
$teaching_hours = $_POST['teaching_hours'];
$feedback       = $_POST['feedback'];
$mentorship     = $_POST['mentorship'];

$cat1 = ($teaching_hours * 2) + ($feedback * 5) + ($mentorship * 3);


/* ----------------------
   CATEGORY II: CO-CURRICULAR
------------------------*/
$extension = $_POST['extension'];
$pdp       = $_POST['pdp'];
$admin     = $_POST['admin'];

$cat2 = ($extension * 4) + ($pdp * 6) + ($admin * 5);


/* ----------------------
   CATEGORY III: RESEARCH
------------------------*/
$papers     = $_POST['papers'];
$books      = $_POST['books'];
$conference = $_POST['conference'];
$patents    = $_POST['patents'];
$projects   = $_POST['projects'];

$cat3 = ($papers * 10) + ($books * 15) + ($conference * 8) + ($patents * 20) + ($projects * 25);


/* ----------------------
   TOTAL API SCORE
------------------------*/
$total_score = $cat1 + $cat2 + $cat3;


/* ----------------------
   ELIGIBILITY CHECK
------------------------*/
$eligibility = ($total_score >= 80) ? "Eligible for Promotion" : "Not Eligible";


/* ----------------------
   SAVE RESULTS (Advanced API Score)
------------------------*/
mysqli_query($conn,
    "INSERT INTO promotion_applications (faculty_id, api_score, cat1, cat2, cat3, status)
     VALUES('$faculty_id', '$total_score', '$cat1', '$cat2', '$cat3', 'Pending')");


?>

<!DOCTYPE html>
<html>
<head>
    <title>API Score Result</title>
   <link rel="stylesheet" href="./css/calculate_advanced_api.css">
</head>
<body>

<div class="main">
    <h1>Advanced API Score Result</h1>

    <div class="card">
        <h2>Total API Score: <?php echo $total_score; ?></h2>
        <h3><?php echo $eligibility; ?></h3>
    </div>

    <h2>Category Breakdown</h2>
    <ul>
        <li><b>Category I:</b> <?php echo $cat1; ?></li>
        <li><b>Category II:</b> <?php echo $cat2; ?></li>
        <li><b>Category III:</b> <?php echo $cat3; ?></li>
    </ul>

    <a class="btn" href="faculty_dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
