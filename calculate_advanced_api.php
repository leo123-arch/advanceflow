<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$faculty_query = mysqli_query($conn, "SELECT name, email FROM faculty WHERE id='$faculty_id'");
$faculty_data = mysqli_fetch_assoc($faculty_query);
$faculty_name = $faculty_data['name'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: advanced_api_form.php");
    exit();
}

/* ----------------------
   CATEGORY I: TEACHING
------------------------*/
$teaching_hours = isset($_POST['teaching_hours']) ? intval($_POST['teaching_hours']) : 0;
$feedback       = isset($_POST['feedback']) ? floatval($_POST['feedback']) : 0;
$mentorship     = isset($_POST['mentorship']) ? intval($_POST['mentorship']) : 0;

// Calculate with detailed breakdown
$teaching_score = $teaching_hours * 2;
$feedback_score = $feedback * 5;
$mentorship_score = $mentorship * 3;
$cat1 = $teaching_score + $feedback_score + $mentorship_score;


/* ----------------------
   CATEGORY II: CO-CURRICULAR
------------------------*/
$extension = isset($_POST['extension']) ? intval($_POST['extension']) : 0;
$pdp       = isset($_POST['pdp']) ? intval($_POST['pdp']) : 0;
$admin     = isset($_POST['admin']) ? intval($_POST['admin']) : 0;

$extension_score = $extension * 4;
$pdp_score = $pdp * 6;
$admin_score = $admin * 5;
$cat2 = $extension_score + $pdp_score + $admin_score;


/* ----------------------
   CATEGORY III: RESEARCH
------------------------*/
$papers     = isset($_POST['papers']) ? intval($_POST['papers']) : 0;
$books      = isset($_POST['books']) ? intval($_POST['books']) : 0;
$conference = isset($_POST['conference']) ? intval($_POST['conference']) : 0;
$patents    = isset($_POST['patents']) ? intval($_POST['patents']) : 0;
$projects   = isset($_POST['projects']) ? intval($_POST['projects']) : 0;

$papers_score = $papers * 10;
$books_score = $books * 15;
$conference_score = $conference * 8;
$patents_score = $patents * 20;
$projects_score = $projects * 25;
$cat3 = $papers_score + $books_score + $conference_score + $patents_score + $projects_score;


/* ----------------------
   TOTAL API SCORE
------------------------*/
$total_score = $cat1 + $cat2 + $cat3;

// Calculate percentages
$total_for_percent = $cat1 + $cat2 + $cat3;
if ($total_for_percent > 0) {
    $cat1_percent = ($cat1 / $total_for_percent) * 100;
    $cat2_percent = ($cat2 / $total_for_percent) * 100;
    $cat3_percent = ($cat3 / $total_for_percent) * 100;
} else {
    $cat1_percent = $cat2_percent = $cat3_percent = 0;
}


/* ----------------------
   ELIGIBILITY CHECK
------------------------*/
$eligibility = ($total_score >= 80) ? "Eligible for Promotion" : "Not Eligible";
$eligibility_class = ($total_score >= 80) ? "eligible" : "not-eligible";
$promotion_status = ($total_score >= 80) ? "Ready to Apply" : "Needs Improvement";

// Determine promotion level
if ($total_score >= 90) {
    $promotion_level = "Excellent - Strong Candidate";
    $promotion_color = "#2ed573";
} elseif ($total_score >= 80) {
    $promotion_level = "Good - Eligible";
    $promotion_color = "#3498db";
} elseif ($total_score >= 70) {
    $promotion_level = "Average - Close to Eligibility";
    $promotion_color = "#ffa502";
} elseif ($total_score >= 60) {
    $promotion_level = "Below Average - Needs Work";
    $promotion_color = "#e67e22";
} else {
    $promotion_level = "Poor - Significant Improvement Needed";
    $promotion_color = "#ff4757";
}


/* ----------------------
   SAVE RESULTS (Advanced API Score)
------------------------*/
// First, let's check if the table exists and what columns it has
$table_check = mysqli_query($conn, "SHOW COLUMNS FROM promotion_applications");
$columns = [];
while($col = mysqli_fetch_assoc($table_check)) {
    $columns[] = $col['Field'];
}

