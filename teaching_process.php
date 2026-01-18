<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit;
}

$faculty_id = $_SESSION['faculty_id'];

$hours      = (int)$_POST['teaching_hours'];
$feedback   = (int)$_POST['feedback_score'];
$mentorship = (int)$_POST['mentorship'];

/* CATEGORY I SCORE */
$cat1_score = ($hours * 2) + ($feedback * 5) + ($mentorship * 3);

/* SAVE TO DATABASE */
mysqli_query($conn,
    "INSERT INTO teaching_activities
     (faculty_id, teaching_hours, feedback_score, mentorship, cat1_score)
     VALUES ('$faculty_id','$hours','$feedback','$mentorship','$cat1_score')"
);

/* AI INSIGHTS */
$ai_insights = [];

if($hours < 12) $ai_insights[] = "Increase weekly teaching hours.";
if($feedback < 7) $ai_insights[] = "Improve teaching methods to increase student feedback.";
if($mentorship < 2) $ai_insights[] = "Take up more lesson planning or mentorship activities.";
if(empty($ai_insights)) $ai_insights[] = "Excellent teaching performance.";

/* STORE VALUES FOR GRAPH (IMPORTANT FIX) */
$_SESSION['teaching_hours'] = $hours;
$_SESSION['feedback_score'] = $feedback;
$_SESSION['mentorship']     = $mentorship;
$_SESSION['cat1_score']     = $cat1_score;
$_SESSION['ai_insights']    = $ai_insights;

header("Location: teaching_result.php");
exit;
