<?php include "config.php"; session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Career System</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<div class="container">

    <form class="login-box" method="POST">
        <h2>Faculty Login</h2>

        <div class="input-group">
            <label>Email</label>
            <input type="text" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" name="login" class="btn">Login</button>

        <a href="register.php" class="btn1">Register</a>

    <p class="note">
    <a href="forgot_password.php">Forgot Password?</a>
    </p>

        <p class="note">Admin can also login here</p>
    </form>

</div>

<?php
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM faculty WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1){
        $row = mysqli_fetch_assoc($result);

        $_SESSION['faculty_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];

        if($row['role'] == "admin"){
            header("Location: admin_dashboard.php");
        } else {
            header("Location: faculty_dashboard.php");
        }
    } else {
        echo "<script>alert('Invalid Email or Password!');</script>";
    }
}
?>

</body>
</html>
