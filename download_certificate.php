<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$query = mysqli_query($conn, "SELECT name, role FROM faculty WHERE id='$faculty_id'");
$faculty_data = mysqli_fetch_assoc($query);
$faculty_name = $faculty_data['name'];

$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error_message = "";
$application_data = null;

if($app_id > 0) {
    $sql = "
        SELECT pr.*, f.name, f.department, f.id AS faculty_owner
        FROM promotion_requests pr
        LEFT JOIN faculty f ON pr.faculty_id = f.id
        WHERE pr.id = '$app_id'
    ";

    $res = mysqli_query($conn, $sql);

    if(mysqli_num_rows($res) == 0){
        $error_message = "Application not found!";
    } else {
        $data = mysqli_fetch_assoc($res);

        if($faculty_id != $data['faculty_owner']){
            $error_message = "Access denied. This is not your application.";
        } elseif($data['status'] != "Approved"){
            $error_message = "Certificate only available for approved applications.";
        } else {
            $application_data = $data;
        }
    }
}

// Approved certificates list
$approved_query = mysqli_query($conn, "
    SELECT pr.*, f.name, f.department 
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    WHERE pr.faculty_id = '$faculty_id' 
    AND pr.status = 'Approved'
    ORDER BY pr.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Download Certificate</title>
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>

<div class="main">
    <h1>Promotion Certificates</h1>

    <?php if($error_message): ?>
        <div style="color:red; margin-bottom:20px;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if($application_data): ?>
        <h2>Certificate Preview</h2>

        <p>
            <b>Name:</b> <?php echo htmlspecialchars($application_data['name']); ?><br>
            <b>Department:</b> <?php echo htmlspecialchars($application_data['department']); ?><br>
            <b>Promoted From:</b> <?php echo htmlspecialchars($application_data['current_position']); ?><br>
            <b>Promoted To:</b> <?php echo htmlspecialchars($application_data['promotion_to']); ?><br>
            <b>Date:</b> <?php echo date('d M Y', strtotime($application_data['created_at'])); ?>
        </p>

        <!-- ✅ FIXED LINK -->
        <a href="promotion_certificate.php?id=<?php echo $app_id; ?>" class="btn">
            Download Certificate (PDF)
        </a>

        <hr>
    <?php endif; ?>

    <h2>All Available Certificates</h2>

    <table border="1" cellpadding="10" style="width:100%;">
        <tr>
            <th>ID</th>
            <th>From</th>
            <th>To</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?php if(mysqli_num_rows($approved_query) > 0): ?>
            <?php while($cert = mysqli_fetch_assoc($approved_query)): ?>
                <tr>
                    <td><?php echo $cert['id']; ?></td>
                    <td><?php echo htmlspecialchars($cert['current_position']); ?></td>
                    <td><?php echo htmlspecialchars($cert['promotion_to']); ?></td>
                    <td><?php echo date('d M Y', strtotime($cert['created_at'])); ?></td>
                    <td>
                        <!-- ✅ FIXED LINK -->
                        <a href="promotion_certificate.php?id=<?php echo $cert['id']; ?>">
                            Preview / Download
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No approved promotions yet.</td>
            </tr>
        <?php endif; ?>

    </table>
</div>

</body>
</html>