// Check if 'created_at' column exists
if (in_array('created_at', $columns)) {
    $insert_query = mysqli_query($conn,
        "INSERT INTO promotion_applications (faculty_id, api_score, cat1, cat2, cat3, status, created_at)
         VALUES('$faculty_id', '$total_score', '$cat1', '$cat2', '$cat3', 'Pending', NOW())");
} 
// Check if 'application_date' column exists
elseif (in_array('application_date', $columns)) {
    $insert_query = mysqli_query($conn,
        "INSERT INTO promotion_applications (faculty_id, api_score, cat1, cat2, cat3, status, application_date)
         VALUES('$faculty_id', '$total_score', '$cat1', '$cat2', '$cat3', 'Pending', NOW())");
}
// If neither exists, don't include the date column
else {
    $insert_query = mysqli_query($conn,
        "INSERT INTO promotion_applications (faculty_id, api_score, cat1, cat2, cat3, status)
         VALUES('$faculty_id', '$total_score', '$cat1', '$cat2', '$cat3', 'Pending')");
}

// Get the inserted ID if query was successful
if ($insert_query) {
    $application_id = mysqli_insert_id($conn);
} else {
    $application_id = null;
    // Log error but don't stop execution
    error_log("Failed to insert API score: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Score Results | Career Advancement System</title>
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
            --success-color: #2ed573;
            --warning-color: #ffa502;
            --danger-color: #ff4757;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-dark: #333;
            --text-gray: #666;
            --border-color: #e1e5ee;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header p {
            color: var(--text-gray);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .user-info {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-top: 15px;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.2);
        }

        /* Main Results Card */
        .results-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .results-container {
                grid-template-columns: 1fr;
            }
        }

        .main-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .score-display {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--border-color);
        }

        .total-score {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .eligibility-badge {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .eligibility-badge.eligible {
            background: linear-gradient(to right, var(--success-color), #27ae60);
            color: white;
        }

        .eligibility-badge.not-eligible {
            background: linear-gradient(to right, var(--danger-color), #c0392b);
            color: white;
        }

        .promotion-level {
            font-size: 1.3rem;
            font-weight: 600;
            margin-top: 10px;
        }

        /* Progress Bars */
        .category-breakdown {
            margin-bottom: 40px;
        }

        .category-item {
            margin-bottom: 25px;
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .category-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .category-score {
            font-weight: 700;
            color: var(--primary-color);
        }

        .progress-container {
            height: 12px;
            background-color: var(--border-color);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 100%;
            border-radius: 6px;
            transition: width 1.5s ease-in-out;
            width: 0%;
        }

        .cat1-bar { background: linear-gradient(to right, #6a11cb, #8a2be2); }
        .cat2-bar { background: linear-gradient(to right, #2575fc, #3498db); }
        .cat3-bar { background: linear-gradient(to right, #2ed573, #27ae60); }

        .percentage {
            font-size: 0.9rem;
            color: var(--text-gray);
            text-align: right;
        }

        /* Stats Card */
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(106, 17, 203, 0.2);
            animation: slideUp 0.8s ease;
        }

        .stats-card h3 {
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-align: center;
            color: white;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-label {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .stat-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Detailed Breakdown */
        .detailed-breakdown {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .detailed-breakdown h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 1.8rem;
            text-align: center;
        }

        .category-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .detail-card {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            border-left: 4px solid;
        }

        .detail-card.cat1 { border-left-color: #6a11cb; }
        .detail-card.cat2 { border-left-color: #2575fc; }
        .detail-card.cat3 { border-left-color: #2ed573; }

        .detail-card h4 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-item {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--border-color);
        }

        .detail-label {
            color: var(--text-gray);
            font-size: 0.95rem;
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Recommendations */
        .recommendations {
            background: linear-gradient(to right, #e3f2fd, #f3e5f5);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            border-left: 5px solid var(--secondary-color);
        }

        .recommendations h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .recommendations ul {
            padding-left: 20px;
        }

        .recommendations li {
            margin-bottom: 12px;
            color: var(--text-dark);
            line-height: 1.5;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 16px 35px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.3);
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

        .btn-danger {
            background: linear-gradient(to right, #ff4757, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 50px;
            color: var(--text-gray);
            font-size: 0.9rem;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Print Styles */
        @media print {
            .action-buttons, .footer {
                display: none;
            }
            
            body {
                background: white;
                padding: 0;
            }
            
            .main-card, .stats-card, .detailed-breakdown, .recommendations {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

        /* Error Alert */
        .error-alert {
            background-color: #ffeaea;
            color: #d32f2f;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-alert i {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> API Score Results</h1>
            <p>Detailed analysis of your Academic Performance Indicator calculation</p>
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Faculty: <?php echo htmlspecialchars($faculty_name); ?>
                <?php if($application_id): ?>
                    | Application ID: #<?php echo str_pad($application_id, 5, '0', STR_PAD_LEFT); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if(!$insert_query): ?>
        <div class="error-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Note:</strong> Your API score was calculated successfully but could not be saved to the database.
                You can still use this result for your reference. Please contact the administrator if you need to save this calculation.
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Results -->
        <div class="results-container">
            <!-- Left Column: Main Score -->
            <div class="main-card">
                <div class="score-display">
                    <div class="total-score"><?php echo $total_score; ?></div>
                    <div class="eligibility-badge <?php echo $eligibility_class; ?>">
                        <?php echo $eligibility; ?>
                    </div>
                    <div class="promotion-level" style="color: <?php echo $promotion_color; ?>">
                        <?php echo $promotion_level; ?>
                    </div>
                    <p style="color: var(--text-gray); margin-top: 15px;">
                        Calculated on <?php echo date('F j, Y'); ?>
                    </p>
                </div>

                <!-- Category Breakdown -->
                <div class="category-breakdown">
                    <h3 style="color: var(--primary-color); margin-bottom: 25px; font-size: 1.4rem;">
                        <i class="fas fa-chart-pie"></i> Category Breakdown
                    </h3>
                    
                    <!-- Category I -->
                    <div class="category-item">
                        <div class="category-header">
                            <span class="category-title">Teaching Activities</span>
                            <span class="category-score"><?php echo $cat1; ?> points</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar cat1-bar" data-percent="<?php echo $cat1_percent; ?>"></div>
                        </div>
                        <div class="percentage"><?php echo round($cat1_percent, 1); ?>% of total score</div>
                    </div>
                    
                    <!-- Category II -->
                    <div class="category-item">
                        <div class="category-header">
                            <span class="category-title">Co-curricular Activities</span>
                            <span class="category-score"><?php echo $cat2; ?> points</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar cat2-bar" data-percent="<?php echo $cat2_percent; ?>"></div>
                        </div>
                        <div class="percentage"><?php echo round($cat2_percent, 1); ?>% of total score</div>
                    </div>
                    
                    <!-- Category III -->
                    <div class="category-item">
                        <div class="category-header">
                            <span class="category-title">Research Contributions</span>
                            <span class="category-score"><?php echo $cat3; ?> points</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar cat3-bar" data-percent="<?php echo $cat3_percent; ?>"></div>
                        </div>
                        <div class="percentage"><?php echo round($cat3_percent, 1); ?>% of total score</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Stats -->
            <div class="stats-card">
                <h3><i class="fas fa-info-circle"></i> Quick Stats</h3>
                
                <div class="stat-item">
                    <span class="stat-label">Minimum Required Score</span>
                    <span class="stat-value">80</span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Your Score</span>
                    <span class="stat-value"><?php echo $total_score; ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Difference</span>
                    <span class="stat-value"><?php echo $total_score - 80; ?></span>
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Promotion Status</span>
                    <span class="stat-value"><?php echo $promotion_status; ?></span>
                </div>
                
                <?php if($application_id): ?>
                <div class="stat-item">
                    <span class="stat-label">Application ID</span>
                    <span class="stat-value">#<?php echo str_pad($application_id, 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="stat-item">
                    <span class="stat-label">Date Calculated</span>
                    <span class="stat-value"><?php echo date('M d, Y'); ?></span>
                </div>
            </div>
        </div>

        <!-- Detailed Breakdown -->
        <div class="detailed-breakdown">
            <h2><i class="fas fa-list-alt"></i> Detailed Score Breakdown</h2>
            
            <div class="category-details">
                <!-- Category I Details -->
                <div class="detail-card cat1">
                    <h4><i class="fas fa-chalkboard-teacher"></i> Teaching Activities</h4>
                    <div class="detail-item">
                        <span class="detail-label">Teaching Hours</span>
                        <span class="detail-value"><?php echo $teaching_hours; ?> × 2 = <?php echo $teaching_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Student Feedback</span>
                        <span class="detail-value"><?php echo $feedback; ?> × 5 = <?php echo $feedback_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Mentorship</span>
                        <span class="detail-value"><?php echo $mentorship; ?> × 3 = <?php echo $mentorship_score; ?></span>
                    </div>
                    <div class="detail-item" style="border-bottom: none; font-weight: 700; padding-top: 10px;">
                        <span class="detail-label">Total Category I</span>
                        <span class="detail-value"><?php echo $cat1; ?> points</span>
                    </div>
                </div>
                
                <!-- Category II Details -->
                <div class="detail-card cat2">
                    <h4><i class="fas fa-users"></i> Co-curricular Activities</h4>
                    <div class="detail-item">
                        <span class="detail-label">Extension Activities</span>
                        <span class="detail-value"><?php echo $extension; ?> × 4 = <?php echo $extension_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Professional Development</span>
                        <span class="detail-value"><?php echo $pdp; ?> × 6 = <?php echo $pdp_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Administrative Roles</span>
                        <span class="detail-value"><?php echo $admin; ?> × 5 = <?php echo $admin_score; ?></span>
                    </div>
                    <div class="detail-item" style="border-bottom: none; font-weight: 700; padding-top: 10px;">
                        <span class="detail-label">Total Category II</span>
                        <span class="detail-value"><?php echo $cat2; ?> points</span>
                    </div>
                </div>
                
                <!-- Category III Details -->
                <div class="detail-card cat3">
                    <h4><i class="fas fa-flask"></i> Research Contributions</h4>
                    <div class="detail-item">
                        <span class="detail-label">Research Papers</span>
                        <span class="detail-value"><?php echo $papers; ?> × 10 = <?php echo $papers_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Books/Chapters</span>
                        <span class="detail-value"><?php echo $books; ?> × 15 = <?php echo $books_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Conference Presentations</span>
                        <span class="detail-value"><?php echo $conference; ?> × 8 = <?php echo $conference_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Patents</span>
                        <span class="detail-value"><?php echo $patents; ?> × 20 = <?php echo $patents_score; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Research Projects</span>
                        <span class="detail-value"><?php echo $projects; ?> × 25 = <?php echo $projects_score; ?></span>
                    </div>
                    <div class="detail-item" style="border-bottom: none; font-weight: 700; padding-top: 10px;">
                        <span class="detail-label">Total Category III</span>
                        <span class="detail-value"><?php echo $cat3; ?> points</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="recommendations">
            <h3><i class="fas fa-lightbulb"></i> Recommendations & Next Steps</h3>
            <ul>
                <?php if($total_score >= 80): ?>
                    <li><strong>Congratulations!</strong> You meet the minimum API score requirement for promotion.</li>
                    <li>Submit your promotion application through the "Apply for Promotion" section.</li>
                    <li>Ensure all supporting documents are prepared and ready for submission.</li>
                    <li>Consider applying for higher positions if your score exceeds 90 points.</li>
                <?php else: ?>
                    <li><strong>Areas for Improvement:</strong> Your score is <?php echo 80 - $total_score; ?> points below the requirement.</li>
                    <li><strong>Focus on Research:</strong> Increase research publications and project work to boost Category III score.</li>
                    <li><strong>Enhance Teaching:</strong> Improve student feedback scores and increase mentorship activities.</li>
                    <li><strong>Expand Co-curricular:</strong> Participate in more extension activities and take administrative roles.</li>
                    <li>Consider waiting for next evaluation cycle to accumulate more points.</li>
                <?php endif; ?>
                <?php if($application_id): ?>
                    <li>Keep this report for your records - Application ID: #<?php echo str_pad($application_id, 5, '0', STR_PAD_LEFT); ?></li>
                <?php else: ?>
                    <li><strong>Note:</strong> This calculation was not saved to the database. Please take a screenshot or print this page for your records.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if($total_score >= 80): ?>
                <a href="apply_promotion.php?api_score=<?php echo $total_score; ?>" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Apply for Promotion
                </a>
            <?php endif; ?>
            
            <a href="advanced_api_form.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Recalculate API Score
            </a>
            
            <a href="faculty_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-tachometer-alt"></i> Back to Dashboard
            </a>
            
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Print Results
            </button>
            
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | API Results v2.0</p>
            <p>This report is generated for <?php echo htmlspecialchars($faculty_name); ?> on <?php echo date('F j, Y, h:i A'); ?></p>
            <p>Confidential - For official use only</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const percent = bar.getAttribute('data-percent');
                setTimeout(() => {
                    bar.style.width = percent + '%';
                }, 300);
            });

            // Add animation to score display
            const totalScore = document.querySelector('.total-score');
            totalScore.style.animation = 'pulse 2s infinite';

            // Auto-scroll to top
            window.scrollTo(0, 0);

            // Add event listener for print button
            const printBtn = document.querySelector('[onclick="window.print()"]');
            printBtn.addEventListener('click', function() {
                setTimeout(() => {
                    alert('Your API Score report is ready to print. Save as PDF for digital records.');
                }, 500);
            });

            // Save results to localStorage for later reference
            const resultsData = {
                totalScore: <?php echo $total_score; ?>,
                category1: <?php echo $cat1; ?>,
                category2: <?php echo $cat2; ?>,
                category3: <?php echo $cat3; ?>,
                eligibility: "<?php echo $eligibility; ?>",
                date: new Date().toISOString(),
                applicationId: "<?php echo $application_id; ?>"
            };
            
            localStorage.setItem('lastAPICalculation', JSON.stringify(resultsData));
            
            // Show save confirmation if successful
            <?php if($application_id): ?>
            setTimeout(() => {
                const saveMsg = document.createElement('div');
                saveMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:var(--success-color);color:white;padding:12px 20px;border-radius:8px;box-shadow:0 5px 15px rgba(0,0,0,0.2);z-index:1000;';
                saveMsg.innerHTML = '<i class="fas fa-check"></i> Results saved successfully!';
                document.body.appendChild(saveMsg);
                
                setTimeout(() => {
                    saveMsg.style.opacity = '0';
                    saveMsg.style.transition = 'opacity 0.5s';
                    setTimeout(() => saveMsg.remove(), 500);
                }, 3000);
            }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 3c9ca7f4e7925fe3261a82e10ffb2dcc04d3d0a3
