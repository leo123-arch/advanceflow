<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch user details
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Promotion</title>
    <link rel="stylesheet" href="./css/apply_promotion.css">
</head>
<body>

<div class="main">
    <h1>Apply for Promotion</h1>

    <form action="process_promotion.php" method="POST" enctype="multipart/form-data" class="form-box">

        <label>Faculty Name</label>
        <input type="text" value="<?php echo $user['name']; ?>" disabled>

        <label>Department</label>
        <input type="text" value="<?php echo $user['department']; ?>" disabled>

        <label>Current Position</label>
        <select name="current_position" required>
            <option>Assistant Professor</option>
            <option>Associate Professor</option>
            <option>Professor</option>
        </select>

        <label>Applying For</label>
        <select name="promotion_to" required>
            <option>Associate Professor</option>
            <option>Professor</option>
            <option>Senior Professor</option>
        </select>

        <label>Upload Supporting Documents (PDF Only)</label>
        <input type="file" name="docs" accept="application/pdf" required>

        <label>Remarks (Optional)</label>
        <textarea name="remarks" placeholder="Enter any note for admin..."></textarea>

        <button type="submit" class="btn">Submit Application</button>

    </form>
</div>

</body>
</html>
