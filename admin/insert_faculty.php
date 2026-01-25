<?php
session_start();
include "config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit;
}

$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAUL);
$department = mysqli_real_escape_string($conn, $_POST['department']);
$qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
$experience = intval($_POST['experience']);

$imageName = "default_avatar.png";

if(!empty($_FILES['profile_image']['name'])){
    $targetDir = "uploads/";
    $imageName = time() . "_" . basename($_FILES["profile_image"]["name"]);
    move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetDir . $imageName);
}

$query = "INSERT INTO faculty (name, email, password, department, qualification, experience, profile_image, role)
          VALUES ('$name', '$email', '$password', '$department', '$qualification', '$experience', '$imageName', 'faculty')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Faculty Added Successfully'); window.location='admin_faculty_list.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
