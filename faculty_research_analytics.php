<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}

$faculty_id = $_SESSION['faculty_id'];

// Count research by category
$data = [
    "Paper" => 0,
    "Book" => 0,
    "Conference" => 0,
    "Project" => 0
];

$result = mysqli_query($conn,
    "SELECT category, COUNT(*) AS total
     FROM research_uploads
     WHERE faculty_id='$faculty_id'
     GROUP BY category"
);

while($row = mysqli_fetch_assoc($result)){
    $data[$row['category']] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Research Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>
<div class="sidebar">
    <h2>Career System</h2>
    <a href="faculty_dashboard.php">Dashboard</a>
    <a href="advanced_api_form.php" class="btn">Advanced API Score</a>
    <a href="generate_resume.php">Resume Builder</a>
    <a href="faculty_research_analytics.php">Analytics</a>
    <a href="logout.php">Logout</a>
</div>


</div>

<div class="main">
    <h1>📊 Research Analytics Dashboard</h1>

    <!-- Summary cards -->
    <div class="cards">
        <?php foreach($data as $type => $count){ ?>
            <div class="card">
                <h3><?php echo $type; ?></h3>
                <p><?php echo $count; ?></p>
            </div>
        <?php } ?>
    </div>

    <!-- Graph Section -->
    <div style="width:80%; margin:40px auto;">
        <canvas id="researchChart"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('researchChart').getContext('2d');

const researchChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Papers', 'Books', 'Conferences', 'Patents', 'Projects'],
        datasets: [{
            label: 'Research Contributions',
            data: [
                <?php echo $data['Paper']; ?>,
                <?php echo $data['Book']; ?>,
                <?php echo $data['Conference']; ?>,
                <?php echo $data['Project']; ?>
            ],
            backgroundColor: [
                '#3498db',
                '#9b59b6',
                '#1abc9c',
                '#e67e22',
                '#e74c3c'
            ],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

</body>
</html>
