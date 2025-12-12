<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Career System</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<div class="container">

    <form class="login-box" method="POST">
        <h2>Faculty Registration</h2>

        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group">
            <label>Qualification</label>
            <input type="text" name="qualification" required>
        </div>

        <div class="input-group">
            <label>Experience (years)</label>
            <input type="number" name="experience" required>
        </div>

        <div class="input-group">
            <label>Role</label>
            <input type="text" name="role" required>
        </div>

          <div class="input-group">
            <label>Department</label>
            <input type="text" name="department" required>
        </div>

        <button type="submit" name="register" class="btn">Register</button>

        <p class="note">Already have an account?
            <a href="login.php">Login</a>
        </p>
    </form>

</div>

<?php
if(isset($_POST['register'])){
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // You can encrypt later
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $role = $_POST['role'];
    $department = $_POST['department'];

    // Check if email exists
    $check = mysqli_query($conn, "SELECT * FROM faculty WHERE email='$email'");
    
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Email already registered!');</script>";
    } else {
        // Insert into database
        $query = "INSERT INTO faculty (name, email, password, qualification, experience,role,department)
                  VALUES ('$name', '$email', '$password', '$qualification', '$experience','$role','$department')";

        if(mysqli_query($conn, $query)){
            echo "<script>alert('Registration Successful!'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error! Please try again.');</script>";
        }
    }
}
?>

</body>
</html>
