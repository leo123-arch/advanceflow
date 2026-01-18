<?php
session_start();
include "config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit;
}

/* Fetch faculty data */
$query = mysqli_query($conn, "
    SELECT 
        f.id,
        f.name,
        f.department,

        COALESCE(SUM(t.cat1_score),0) AS teaching_score,
        COALESCE(COUNT(r.id),0) AS research_count,
        COALESCE(AVG(p.api_score),0) AS api_score

    FROM faculty f
    LEFT JOIN teaching_activities t ON f.id = t.faculty_id
    LEFT JOIN research_uploads r ON f.id = r.faculty_id
    LEFT JOIN promotion_applications p ON f.id = p.faculty_id

    GROUP BY f.id
");

$facultyRanks = [];

while($row = mysqli_fetch_assoc($query)){

    $ai_score =
        ($row['teaching_score'] * 0.3) +
        ($row['research_count'] * 10 * 0.4) +
        ($row['api_score'] * 0.3);

    $row['ai_score'] = round($ai_score, 2);
    $facultyRanks[] = $row;
}

/* Sort by AI score (DESC) */
usort($facultyRanks, function($a, $b){
    return $b['ai_score'] <=> $a['ai_score'];
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Faculty Ranking</title>
    <link rel="stylesheet" href="../css/admin_faculty_ranking.css">
</head>
<body>

<div class="main">
<h1>🏆 AI Faculty Ranking System</h1>

<table class="table">
    <tr>
        <th>Rank</th>
        <th>Faculty Name</th>
        <th>Department</th>
        <th>Teaching</th>
        <th>Research</th>
        <th>API</th>
        <th>AI Score</th>
    </tr>

<?php
$rank = 1;
foreach($facultyRanks as $f){
?>
<tr>
    <td><?php echo $rank++; ?></td>
    <td><?php echo htmlspecialchars($f['name']); ?></td>
    <td><?php echo htmlspecialchars($f['department']); ?></td>
    <td><?php echo $f['teaching_score']; ?></td>
    <td><?php echo $f['research_count']; ?></td>
    <td><?php echo round($f['api_score'],2); ?></td>
    <td><b><?php echo $f['ai_score']; ?></b></td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>
