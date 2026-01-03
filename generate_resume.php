<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty data
$f = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM faculty WHERE id='$faculty_id'"
));

// Research stats
$research = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT 
        SUM(category='Paper') AS papers,
        SUM(category='Book') AS books,
        SUM(category='Conference') AS conferences,
        SUM(category='Patent') AS patents
     FROM research_uploads
     WHERE faculty_id='$faculty_id'"
));

// API score
$api = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT api_score FROM promotion_applications 
     WHERE faculty_id='$faculty_id' 
     ORDER BY id DESC LIMIT 1"
));

// ---------------- AI SUMMARY LOGIC ----------------
$summary = "Dedicated academic professional with ";

if($research['papers'] >= 5){
    $summary .= "a strong research publication record, ";
}
if($research['patents'] > 0){
    $summary .= "innovation through patents, ";
}
// if($api['api_score'] >= 80){
//     $summary .= "and proven eligibility for academic promotion.";
// } else {
//     $summary .= "consistent academic and teaching contributions.";
// }
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
<p><b>Department:</b> <?php echo $f['department']; ?></p>
<p><b>Email:</b> <?php echo $f['email']; ?></p>

<hr>

<h2>Professional Summary</h2>
<p><?php echo $summary; ?></p>

<h2>Academic Profile</h2>
<ul>
    <li>Experience: <?php echo $f['experience']; ?> years</li>
    <li>API Score: <?php echo $api['api_score'] ?? 'N/A'; ?></li>
</ul>

<h2>Research Contributions</h2>
<ul>
    <li>Research Papers: <?php echo $research['papers']; ?></li>
    <li>Books / Chapters: <?php echo $research['books']; ?></li>
    <li>Conferences: <?php echo $research['conferences']; ?></li>
    <li>Patents: <?php echo $research['patents']; ?></li>
</ul>

<a href="resume_pdf.php" class="btn">Download PDF</a>

</div>

</body>
</html>
