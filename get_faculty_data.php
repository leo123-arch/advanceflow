<?php
// get_faculty_data.php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$faculty_id = $_SESSION['faculty_id'];

// Get faculty details
$f = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT name, email, department, experience, qualification FROM faculty WHERE id='$faculty_id'"
));

// Get research count
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM research_uploads WHERE faculty_id='$faculty_id'"
));

// Get teaching hours
$t = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(teaching_hours) as hours FROM teaching_activities WHERE faculty_id='$faculty_id'"
));

// Prepare data array
$data = [
    'name' => $f['name'] ?? 'Faculty Member',
    'email' => $f['email'] ?? 'faculty@example.com',
    'department' => $f['department'] ?? 'Department',
    'experience' => $f['experience'] ?? '0',
    'qualification' => $f['qualification'] ?? 'Not specified',
    'teaching_hours' => $t['hours'] ?? 0,
    'research_count' => $r['total'] ?? 0
];

// Return as JSON
header('Content-Type: application/json');
echo json_encode($data);
exit;
?>