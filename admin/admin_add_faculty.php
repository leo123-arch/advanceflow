<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Faculty</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="main">
    <h1>➕ Add New Faculty</h1>

    <form action="insert_faculty.php" method="POST" enctype="multipart/form-data" class="form-box">

        <label>Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Department</label>
        <input type="text" name="department" required>

        <label>Qualification</label>
        <input type="text" name="qualification" required>

        <label>Experience (Years)</label>
        <input type="number" name="experience" required>

        <label>Profile Image</label>
        <input type="file" name="profile_image" accept="image/*">

        <button type="submit" class="btn">Add Faculty</button>
    </form>
</div>

</body>
</html>
