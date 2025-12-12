<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

$name         = $_POST['name'];
$email        = $_POST['email'];
$qualification = $_POST['qualification'];
$experience   = $_POST['experience'];
$department   = $_POST['department'];

// Handle Image Upload
if (!empty($_FILES['profile_image']['name'])) {

    $image_name = time() . "_" . $_FILES['profile_image']['name'];
    $target = "uploads/" . $image_name;

    move_uploaded_file($_FILES['profile_image']['tmp_name'], $target);

    // Save image to DB
    mysqli_query($conn, 
        "UPDATE faculty SET profile_image='$image_name' WHERE id='$faculty_id'");
}

// Update other fields
$update = "UPDATE faculty SET 
            name='$name',
            email='$email',
            qualification='$qualification',
            experience='$experience',
            department='$department'
           WHERE id='$faculty_id'";

mysqli_query($conn, $update);

echo "<script>alert('Profile Updated Successfully!'); 
window.location='faculty_dashboard.php';
</script>";
?>
