<?php
session_start();
include "config.php";

// Only admin allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
}

// Get all promotion requests with faculty name
$result = mysqli_query($conn, "
    SELECT pr.*, f.name 
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    ORDER BY pr.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Promotion Applications</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_promotion_request.php">Promotion Applications</a>
    <a href="admin_password_requests.php">Password Requests</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <h1>Promotion Applications</h1>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Faculty Name</th>
            <th>Current Position</th>
            <th>Applied For</th>
            <th>Document</th>
            <th>Remarks</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($result)){ ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['current_position']; ?></td>
            <td><?php echo $row['promotion_to']; ?></td>

            <td>
                <?php if($row['document']) { ?>
                    <a href="uploads/<?php echo $row['document']; ?>" target="_blank">View</a>
                <?php } else { echo "No File"; } ?>
            </td>

            <td><?php echo $row['remarks']; ?></td>

            <td>
                <span class="badge <?php echo strtolower($row['status']); ?>">
                    <?php echo $row['status']; ?>
                </span>
            </td>

            <td>
                <?php if($row['status'] == "Pending") { ?>
                    <a class="btn approve" 
                       href="approve.php?id=<?php echo $row['id']; ?>&action=approve">Approve</a>

                    <a class="btn reject" 
                       href="approve.php?id=<?php echo $row['id']; ?>&action=reject">Reject</a>
                <?php } else { ?>
                    <span>—</span>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>
