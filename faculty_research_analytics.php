<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Get faculty info
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

/* ==============================
   COUNT RESEARCH BY CATEGORY
============================== */
$data = [
    "Paper" => 0,
    "Book" => 0,
    "Conference" => 0,
    "Patent" => 0,
    "Project" => 0
];

$result = mysqli_query($conn,
    "SELECT category, COUNT(*) AS total
     FROM research_uploads
     WHERE faculty_id='$faculty_id'
     GROUP BY category"
);

while($row = mysqli_fetch_assoc($result)){
    if(isset($data[$row['category']])){
        $data[$row['category']] = (int)$row['total'];
    }
}

// Calculate total research items
$total_research = array_sum($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Analytics Dashboard | Career Advancement System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --paper-color: #3498db;
            --book-color: #9b59b6;
            --conference-color: #2ecc71;
            --patent-color: #e67e22;
            --project-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px 0;
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 25px 30px;
            border-bottom: 2px solid var(--light-bg);
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-dept {
            opacity: 0.9;
            font-size: 0.85rem;
        }

        .sidebar-nav {
            padding: 0 25px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: var(--gray-text);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .sidebar-nav a:hover {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        }

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 25px;
            margin-top: 30px;
            border-top: 2px solid var(--light-bg);
            text-align: center;
            color: var(--gray-text);
            font-size: 0.85rem;
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            max-width: 700px;
            line-height: 1.6;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
        }

        .summary-card.paper::before { background: linear-gradient(to right, var(--paper-color), #2980b9); }
        .summary-card.book::before { background: linear-gradient(to right, var(--book-color), #8e44ad); }
        .summary-card.conference::before { background: linear-gradient(to right, var(--conference-color), #27ae60); }
        .summary-card.patent::before { background: linear-gradient(to right, var(--patent-color), #d35400); }
        .summary-card.project::before { background: linear-gradient(to right, var(--project-color), #c0392b); }
        .summary-card.total::before { background: linear-gradient(to right, var(--primary-color), var(--secondary-color)); }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: white;
        }

        .summary-card.paper .card-icon { background: linear-gradient(135deg, var(--paper-color), #2980b9); }
        .summary-card.book .card-icon { background: linear-gradient(135deg, var(--book-color), #8e44ad); }
        .summary-card.conference .card-icon { background: linear-gradient(135deg, var(--conference-color), #27ae60); }
        .summary-card.patent .card-icon { background: linear-gradient(135deg, var(--patent-color), #d35400); }
        .summary-card.project .card-icon { background: linear-gradient(135deg, var(--project-color), #c0392b); }
        .summary-card.total .card-icon { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }

        .card-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .summary-card.paper .card-value { color: var(--paper-color); }
        .summary-card.book .card-value { color: var(--book-color); }
        .summary-card.conference .card-value { color: var(--conference-color); }
        .summary-card.patent .card-value { color: var(--patent-color); }
        .summary-card.project .card-value { color: var(--project-color); }
        .summary-card.total .card-value { color: var(--primary-color); }

        .card-label {
            font-size: 1rem;
            color: var(--gray-text);
            font-weight: 600;
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .chart-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chart-actions {
            display: flex;
            gap: 10px;
        }

        .chart-btn {
            padding: 10px 20px;
            border-radius: 8px;
            background: var(--light-bg);
            color: var(--dark-text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-btn:hover {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin-top: 20px;
        }

        /* Insights Section */
        .insights-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .insights-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .insights-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }

        .insights-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .insights-subtitle {
            color: var(--gray-text);
            font-size: 1rem;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .insight-card {
            background: var(--light-bg);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid var(--primary-color);
        }

        .insight-card h4 {
            font-size: 1.2rem;
            color: var(--dark-text);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .insight-card p {
            color: var(--gray-text);
            line-height: 1.6;
        }

        /* Distribution Chart */
        .distribution-chart {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        @media (max-width: 1024px) {
            .distribution-chart {
                grid-template-columns: 1fr;
            }
        }

        .pie-chart-container,
        .bar-chart-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .chart-title-small {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 50px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }
            
            .main {
                margin-left: 250px;
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            
            .main {
                margin-left: 0;
                padding: 20px;
            }
            
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .chart-container {
                padding: 25px;
            }
        }

        @media (max-width: 480px) {
            .main {
                padding: 15px;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .insights-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> Career System</h2>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($faculty['name']); ?></div>
                <div class="user-dept"><?php echo htmlspecialchars($faculty['department']); ?></div>
            </div>
        </div>

        <div class="sidebar-nav">
            <!-- ONLY THESE 4 MENU ITEMS -->
            <a href="faculty_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="view_research_uploads.php">
                <i class="fas fa-eye"></i> View Uploaded Research
            </a>
            <a href="faculty_research_analytics.php" class="active">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="sidebar-footer">
            <p>© <?php echo date('Y'); ?> Career System</p>
            <p>Analytics Dashboard v2.0</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Research Analytics Dashboard</h1>
            <p>Comprehensive analysis of your research portfolio with visual insights and statistics</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card total">
                <div class="card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="card-value"><?php echo $total_research; ?></div>
                <div class="card-label">Total Research Items</div>
            </div>
            
            <div class="summary-card paper">
                <div class="card-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="card-value"><?php echo $data['Paper']; ?></div>
                <div class="card-label">Research Papers</div>
            </div>
            
            <div class="summary-card book">
                <div class="card-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="card-value"><?php echo $data['Book']; ?></div>
                <div class="card-label">Books / Chapters</div>
            </div>
            
            <div class="summary-card conference">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-value"><?php echo $data['Conference']; ?></div>
                <div class="card-label">Conferences</div>
            </div>
            
            <div class="summary-card patent">
                <div class="card-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="card-value"><?php echo $data['Patent']; ?></div>
                <div class="card-label">Patents</div>
            </div>
            
            <div class="summary-card project">
                <div class="card-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="card-value"><?php echo $data['Project']; ?></div>
                <div class="card-label">Projects</div>
            </div>
        </div>

        <!-- Main Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fas fa-chart-bar"></i> Research Distribution Overview
                </div>
                <div class="chart-actions">
                    <button class="chart-btn" id="barChartBtn">
                        <i class="fas fa-chart-bar"></i> Bar Chart
                    </button>
                    <button class="chart-btn" id="pieChartBtn">
                        <i class="fas fa-chart-pie"></i> Pie Chart
                    </button>
                </div>
            </div>
            
            <div class="chart-wrapper">
                <canvas id="researchChart"></canvas>
            </div>
        </div>

        <!-- Insights Section -->
        <div class="insights-section">
            <div class="insights-header">
                <div class="insights-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <div class="insights-title">AI-Generated Insights</div>
                    <div class="insights-subtitle">Personalized recommendations based on your research portfolio</div>
                </div>
            </div>
            
            <div class="insights-grid">
                <div class="insight-card">
                    <h4><i class="fas fa-trophy"></i> Strength Analysis</h4>
                    <p>
                        <?php 
                        $maxCategory = array_search(max($data), $data);
                        echo "Your strongest area is <strong>" . htmlspecialchars($maxCategory) . "</strong> with " . $data[$maxCategory] . " publications.";
                        ?>
                    </p>
                </div>
                
                <div class="insight-card">
                    <h4><i class="fas fa-bullseye"></i> Growth Opportunities</h4>
                    <p>
                        <?php 
                        $minCategory = array_search(min($data), $data);
                        if($data[$minCategory] == 0) {
                            echo "Consider expanding into <strong>" . htmlspecialchars($minCategory) . "</strong> to diversify your research portfolio.";
                        } else {
                            echo "You have good coverage across all research categories. Keep up the balanced approach!";
                        }
                        ?>
                    </p>
                </div>
                
                <div class="insight-card">
                    <h4><i class="fas fa-chart-line"></i> Performance Metrics</h4>
                    <p>
                        <?php 
                        $avgPerCategory = $total_research / count($data);
                        echo "Average of <strong>" . round($avgPerCategory, 1) . "</strong> publications per category. " . 
                             ($avgPerCategory >= 2 ? "Great distribution!" : "Consider increasing output in weaker areas.");
                        ?>
                    </p>
                </div>
                
                <div class="insight-card">
                    <h4><i class="fas fa-rocket"></i> Next Steps</h4>
                    <p>
                        Based on your current portfolio, focus on high-impact publications and 
                        consider collaborative projects to expand your research network and citation impact.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Research Analytics Dashboard</p>
            <p>Analysis generated on: <?php echo date('F j, Y, h:i A'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart data
            const chartData = {
                labels: ['Papers', 'Books', 'Conferences', 'Patents', 'Projects'],
                datasets: [{
                    label: 'Research Contributions',
                    data: [
                        <?php echo $data['Paper']; ?>,
                        <?php echo $data['Book']; ?>,
                        <?php echo $data['Conference']; ?>,
                        <?php echo $data['Patent']; ?>,
                        <?php echo $data['Project']; ?>
                    ],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.8)',
                        'rgba(155, 89, 182, 0.8)',
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(230, 126, 34, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    borderColor: [
                        'rgb(52, 152, 219)',
                        'rgb(155, 89, 182)',
                        'rgb(46, 204, 113)',
                        'rgb(230, 126, 34)',
                        'rgb(231, 76, 60)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(230, 126, 34, 1)',
                        'rgba(231, 76, 60, 1)'
                    ]
                }]
            };

            // Chart options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                        },
                        bodyFont: {
                            size: 13,
                            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                        },
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 12,
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: 'var(--gray-text)'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 13,
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                                weight: '600'
                            },
                            color: 'var(--dark-text)'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            };

            // Initialize Chart.js
            let researchChart = new Chart(document.getElementById('researchChart'), {
                type: 'bar',
                data: chartData,
                options: chartOptions
            });

            // Chart type switching
            const barChartBtn = document.getElementById('barChartBtn');
            const pieChartBtn = document.getElementById('pieChartBtn');

            barChartBtn.addEventListener('click', function() {
                researchChart.destroy();
                researchChart = new Chart(document.getElementById('researchChart'), {
                    type: 'bar',
                    data: chartData,
                    options: chartOptions
                });
                
                barChartBtn.style.background = 'rgba(106, 17, 203, 0.1)';
                barChartBtn.style.color = 'var(--primary-color)';
                pieChartBtn.style.background = 'var(--light-bg)';
                pieChartBtn.style.color = 'var(--dark-text)';
            });

            pieChartBtn.addEventListener('click', function() {
                researchChart.destroy();
                researchChart = new Chart(document.getElementById('researchChart'), {
                    type: 'pie',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        size: 13,
                                        family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                                    },
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 14,
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                                },
                                bodyFont: {
                                    size: 13,
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                                },
                                padding: 12,
                                cornerRadius: 8
                            }
                        }
                    }
                });
                
                pieChartBtn.style.background = 'rgba(106, 17, 203, 0.1)';
                pieChartBtn.style.color = 'var(--primary-color)';
                barChartBtn.style.background = 'var(--light-bg)';
                barChartBtn.style.color = 'var(--dark-text)';
            });

            // Add animation to summary cards
            const summaryCards = document.querySelectorAll('.summary-card');
            summaryCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects
            summaryCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Auto-scroll to top
            window.scrollTo(0, 0);
        });
    </script>
</body>
</html>