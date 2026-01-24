<?php
session_start();
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$hours    = $_SESSION['teaching_hours'];
$feedback = $_SESSION['feedback_score'];
$mentor   = $_SESSION['mentorship'];
$score    = $_SESSION['cat1_score'];
$insights = $_SESSION['ai_insights'];

// Get faculty info
include "config.php";
$faculty_id = $_SESSION['faculty_id'];
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teaching Activities Analysis | Career Advancement System</title>
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
            --teaching-color: #3498db;
            --feedback-color: #2ecc71;
            --mentorship-color: #9b59b6;
            --success-color: #2ed573;
            --warning-color: #ffa502;
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
            color: var(--dark-text);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            display: inline-block;
            margin-top: 20px;
            font-size: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Score Card */
        .score-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(106, 17, 203, 0.3);
            position: relative;
            overflow: hidden;
        }

        .score-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .score-title {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .score-value {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .score-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Main Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* AI Insights Card */
        .insights-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--primary-color);
        }

        .insights-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
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
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .insights-subtitle {
            color: var(--gray-text);
            font-size: 1rem;
        }

        .insights-list {
            list-style: none;
        }

        .insights-list li {
            margin-bottom: 20px;
            padding-left: 35px;
            position: relative;
            line-height: 1.6;
        }

        .insights-list li:before {
            content: '💡';
            position: absolute;
            left: 0;
            top: 0;
            font-size: 1.2rem;
        }

        .insight-item {
            background: var(--light-bg);
            padding: 15px;
            border-radius: 10px;
            border-left: 3px solid var(--success-color);
            transition: all 0.3s;
        }

        .insight-item:hover {
            transform: translateX(5px);
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .chart-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .chart-subtitle {
            color: var(--gray-text);
            font-size: 1rem;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        /* Metrics Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-top: 4px solid transparent;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .metric-card.teaching {
            border-top-color: var(--teaching-color);
        }

        .metric-card.feedback {
            border-top-color: var(--feedback-color);
        }

        .metric-card.mentorship {
            border-top-color: var(--mentorship-color);
        }

        .metric-icon {
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

        .metric-card.teaching .metric-icon {
            background: linear-gradient(135deg, var(--teaching-color), #2980b9);
        }

        .metric-card.feedback .metric-icon {
            background: linear-gradient(135deg, var(--feedback-color), #27ae60);
        }

        .metric-card.mentorship .metric-icon {
            background: linear-gradient(135deg, var(--mentorship-color), #8e44ad);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .metric-card.teaching .metric-value { color: var(--teaching-color); }
        .metric-card.feedback .metric-value { color: var(--feedback-color); }
        .metric-card.mentorship .metric-value { color: var(--mentorship-color); }

        .metric-label {
            font-size: 1rem;
            color: var(--gray-text);
            font-weight: 600;
        }

        /* Performance Tags */
        .performance-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .tag {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .tag-excellent {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .tag-good {
            background: rgba(52, 152, 219, 0.1);
            color: var(--teaching-color);
            border: 1px solid rgba(52, 152, 219, 0.3);
        }

        .tag-improve {
            background: rgba(255, 165, 2, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 165, 2, 0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 50px;
        }

        .btn {
            padding: 16px 35px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--light-bg);
            transform: translateY(-3px);
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
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .score-card {
                padding: 25px;
            }
            
            .score-value {
                font-size: 3rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation for insights */
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .insights-list li {
            animation: slideIn 0.5s ease;
            animation-fill-mode: both;
        }

        .insights-list li:nth-child(1) { animation-delay: 0.1s; }
        .insights-list li:nth-child(2) { animation-delay: 0.2s; }
        .insights-list li:nth-child(3) { animation-delay: 0.3s; }
        .insights-list li:nth-child(4) { animation-delay: 0.4s; }
        .insights-list li:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-chalkboard-teacher"></i> Teaching Activities Analysis</h1>
            <p>Comprehensive breakdown of your teaching performance with AI-powered insights</p>
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Faculty: <?php echo htmlspecialchars($faculty['name']); ?>
                | Department: <?php echo htmlspecialchars($faculty['department']); ?>
            </div>
        </div>

        <!-- Score Card -->
        <div class="score-card">
            <div class="score-title">
                <i class="fas fa-medal"></i> Category I Teaching Score
            </div>
            <div class="score-value"><?php echo $score; ?></div>
            <div class="score-subtitle">
                Calculated based on teaching hours, student feedback, and mentorship activities
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-grid">
            <div class="metric-card teaching">
                <div class="metric-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="metric-value"><?php echo $hours; ?></div>
                <div class="metric-label">Teaching Hours / Week</div>
                <div class="performance-tags">
                    <?php if($hours >= 15): ?>
                        <span class="tag tag-excellent">Excellent</span>
                    <?php elseif($hours >= 10): ?>
                        <span class="tag tag-good">Good</span>
                    <?php else: ?>
                        <span class="tag tag-improve">Can Improve</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="metric-card feedback">
                <div class="metric-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="metric-value"><?php echo $feedback; ?>/10</div>
                <div class="metric-label">Student Feedback Score</div>
                <div class="performance-tags">
                    <?php if($feedback >= 8): ?>
                        <span class="tag tag-excellent">Excellent</span>
                    <?php elseif($feedback >= 6): ?>
                        <span class="tag tag-good">Good</span>
                    <?php else: ?>
                        <span class="tag tag-improve">Needs Attention</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="metric-card mentorship">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-value"><?php echo $mentor; ?></div>
                <div class="metric-label">Mentorship Activities</div>
                <div class="performance-tags">
                    <?php if($mentor >= 8): ?>
                        <span class="tag tag-excellent">Outstanding</span>
                    <?php elseif($mentor >= 5): ?>
                        <span class="tag tag-good">Satisfactory</span>
                    <?php else: ?>
                        <span class="tag tag-improve">Expand Activities</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- AI Insights -->
            <div class="insights-card">
                <div class="insights-header">
                    <div class="insights-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <div class="insights-title">AI Teaching Insights</div>
                        <div class="insights-subtitle">Personalized recommendations based on your performance</div>
                    </div>
                </div>
                
                <ul class="insights-list">
                    <?php foreach($insights as $tip): ?>
                    <li>
                        <div class="insight-item">
                            <?php echo htmlspecialchars($tip); ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Chart Container -->
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <div class="chart-title">Teaching Performance Overview</div>
                        <div class="chart-subtitle">Visual breakdown of your teaching metrics</div>
                    </div>
                </div>
                
                <div class="chart-wrapper">
                    <canvas id="teachingChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="teaching_form.php" class="btn btn-primary">
                <i class="fas fa-edit"></i> Update Teaching Data
            </a>
            <a href="faculty_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Teaching Analytics</p>
            <p>Analysis generated on: <?php echo date('F j, Y, h:i A'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Chart.js with improved styling
            new Chart(document.getElementById('teachingChart'), {
                type: 'bar',
                data: {
                    labels: [
                        'Teaching Hours / Week',
                        'Student Feedback (0-10)',
                        'Lesson Planning / Mentorship'
                    ],
                    datasets: [{
                        label: 'Teaching Activities Score',
                        data: [
                            <?php echo $hours; ?>,
                            <?php echo $feedback; ?>,
                            <?php echo $mentor; ?>
                        ],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.8)',
                            'rgba(46, 204, 113, 0.8)',
                            'rgba(155, 89, 182, 0.8)'
                        ],
                        borderColor: [
                            'rgb(52, 152, 219)',
                            'rgb(46, 204, 113)',
                            'rgb(155, 89, 182)'
                        ],
                        borderWidth: 2,
                        borderRadius: 8,
                        hoverBackgroundColor: [
                            'rgba(52, 152, 219, 1)',
                            'rgba(46, 204, 113, 1)',
                            'rgba(155, 89, 182, 1)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 25,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                                },
                                color: 'var(--gray-text)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 13,
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                                    weight: '600'
                                },
                                color: 'var(--dark-text)'
                            }
                        }
                    },
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
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });
            
            // Add score card animation
            const scoreCard = document.querySelector('.score-card');
            setTimeout(() => {
                scoreCard.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    scoreCard.style.transform = 'scale(1)';
                }, 300);
            }, 500);
            
            // Add hover effects to metric cards
            const metricCards = document.querySelectorAll('.metric-card');
            metricCards.forEach(card => {
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