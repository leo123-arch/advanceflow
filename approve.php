<?php
include "config.php";
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
}

$id = $_GET['id'];
$action = $_GET['action'];

$status = ($action == "approve") ? "Approved" : "Rejected";

// Update status in promotion_requests table
$update = "UPDATE promotion_requests SET status='$status' WHERE id='$id'";
mysqli_query($conn, $update);

echo "<script>
        alert('Application status updated successfully!');
        window.location='admin_promotion_request.php';
      </script>";
?>
