<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];
$title      = mysqli_real_escape_string($conn, $_POST['title']);
$category   = $_POST['category'];

$uploadDir = "uploads/research/";

// Create folder if missing
if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

$fileName = time() . "_" . basename($_FILES["file"]["name"]);
$targetFile = $uploadDir . $fileName;

$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Allowed types
$allowed = ['pdf','jpg','jpeg','png'];

if(!in_array($fileType, $allowed)){
    echo "<script>alert('Invalid file type. Only PDF, JPG, PNG allowed.'); window.history.back();</script>";
    exit;
}

if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)){
    
    $query = "INSERT INTO research_uploads (faculty_id, title, category, filename)
              VALUES ('$faculty_id', '$title', '$category', '$fileName')";

    mysqli_query($conn, $query);

    echo "<script>alert('Research Document Uploaded Successfully!'); window.location='view_research_uploads.php';</script>";
} 
else {
    echo "<script>alert('Failed to upload file.'); window.history.back();</script>";
}

?>
