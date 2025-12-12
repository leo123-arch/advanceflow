<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id       = $_SESSION['faculty_id'];
$current_position = $_POST['current_position'];
$promotion_to     = $_POST['promotion_to'];
$remarks          = $_POST['remarks'];

// Handle Document Upload
$doc_name = "";

if(!empty($_FILES['docs']['name'])){
    $doc_name = time() . "_" . $_FILES['docs']['name'];
    $target = "uploads/" . $doc_name;
    move_uploaded_file($_FILES['docs']['tmp_name'], $target);
}

// Insert into new table
$query = "INSERT INTO promotion_requests 
        (faculty_id, current_position, promotion_to, document, remarks, status)
        VALUES 
        ('$faculty_id', '$current_position', '$promotion_to', '$doc_name', '$remarks', 'Pending')";

if(mysqli_query($conn, $query)){
    echo "<script>
            alert('Promotion Application Submitted Successfully!');
            window.location='promotion_status.php';
          </script>";
} else {
    echo "<script>
            alert('Error submitting application!');
            window.location='apply_promotion.php';
          </script>";
}
?>
