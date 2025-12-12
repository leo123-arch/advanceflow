<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

$result = mysqli_query($conn, 
    "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Research Documents</title>
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Career System</h2>
    <a href="faculty_dashboard.php">Dashboard</a>
    <a href="faculty_research_upload.php">Upload Research</a>
    <a href="view_research_uploads.php">View Research</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>Your Uploaded Research Documents</h1>

    <table class="status-table" border="1" cellpadding="12">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>File</th>
            <th>Date</th>
        </tr>

        <?php
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                echo "<tr>
                    <td>".$row['id']."</td>
                    <td>".$row['title']."</td>
                    <td>".$row['category']."</td>
                    <td><a href='uploads/research/".$row['filename']."' target='_blank'>View</a></td>
                    <td>".$row['uploaded_at']."</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No research documents uploaded yet.</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>
