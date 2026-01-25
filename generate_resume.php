<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Faculty basic info
$f = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM faculty WHERE id='$faculty_id'"
));

// Research count
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM research_uploads WHERE faculty_id='$faculty_id'"
));

// Teaching score
$t = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(teaching_hours) as hours FROM teaching_activities WHERE faculty_id='$faculty_id'"
));
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Generated Resume</title>
    <link rel="stylesheet" href="./css/resume.css">
</head>
<body>

<div class="resume">
    <h1><?php echo $f['name']; ?></h1>
    <p><?php echo $f['email']; ?> | <?php echo $f['department']; ?></p>

    <h2>Professional Summary</h2>
    <p>
        Dedicated faculty member with <?php echo $f['experience']; ?> years of teaching experience,
        actively involved in research and academic development.
    </p>

    <h2>Teaching Experience</h2>
    <p>Total Teaching Hours Recorded: <?php echo $t['hours'] ?? 0; ?></p>

    <h2>Research Contributions</h2>
    <p>Total Research Documents Uploaded: <?php echo $r['total']; ?></p>

    <h2>Qualification</h2>
    <p><?php echo $f['qualification']; ?></p>

    <br>
    <a href="resume_pdf.php" class="btn">Download Resume PDF</a>
</div>

</body>
</html>
