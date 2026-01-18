<?php
session_start();
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit;
}

$hours    = $_SESSION['teaching_hours'];
$feedback = $_SESSION['feedback_score'];
$mentor   = $_SESSION['mentorship'];
$score    = $_SESSION['cat1_score'];
$insights = $_SESSION['ai_insights'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teaching Activities Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>

<div class="main">
    <h1>Teaching Activities Summary</h1>

    <div class="card">
        <h2>Category I Score: <?php echo $score; ?></h2>
    </div>

    <h3>🤖 AI Teaching Insights</h3>
    <ul>
        <?php foreach($insights as $tip){ ?>
            <li><?php echo $tip; ?></li>
        <?php } ?>
    </ul>

    <!-- GRAPH -->
    <div style="width:70%; margin:40px auto;">
        <canvas id="teachingChart"></canvas>
    </div>
</div>

<script>
new Chart(document.getElementById('teachingChart'), {
    type: 'bar',
    data: {
        labels: [
            'Teaching Hours / Week',
            'Student Feedback (0-10)',
            'Lesson Planning / Mentorship'
        ],
        datasets: [{
            label: 'Teaching Activities',
            data: [
                <?php echo $hours; ?>,
                <?php echo $feedback; ?>,
                <?php echo $mentor; ?>
            ],
            backgroundColor: ['#3498db', '#1abc9c', '#9b59b6']
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                suggestedMax: 25
            }
        }
    }
});
</script>

</body>
</html>
