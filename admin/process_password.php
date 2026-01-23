<?php
include "config.php";
include "email_config.php";

$id = $_GET['id'];
$action = $_GET['action'];

// Fetch request details
$request = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM password_requests WHERE id='$id'"));

$faculty_id = $request['faculty_id'];
$newpass = $request['new_password'];

// Fetch faculty info
$faculty = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM faculty WHERE id='$faculty_id'"));

$email = $faculty['email'];
$name = $faculty['name'];

if($action == "approve"){
    // Update password
    mysqli_query($conn,
        "UPDATE faculty SET password='$newpass' WHERE id='$faculty_id'");

    // Mark request approved
    mysqli_query($conn,
        "UPDATE password_requests SET status='Approved' WHERE id='$id'");

    $subject = "Password Reset Approved";
    $message = "Hello $name,\n\nYour password reset request has been approved.\nYour new password: $newpass\n\nLogin and change it immediately.\n\nRegards,\nAdmin Team";
}
else {
    // Reject request
    mysqli_query($conn,
        "UPDATE password_requests SET status='Rejected' WHERE id='$id'");

    $subject = "Password Reset Rejected";
    $message = "Hello $name,\n\nYour password reset request has been rejected.\nPlease contact admin if needed.\n\nRegards,\nAdmin Team";
}

// Send email using PHPMailer
sendMailToFaculty($email, $subject, $message);

header("Location: admin_password_requests.php?msg=done");
?>
