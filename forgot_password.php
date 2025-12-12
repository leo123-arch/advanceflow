<?php include "config.php"; session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<div class="container">
    <form class="login-box" method="POST">
        <h2>Forgot Password</h2>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>New Password</label>
            <input type="password" name="newpass" required>
        </div>

        <button type="submit" name="submit" class="btn">Submit Request</button>
        <p class="note">Password will be updated only after admin approval.</p>
    </form>
</div>

<?php
if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $newpass = $_POST['newpass'];

    // Check if faculty exists
    $q = mysqli_query($conn, "SELECT * FROM faculty WHERE email='$email'");

    if(mysqli_num_rows($q) == 1){
        $row = mysqli_fetch_assoc($q);
        $faculty_id = $row['id'];

        // Insert request
        $insert = "INSERT INTO password_requests (faculty_id, new_password)
                   VALUES ('$faculty_id', '$newpass')";

        mysqli_query($conn, $insert);

        echo "<script>alert('Password change request submitted! Wait for admin approval.'); 
              window.location='login.php';</script>";
    } 
    else {
        echo "<script>alert('Email not found!');</script>";
    }
}
?>
</body>
</html>
