<?php
session_start();
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Research Documents</title>
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Career System</h2>
    <a href="faculty_dashboard.php">Dashboard</a>
    <a href="faculty_research_upload.php">Upload Research</a>
    <a href="view_research_uploads.php">View Uploaded Research</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
    <h1>Upload Research Document</h1>

    <form action="save_research_upload.php" method="POST" enctype="multipart/form-data" class="form-box">

        <label>Title of Document</label>
        <input type="text" name="title" required>

        <label>Select Category</label>
        <select name="category" required>
            <option value="Research Paper">Research Paper</option>
            <option value="Book / Chapter">Book / Chapter</option>
            <option value="Conference Presentation">Conference Presentation</option>
            <option value="Patent">Patent</option>
            <option value="Research Project">Research Project</option>
        </select>

        <label>Upload File (PDF / JPG / PNG)</label>
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>

        <button type="submit" class="btn">Upload Document</button>
    </form>

</div>

</body>
</html>
