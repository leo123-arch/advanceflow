<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch profile data
$query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$user = mysqli_fetch_assoc($query);

// Default image detection
if (file_exists("uploads/default_avatar.jpg")) {
    $defaultImage = "default_avatar.jpg";
} elseif (file_exists("uploads/default_avatar.png")) {
    $defaultImage = "default_avatar.png";
} else {
    $defaultImage = "default_avatar.png"; // fallback
}

// Final selected image
$image = !empty($user['profile_image']) ? $user['profile_image'] : $defaultImage;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="./css/edit_profile.css">
</head>
<body>

<div class="main">
    <h1>Edit Profile</h1>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="form-box">

        <!-- Profile Image -->
        <div class="image-section">
            <img src="uploads/<?php echo $image; ?>" class="profile-pic">
            
            <label class="upload-btn">
                Upload Image
                <input type="file" name="profile_image" hidden>
            </label>
        </div>

        <label>Name</label>
        <input type="text" name="name" value="<?php echo $user['name']; ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo $user['email']; ?>" required>

        <label>Qualification</label>
        <input type="text" name="qualification" value="<?php echo $user['qualification']; ?>" required>

        <label>Experience (Years)</label>
        <input type="number" name="experience" value="<?php echo $user['experience']; ?>" required>

        <label>Department</label>
        <input type="text" name="department" value="<?php echo $user['department']; ?>" required>

        <button type="submit" class="btn">Update Profile</button>
    </form>

</div>

</body>
</html>
