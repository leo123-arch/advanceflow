<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$query = mysqli_query($conn, "SELECT name, role, email FROM faculty WHERE id='$faculty_id'");
$faculty_data = mysqli_fetch_assoc($query);
$faculty_name = $faculty_data['name'];

// Fetch recent teaching activities
$recent_query = mysqli_query($conn, 
    "SELECT * FROM teaching_activities 
     WHERE faculty_id='$faculty_id' 
     ORDER BY created_at DESC 
     LIMIT 5");

// Fetch teaching stats
$stats_query = mysqli_query($conn,
    "SELECT 
        AVG(teaching_hours) as avg_hours,
        AVG(feedback_score) as avg_feedback,
        SUM(mentorship) as total_mentorship,
        COUNT(*) as total_submissions
     FROM teaching_activities 
     WHERE faculty_id='$faculty_id'");
$stats = mysqli_fetch_assoc($stats_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teaching Activities | Career Advancement System</title>
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
            --sidebar-width: 280px;
            --header-height: 80px;
        }

        body {
            background-color: #f5f7fb;
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            border-right: 1px solid #e9ecef;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 30px 25px 25px;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .logo-text h2 {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo-text p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }

        /* Profile Section */
        .profile-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 10px;
        }

        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 600;
            overflow: hidden;
        }

        .profile-info h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .profile-info .role {
            font-size: 13px;
            color: var(--primary-color);
            background: rgba(106, 17, 203, 0.1);
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Navigation */
        .sidebar-nav {
            padding: 25px 20px;
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-section h3 {
            font-size: 13px;
            color: var(--gray-text);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-left: 10px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-link:hover {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.1));
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.2);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f8f9fa;
        }

        /* Header */
        .main-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .header-left h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-text);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Content Area */
        .content-wrapper {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(106, 17, 203, 0.2);
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-banner h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .welcome-banner p {
            font-size: 16px;
            opacity: 0.9;
            max-width: 700px;
            position: relative;
            z-index: 1;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary-color);
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card.hours { border-left-color: #6a11cb; }
        .stat-card.feedback { border-left-color: #2ed573; }
        .stat-card.mentorship { border-left-color: #2575fc; }
        .stat-card.submissions { border-left-color: #ffa502; }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            margin: 0 auto 15px;
        }

        .stat-card.hours .stat-icon { background: linear-gradient(135deg, #6a11cb, #8a2be2); }
        .stat-card.feedback .stat-icon { background: linear-gradient(135deg, #2ed573, #27ae60); }
        .stat-card.mentorship .stat-icon { background: linear-gradient(135deg, #2575fc, #3498db); }
        .stat-card.submissions .stat-icon { background: linear-gradient(135deg, #ffa502, #e67e22); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card.hours .stat-value { background: linear-gradient(135deg, #6a11cb, #8a2be2); -webkit-background-clip: text; }
        .stat-card.feedback .stat-value { background: linear-gradient(135deg, #2ed573, #27ae60); -webkit-background-clip: text; }
        .stat-card.mentorship .stat-value { background: linear-gradient(135deg, #2575fc, #3498db); -webkit-background-clip: text; }
        .stat-card.submissions .stat-value { background: linear-gradient(135deg, #ffa502, #e67e22); -webkit-background-clip: text; }

        .stat-label {
            color: var(--text-gray);
            font-size: 14px;
            font-weight: 500;
        }

        /* Two Column Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Teaching Form Card */
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-header p {
            color: var(--text-gray);
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 15px;
        }

        .form-group label .required {
            color: var(--danger-color);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: var(--light-bg);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .range-slider {
            width: 100%;
            margin-top: 10px;
            -webkit-appearance: none;
            height: 8px;
            background: var(--light-bg);
            border-radius: 4px;
            outline: none;
        }

        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: var(--primary-color);
            border-radius: 50%;
            cursor: pointer;
        }

        .slider-value {
            text-align: center;
            font-weight: 600;
            color: var(--primary-color);
            margin-top: 10px;
        }

        .input-hint {
            font-size: 13px;
            color: var(--text-gray);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-hint i {
            color: var(--warning-color);
        }

        /* Recent Activities Card */
        .activities-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .activities-header {
            margin-bottom: 30px;
        }

        .activities-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activities-header p {
            color: var(--text-gray);
            font-size: 16px;
        }

        .activities-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--light-bg);
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }

        .activity-item:hover {
            background: rgba(106, 17, 203, 0.05);
            transform: translateX(5px);
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .activity-details {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: var(--text-gray);
        }

        .activity-detail {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .activity-date {
            font-size: 12px;
            color: var(--text-gray);
        }

        .no-activities {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-gray);
        }

        .no-activities i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #e9ecef;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 16px 30px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            min-width: 150px;
            justify-content: center;
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

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
            border-left: 5px solid var(--secondary-color);
        }

        .info-box h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box ul {
            padding-left: 20px;
            margin-top: 10px;
        }

        .info-box li {
            margin-bottom: 8px;
            color: var(--text-gray);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
                position: fixed;
                top: 25px;
                right: 25px;
                z-index: 101;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                width: 45px;
                height: 45px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                cursor: pointer;
                box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
            }
            
            .content-wrapper {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .form-card, .activities-card {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .activity-details {
                flex-direction: column;
                gap: 5px;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="logo-text">
                    <h2>CareerFlow</h2>
                    <p>Advancement System</p>
                </div>
            </div>
            
            <div class="profile-section">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($faculty_name, 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h4><?php echo htmlspecialchars($faculty_name); ?></h4>
                    <span class="role"><?php echo ucfirst($faculty_data['role'] ?? 'Faculty'); ?></span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">
            <div class="nav-section">
                <h3>Dashboard</h3>
                <div class="nav-links">
                    <a href="faculty_dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard Overview</span>
                    </a>
                    <a href="edit_profile.php" class="nav-link">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Promotion</h3>
                <div class="nav-links">
                    <a href="apply_promotion.php" class="nav-link">
                        <i class="fas fa-medal"></i>
                        <span>Apply for Promotion</span>
                    </a>
                    <a href="advanced_api_form.php" class="nav-link">
                        <i class="fas fa-calculator"></i>
                        <span>Calculate API Score</span>
                    </a>
                    <a href="promotion_status.php" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Check Status</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Activities</h3>
                <div class="nav-links">
                    <a href="teaching_form.php" class="nav-link active">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teaching Activities</span>
                    </a>
                    <a href="faculty_research_upload.php" class="nav-link">
                        <i class="fas fa-flask"></i>
                        <span>Research Contributions</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Account</h3>
                <div class="nav-links">
                    <a href="logout.php" class="nav-link" style="color: #ff4757;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="main-header">
            <div class="header-left">
                <h1>Teaching Activities</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($faculty_name); ?></span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2><i class="fas fa-chalkboard-teacher"></i> Category I – Teaching Activities</h2>
                <p>Record your teaching hours, student feedback, and mentorship activities for API score calculation</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card hours">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo round($stats['avg_hours'] ?? 0, 1); ?></div>
                    <div class="stat-label">Average Teaching Hours</div>
                </div>

                <div class="stat-card feedback">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?php echo round($stats['avg_feedback'] ?? 0, 1); ?>/10</div>
                    <div class="stat-label">Avg Feedback Score</div>
                </div>

                <div class="stat-card mentorship">
                    <div class="stat-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_mentorship'] ?? 0; ?></div>
                    <div class="stat-label">Total Mentorship</div>
                </div>

                <div class="stat-card submissions">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_submissions'] ?? 0; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
            </div>

            <!-- Two Column Grid -->
            <div class="content-grid">
                <!-- Left Column: Teaching Form -->
                <div class="form-card">
                    <div class="form-header">
                        <h3><i class="fas fa-edit"></i> Submit Teaching Activities</h3>
                        <p>Enter your teaching details for API score calculation</p>
                    </div>

                    <form method="POST" action="teaching_process.php" id="teachingForm">
                        <!-- Teaching Hours -->
                        <div class="form-group">
                            <label>Teaching Hours per Week <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-clock"></i>
                                <input type="number" name="teaching_hours" class="form-control" 
                                       placeholder="e.g., 16" min="0" max="40" required>
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Standard: 16 hours/week for Assistant Professor
                            </div>
                        </div>

                        <!-- Student Feedback -->
                        <div class="form-group">
                            <label>Student Feedback Score (0–10) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-star"></i>
                                <input type="number" name="feedback_score" class="form-control" 
                                       id="feedbackInput" placeholder="0-10" min="0" max="10" step="0.5" required>
                            </div>
                            <input type="range" class="range-slider" id="feedbackSlider" 
                                   min="0" max="10" step="0.5" value="5">
                            <div class="slider-value" id="sliderValue">5.0</div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Based on student evaluation surveys
                            </div>
                        </div>

                        <!-- Mentorship -->
                        <div class="form-group">
                            <label>Mentorship Activities <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-hands-helping"></i>
                                <input type="number" name="mentorship" class="form-control" 
                                       placeholder="e.g., 5" min="0" required>
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Number of students mentored/projects guided this month
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Activities
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Recent Activities -->
                <div class="activities-card">
                    <div class="activities-header">
                        <h3><i class="fas fa-history"></i> Recent Submissions</h3>
                        <p>Your recently recorded teaching activities</p>
                    </div>

                    <div class="activities-list">
                        <?php if(mysqli_num_rows($recent_query) > 0): ?>
                            <?php while($activity = mysqli_fetch_assoc($recent_query)): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Teaching Activities Recorded</div>
                                    <div class="activity-details">
                                        <div class="activity-detail">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $activity['teaching_hours']; ?> hours</span>
                                        </div>
                                        <div class="activity-detail">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo $activity['feedback_score']; ?>/10</span>
                                        </div>
                                        <div class="activity-detail">
                                            <i class="fas fa-hands-helping"></i>
                                            <span><?php echo $activity['mentorship']; ?> mentorship</span>
                                        </div>
                                    </div>
                                    <div class="activity-date">
                                        <?php echo date('M d, Y', strtotime($activity['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-activities">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <h4>No Activities Recorded</h4>
                                <p>Submit your first teaching activities to see them here</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- View All Button -->
                    <?php if(mysqli_num_rows($recent_query) > 0): ?>
                    <div style="margin-top: 25px; text-align: center;">
                        <a href="teaching_history.php" class="btn btn-secondary" style="min-width: auto; padding: 12px 25px;">
                            <i class="fas fa-list"></i> View All Activities
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <h4><i class="fas fa-lightbulb"></i> API Score Calculation Guidelines</h4>
                <p>Your teaching activities contribute to Category I of the API score:</p>
                <ul>
                    <li><strong>Teaching Hours:</strong> 2 points per hour (Maximum 40 hours/week)</li>
                    <li><strong>Student Feedback:</strong> 5 points per rating point (Scale: 0-10)</li>
                    <li><strong>Mentorship:</strong> 3 points per student/project guided</li>
                    <li><strong>Weightage:</strong> Category I contributes 50% to total API score</li>
                    <li><strong>Frequency:</strong> Submit activities monthly for accurate tracking</li>
                </ul>
                <p style="margin-top: 15px; font-style: italic;">
                    Tip: Regular submission of teaching activities helps maintain accurate API scores for promotion applications.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Feedback slider functionality
        const feedbackInput = document.getElementById('feedbackInput');
        const feedbackSlider = document.getElementById('feedbackSlider');
        const sliderValue = document.getElementById('sliderValue');

        // Sync slider and input
        function updateFeedbackValue() {
            const value = parseFloat(feedbackSlider.value);
            feedbackInput.value = value;
            sliderValue.textContent = value.toFixed(1);
        }

        feedbackSlider.addEventListener('input', updateFeedbackValue);
        feedbackInput.addEventListener('input', function() {
            const value = parseFloat(this.value) || 0;
            if (value >= 0 && value <= 10) {
                feedbackSlider.value = value;
                sliderValue.textContent = value.toFixed(1);
            }
        });

        // Initialize slider value
        updateFeedbackValue();

        // Form validation
        document.getElementById('teachingForm').addEventListener('submit', function(e) {
            const teachingHours = document.querySelector('input[name="teaching_hours"]');
            const feedback = document.querySelector('input[name="feedback_score"]');
            const mentorship = document.querySelector('input[name="mentorship"]');
            
            let isValid = true;
            let errorMessage = '';
            
            if (teachingHours.value < 0 || teachingHours.value > 40) {
                isValid = false;
                errorMessage += 'Teaching hours must be between 0 and 40.\n';
            }
            
            if (feedback.value < 0 || feedback.value > 10) {
                isValid = false;
                errorMessage += 'Feedback score must be between 0 and 10.\n';
            }
            
            if (mentorship.value < 0) {
                isValid = false;
                errorMessage += 'Mentorship activities cannot be negative.\n';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errorMessage);
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
            // Add focus effects to inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>