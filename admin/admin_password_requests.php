<?php
include "config.php";
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
}

if(isset($_GET['msg'])){
    echo "<script>alert('Email sent successfully!');</script>";
}

$requests = mysqli_query($conn, 
    "SELECT pr.*, f.name, f.email 
     FROM password_requests pr 
     LEFT JOIN faculty f ON pr.faculty_id = f.id 
     ORDER BY pr.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Requests</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_promotion_request.php">Promotion applications</a>
    <a href="admin_password_requests.php">Password Requests</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>Password Change Requests</h1>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Faculty</th>
            <th>Email</th>
            <th>New Password</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($r = mysqli_fetch_assoc($requests)){ ?>
        <tr>
            <td><?php echo $r['id']; ?></td>
            <td><?php echo $r['name']; ?></td>
            <td><?php echo $r['email']; ?></td>
            <td><?php echo $r['new_password']; ?></td>
            <td><?php echo $r['status']; ?></td>
            <td>
                <?php if($r['status'] == "Pending"){ ?>
                    <a class="btn approve" href="process_password.php?id=<?php echo $r['id']; ?>&action=approve">Approve</a>
                    <a class="btn reject" href="process_password.php?id=<?php echo $r['id']; ?>&action=reject">Reject</a>
                <?php } else { echo "—"; } ?>
            </td>
        </tr>
        <?php } ?>

    </table>
</div>
</body>
</html>
