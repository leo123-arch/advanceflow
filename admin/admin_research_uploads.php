<?php
session_start();
include "config.php";

// Allow only admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
}

// Fetch uploads with faculty name
$query = mysqli_query($conn, "
    SELECT r.*, f.name AS faculty_name
    FROM research_uploads r
    LEFT JOIN faculty f ON r.faculty_id = f.id
    ORDER BY r.uploaded_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Research Uploads</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_research_uploads.php" class="active">Research Documents</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

<h1>Faculty Uploaded Documents</h1>

<table class="table">
    <tr>
        <th>ID</th>
        <th>Faculty Name</th>
        <th>Title</th>
        <th>Category</th>
        <th>Document</th>
        <th>Date</th>
    </tr>

    <?php
    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['faculty_name']}</td>
                <td>{$row['title']}</td>
                <td>{$row['category']}</td>
                <td>
                    <a href='../uploads/research/{$row['filename']}' target='_blank'>
                        View / Download
                    </a>
                </td>
                <td>{$row['uploaded_at']}</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No documents uploaded.</td></tr>";
    }
    ?>
</table>

</div>

</body>
</html>
