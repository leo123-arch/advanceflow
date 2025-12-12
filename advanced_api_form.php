<?php
session_start();
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Advanced API Score Calculator</title>
    <link rel="stylesheet" href="./css/advanced_api_form.css">

</head>
<body>

<div class="main">
    <h1>Advanced API Score (UGC Format)</h1>

    <form action="calculate_advanced_api.php" method="POST" class="form-box">

        <h2>Category I: Teaching Activities</h2>

        <label>Teaching Hours per Week</label>
        <input type="number" name="teaching_hours" required>

        <label>Student Feedback Score (0-10)</label>
        <input type="number" name="feedback" min="0" max="10" required>

        <label>Lesson Planning / Mentorship</label>
        <input type="number" name="mentorship" min="0" required>


        <h2>Category II: Co-Curricular Activities</h2>

        <label>Extension Activities (NSS/NCC/Clubs)</label>
        <input type="number" name="extension" required>

        <label>Professional Development Programs Attended</label>
        <input type="number" name="pdp" required>

        <label>Administrative Responsibilities (Coordinator/Convener)</label>
        <input type="number" name="admin" required>


        <h2>Category III: Research & Academic Contributions</h2>

        <label>Research Papers Published</label>
        <input type="number" name="papers" required>

        <label>Books/Chapters Published</label>
        <input type="number" name="books" required>

        <label>Conference Presentations</label>
        <input type="number" name="conference" required>

        <label>Patents Filed/Granted</label>
        <input type="number" name="patents" required>

        <label>Research Projects Completed</label>
        <input type="number" name="projects" required>

        <button type="submit" class="btn">Calculate API Score</button>
    </form>
</div>

</body>
</html>
