<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch all applications by this faculty
$query = mysqli_query($conn, 
    "SELECT * FROM promotion_applications WHERE faculty_id='$faculty_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Status</title>
    <link rel="stylesheet" href="./css/application_status.css">
</head>
<body>

<div class="sidebar">
    <h2>Career System</h2>
    <a href="faculty_dashboard.php">Dashboard</a>
    <a href="apply_promotion.php">Apply for Promotion</a>
    <a href="advanced_api_form.php">Advanced API Score</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>Your Application Status</h1>

    <table class="status-table">
        <tr>
            <th>Application ID</th>
            <th>API Score</th>
            <th>Category I</th>
            <th>Category II</th>
            <th>Category III</th>
            <th>Status</th>
            <th>Date Applied</th>
        </tr>

        <?php 
        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){ 
        ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['api_score']; ?></td>
            <td><?php echo $row['cat1']; ?></td>
            <td><?php echo $row['cat2']; ?></td>
            <td><?php echo $row['cat3']; ?></td>

            <td>
                <?php 
                if($row['status'] == "Pending") echo "<span class='pending'>Pending</span>";
                if($row['status'] == "Approved") echo "<span class='approved'>Approved</span>";
                if($row['status'] == "Rejected") echo "<span class='rejected'>Rejected</span>";
                ?>
            </td>

            <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='7' style='text-align:center;'>No applications submitted yet.</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>
