<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];
$result = mysqli_query($conn, "SELECT * FROM promotion_requests WHERE faculty_id='$faculty_id'");
?>

<!DOCTYPE html>
<html>
<head>
<title>Your Promotion Applications</title>
<link rel="stylesheet" href="./css/promotion_status.css">
</head>
<body>

<div class="main">
<h1>Your Promotion Status</h1>

<table class="status-table" border="1" cellpadding="12" style="width:90%; margin:auto;">
    <tr>
        <th>ID</th>
        <th>Current Position</th>
        <th>Applied For</th>
        <th>Document</th>
        <th>Remarks</th>
        <th>Status</th>
        <th>Date</th>
        <th>Certificate</th> <!-- Added -->
    </tr>

    <?php
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            echo "<tr>
                <td>".$row['id']."</td>
                <td>".$row['current_position']."</td>
                <td>".$row['promotion_to']."</td>
                <td><a href='uploads/".$row['document']."' target='_blank'>View</a></td>
                <td>".$row['remarks']."</td>
                <td>".$row['status']."</td>
                <td>".$row['created_at']."</td>
                <td>";

            // Certificate button only if Approved
            if($row['status'] == "Approved"){
                echo "<a class='btn' href='promotion_certificate.php?id=".$row['id']."'>Download</a>";
            } else {
                echo "—";
            }

            echo "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='8' style='text-align:center;'>No applications found.</td></tr>";
    }
    ?>
</table>

</div>

</body>
</html>
