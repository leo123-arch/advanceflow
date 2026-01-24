<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details (without profile_pic column)
$query = mysqli_query($conn, "SELECT name, role, email FROM faculty WHERE id='$faculty_id'");
$user = mysqli_fetch_assoc($query);
$name = $user['name'];
$role = $user['role'];
$email = $user['email'];

// Fetch latest API score
$api_query = mysqli_query($conn, "SELECT api_score, cat1, cat2, cat3, created_at 
                                 FROM promotion_applications 
                                 WHERE faculty_id='$faculty_id' 
                                 ORDER BY created_at DESC 
                                 LIMIT 1");
$api_data = mysqli_fetch_assoc($api_query);

// Fetch API score stats
$stats_query = mysqli_query($conn, "SELECT 
                                    COUNT(*) as total_calculations,
                                    MAX(api_score) as highest_score,
                                    AVG(api_score) as average_score
                                    FROM promotion_applications 
                                    WHERE faculty_id='$faculty_id'");
$stats_data = mysqli_fetch_assoc($stats_query);

// Fetch recent promotions
$promotions_query = mysqli_query($conn, "SELECT COUNT(*) as pending_applications 
                                        FROM promotion_applications 
                                        WHERE faculty_id='$faculty_id' 
                                        AND status = 'Pending'");
$promotions_data = mysqli_fetch_assoc($promotions_query);
$pending_applications = $promotions_data['pending_applications'] ?? 0;

// Determine promotion eligibility
$current_api_score = $api_data['api_score'] ?? 0;
$eligibility_status = ($current_api_score >= 80) ? "Eligible" : "Not Eligible";
$eligibility_color = ($current_api_score >= 80) ? "#2ed573" : "#ff4757";

// Get latest calculation date
$last_calculated = $api_data['created_at'] ?? "Never";
if ($last_calculated != "Never" && $last_calculated != "0000-00-00 00:00:00") {
    $last_calculated = date("M d, Y", strtotime($last_calculated));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard | Career Advancement System</title>
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
            --dark-text: #2d3436;
            --gray-text: #636e72;
            --sidebar-width: 280px;
            --header-height: 80px;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark-text);
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
            color: var(--dark-text);
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
            color: var(--gray-text);
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

        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f8f9fa;
            border: none;
            color: var(--gray-text);
            cursor: pointer;
            transition: all 0.3s;
        }

        .notification-btn:hover {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.1));
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            padding: 40px;
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
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .welcome-banner p {
            font-size: 16px;
            opacity: 0.9;
            max-width: 600px;
            position: relative;
            z-index: 1;
        }

        /* Quick Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

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
        }

        .stat-trend {
            font-size: 14px;
            font-weight: 600;
            color: var(--success-color);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: var(--gray-text);
            font-size: 14px;
        }

        /* API Score Dashboard */
        .api-dashboard {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-text);
        }

        .api-score-main {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .score-circle {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            position: relative;
        }

        .circle-bg {
            fill: none;
            stroke: #e9ecef;
            stroke-width: 8;
        }

        .circle-progress {
            fill: none;
            stroke: var(--primary-color);
            stroke-width: 8;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 1s ease;
        }

        .circle-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .circle-text .score {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color);
        }

        .circle-text .label {
            font-size: 14px;
            color: var(--gray-text);
        }

        .api-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .category-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .category-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .category-1 .category-icon { background: #6a11cb; }
        .category-2 .category-icon { background: #2575fc; }
        .category-3 .category-icon { background: #2ed573; }

        .category-title {
            font-weight: 600;
            color: var(--dark-text);
        }

        .category-score {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .category-percent {
            font-size: 14px;
            color: var(--gray-text);
        }

        /* Quick Actions */
        .quick-actions {
            margin-top: 40px;
        }

        .quick-actions h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--dark-text);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
        }

        .action-info h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-text);
        }

        .action-info p {
            color: var(--gray-text);
            font-size: 14px;
            line-height: 1.5;
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
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
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
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">
                    <h2>CareerFlow</h2>
                    <p>Advancement System</p>
                </div>
            </div>
            
            <div class="profile-section">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($name, 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h4><?php echo htmlspecialchars($name); ?></h4>
                    <span class="role"><?php echo ucfirst($role); ?></span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">
            <div class="nav-section">
                <h3>Dashboard</h3>
                <div class="nav-links">
                    <a href="faculty_dashboard.php" class="nav-link active">
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
                    <a href="advanced_api_form.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Calculate API Score</span>
                    </a>
                    <a href="apply_promotion.php" class="nav-link">
                        <i class="fas fa-medal"></i>
                        <span>Apply for Promotion</span>
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
                    <a href="teaching_form.php" class="nav-link">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teaching Activities</span>
                    </a>
                    <a href="faculty_research_upload.php" class="nav-link">
                        <i class="fas fa-flask"></i>
                        <span>Research Contributions</span>
                    </a>
                    <a href="generate_resume.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Resume Builder</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Support</h3>
                <div class="nav-links">
                    <a href="faculty_chatbot.php" class="nav-link">
                        <i class="fas fa-robot"></i>
                        <span>AI Chatbot Assistant</span>
                    </a>
                    <a href="exam_assistant.html" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                        <span>Exam Assistant</span>
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
                <h1>Dashboard Overview</h1>
            </div>
            <div class="header-right">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <div style="color: var(--gray-text); font-size: 14px;">
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2>Welcome back, <?php echo htmlspecialchars($name); ?>!</h2>
                <p>Track your career advancement progress, check promotion eligibility, and manage your academic portfolio.</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-trend">
                            <?php if($current_api_score > 0): ?>
                                <?php echo ($current_api_score >= 80) ? '✓ Eligible' : 'Needs Work'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $current_api_score ?: '--'; ?></div>
                    <div class="stat-label">Current API Score</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-trend">
                            <?php echo $pending_applications > 0 ? 'In Progress' : 'None'; ?>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $pending_applications; ?></div>
                    <div class="stat-label">Pending Applications</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-trend">
                            <?php echo $stats_data['total_calculations'] ?? 0; ?> Total
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats_data['average_score'] ? round($stats_data['average_score']) : '--'; ?></div>
                    <div class="stat-label">Average API Score</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-trend">Today</div>
                    </div>
                    <div class="stat-value"><?php echo date('d M'); ?></div>
                    <div class="stat-label">Current Date</div>
                </div>
            </div>

            <!-- API Score Dashboard -->
            <div class="api-dashboard">
                <div class="dashboard-header">
                    <h2>API Score Analysis</h2>
                    <?php if($last_calculated != "Never"): ?>
                        <div style="color: var(--gray-text); font-size: 14px;">
                            Last calculated: <?php echo $last_calculated; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($api_data && $current_api_score > 0): ?>
                    <div class="api-score-main">
                        <div class="score-circle">
                            <svg width="150" height="150" viewBox="0 0 150 150">
                                <circle class="circle-bg" cx="75" cy="75" r="65"></circle>
                                <circle class="circle-progress" cx="75" cy="75" r="65" 
                                        stroke-dasharray="408.4" 
                                        stroke-dashoffset="<?php echo 408.4 * (1 - min($current_api_score / 100, 1)); ?>">
                                </circle>
                            </svg>
                            <div class="circle-text">
                                <div class="score"><?php echo $current_api_score; ?></div>
                                <div class="label">API Score</div>
                            </div>
                        </div>
                        <div style="display: inline-block; padding: 8px 20px; background: <?php echo $eligibility_color; ?>; color: white; border-radius: 20px; font-weight: 600;">
                            <?php echo $eligibility_status; ?> for Promotion
                        </div>
                        <p style="margin-top: 15px; color: var(--gray-text);">
                            Minimum required: 80 points
                        </p>
                    </div>

                    <div class="api-categories">
                        <div class="category-card category-1">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="category-title">Teaching</div>
                            </div>
                            <div class="category-score"><?php echo $api_data['cat1']; ?></div>
                            <div class="category-percent">Category I</div>
                        </div>

                        <div class="category-card category-2">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="category-title">Co-curricular</div>
                            </div>
                            <div class="category-score"><?php echo $api_data['cat2']; ?></div>
                            <div class="category-percent">Category II</div>
                        </div>

                        <div class="category-card category-3">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="category-title">Research</div>
                            </div>
                            <div class="category-score"><?php echo $api_data['cat3']; ?></div>
                            <div class="category-percent">Category III</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 50px 20px;">
                        <div style="font-size: 48px; color: #e9ecef; margin-bottom: 20px;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 style="color: var(--gray-text); margin-bottom: 15px;">No API Score Calculated Yet</h3>
                        <p style="color: var(--gray-text); margin-bottom: 25px; max-width: 400px; margin: 0 auto 25px;">
                            Calculate your API score to track your promotion eligibility and academic performance.
                        </p>
                        <a href="advanced_api_form.php" style="display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; text-decoration: none; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-calculator"></i> Calculate API Score
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="advanced_api_form.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="action-info">
                            <h4>Calculate API Score</h4>
                            <p>Calculate your academic performance indicator score for promotion</p>
                        </div>
                    </a>

                    <a href="apply_promotion.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="action-info">
                            <h4>Apply for Promotion</h4>
                            <p>Submit promotion application with required documents</p>
                        </div>
                    </a>

                    <a href="faculty_research_upload.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="action-info">
                            <h4>Research Contributions</h4>
                            <p>Add and manage your research publications and projects</p>
                        </div>
                    </a>

                    <a href="faculty_chatbot.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="action-info">
                            <h4>AI Assistant</h4>
                            <p>Get instant answers about promotion and API score queries</p>
                        </div>
                    </a>

                    <a href="generate_resume.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="action-info">
                            <h4>Resume Builder</h4>
                            <p>Create professional academic resumes automatically</p>
                        </div>
                    </a>

                    <a href="edit_profile.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div class="action-info">
                            <h4>Edit Profile</h4>
                            <p>Update your personal and professional information</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

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

        // API Score circle animation
        document.addEventListener('DOMContentLoaded', function() {
            const progressCircle = document.querySelector('.circle-progress');
            if (progressCircle) {
                // Already set via PHP, just add animation class
                progressCircle.style.transition = 'stroke-dashoffset 1.5s ease-in-out';
            }

            // Add hover effects to cards
            const cards = document.querySelectorAll('.action-card, .category-card, .stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });

            // Update current date time
            function updateDateTime() {
                const now = new Date();
                const options = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric'
                };
                const dateElement = document.querySelector('.header-right div');
                if (dateElement) {
                    dateElement.textContent = now.toLocaleDateString('en-US', options);
                }
            }

            // Update time every minute
            setInterval(updateDateTime, 60000);
        });

        // Scroll to top when navigating
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                window.scrollTo(0, 0);
            });
        });
    </script>
</body>
</html>