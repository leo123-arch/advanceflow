<?php
session_start();
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teaching Activities</title>
    <link rel="stylesheet" href="./css/teaching_form.css">
</head>
<body>

<div class="main">
<h1>Category I – Teaching Activities</h1>

<form method="POST" action="teaching_process.php" class="form-box">

    <label>Teaching Hours per Week</label>
    <input type="number" name="teaching_hours" required>

    <label>Student Feedback Score (0–10)</label>
    <input type="number" name="feedback_score" min="0" max="10" required>

    <label>Lesson Planning / Mentorship</label>
    <input type="number" name="mentorship" required>

    <button type="submit" class="btn">Submit</button>
</form>

</div>
</body>
</html>
