<?php
include "config.php";
session_start();

// Allow admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
}

// Fetch statistics from promotion_requests table
$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests"))['c'];
$pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Pending'"))['c'];
$approved  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Approved'"))['c'];
$rejected  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Rejected'"))['c'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_promotion_request.php">Promotion Applications</a>
    <a href="admin_password_requests.php">Password Requests</a>
    <a href="admin_research_uploads.php">Research Documents</a>
    <a href="admin_faculty_ranking.php">Faculty Ranking</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <h1>Admin Dashboard</h1>

    <!-- Dashboard Cards -->
    <div class="cards">

        <div class="card">
            <h3>Total Applications</h3>
            <p><?php echo $total; ?></p>
        </div>

        <div class="card">
            <h3>Pending</h3>
            <p><?php echo $pending; ?></p>
        </div>

        <div class="card">
            <h3>Approved</h3>
            <p><?php echo $approved; ?></p>
        </div>

        <div class="card">
            <h3>Rejected</h3>
            <p><?php echo $rejected; ?></p>
        </div>

    </div>

</div>

</body>
</html>
