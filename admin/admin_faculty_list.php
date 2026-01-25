<?php
session_start();
include "config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM faculty ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty List</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_faculty_list.php" class="active">Faculty List</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>👩‍🏫 Faculty List</h1>

    <table class="table">
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Qualification</th>
            <th>Experience</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($result)) { 
            $image = !empty($row['profile_image']) ? $row['profile_image'] : "default_avatar.png";
        ?>
        <tr>
            <td>
                <img src="uploads/<?php echo $image; ?>" width="50" height="50" style="border-radius:50%;">
            </td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['department']); ?></td>
            <td><?php echo htmlspecialchars($row['qualification']); ?></td>
            <td><?php echo htmlspecialchars($row['experience']); ?> yrs</td>
            <td>
                <a class="btn" href="view_faculty.php?id=<?php echo $row['id']; ?>">View</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>
