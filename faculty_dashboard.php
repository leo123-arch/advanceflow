<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$query = mysqli_query($conn, "SELECT name FROM faculty WHERE id='$faculty_id'");
$user = mysqli_fetch_assoc($query);
$name = $user['name'];
?>


<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Career System</h2>
    <a href="faculty_dashboard.php">Dashboard</a>
    <a href="apply_promotion.php">Apply for Promotion</a>
    <a href="advanced_api_form.php" class="btn">Advanced API Score</a>
    <a href="faculty_research_upload.php">Research Contributions</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Welcome Faculty <?php echo $name;?></h1>
    </div>

    <div class="cards">

        <div class="card">
            <h3>Apply for Promotion</h3>
            <p>Submit new promotion request</p>
            <a href="apply_promotion.php" class="btn">Apply</a>
        </div>

        <div class="card">
            <h3>Check Status</h3>
            <p>View your application status</p>
            <a href="promotion_status.php" class="btn">View</a>
        </div>

        <div class="card">
            <h3>Your Profile</h3>
            <p>Edit personal details</p>
            <a href="edit_profile.php" class="btn">Open</a>
        </div>

        <div class="card">
    <h3>Research Contributions</h3>
    <p>Add & View Research Work</p>
    <a class="btn" href="faculty_research_upload.php">Open</a>
</div>

<div class="card">
    <h3>AI Chatbot</h3>
    <p>Ask questions about promotion & API score</p>
    <a class="btn" href="faculty_chatbot.php">Open</a>
</div>


    </div>
</div>

</body>
</html>
