<?php
include "config.php";
session_start();

// Allow admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : "admin@university.edu";

// Fetch statistics from promotion_requests table
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests"))['c'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Pending'"))['c'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Approved'"))['c'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Rejected'"))['c'];

// Additional stats - with error handling for non-existent tables
$faculty_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM faculty"))['c'];

// Try to get research count - check if table exists first
$research_count = 0;
$research_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'research_publications'");
if(mysqli_num_rows($research_table_exists) > 0) {
    $research_result = mysqli_query($conn, "SELECT COUNT(*) AS c FROM research_publications");
    if($research_result) {
        $research_count = mysqli_fetch_assoc($research_result)['c'];
    }
}

// Try to get password requests count
$password_requests = 0;
$password_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'password_reset_requests'");
if(mysqli_num_rows($password_table_exists) > 0) {
    $password_result = mysqli_query($conn, "SELECT COUNT(*) AS c FROM password_reset_requests WHERE status='pending'");
    if($password_result) {
        $password_requests = mysqli_fetch_assoc($password_result)['c'];
    }
}

// Recent promotion applications with corrected column name
$recent_applications = [];
try {
    // First check what column exists in the faculty table
    $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM faculty");
    $faculty_columns = [];
    while($col = mysqli_fetch_assoc($check_columns)) {
        $faculty_columns[] = $col['Field'];
    }
    
    // Determine which designation/position column to use
    $designation_column = 'designation'; // default assumption
    if(in_array('current_designation', $faculty_columns)) {
        $designation_column = 'current_designation';
    } elseif(in_array('designation', $faculty_columns)) {
        $designation_column = 'designation';
    } elseif(in_array('position', $faculty_columns)) {
        $designation_column = 'position';
    } elseif(in_array('current_position', $faculty_columns)) {
        $designation_column = 'current_position';
    } elseif(in_array('job_title', $faculty_columns)) {
        $designation_column = 'job_title';
    }
    
    $recent_query = mysqli_query($conn, "SELECT pr.*, f.name as faculty_name, f.{$designation_column} as current_position
        FROM promotion_requests pr 
        JOIN faculty f ON pr.faculty_id = f.id 
        ORDER BY pr.created_at DESC 
        LIMIT 5");
    
    if($recent_query) {
        while($row = mysqli_fetch_assoc($recent_query)) {
            $recent_applications[] = $row;
        }
    }
} catch (Exception $e) {
    // Fallback: try without the designation column
    $recent_query = mysqli_query($conn, "SELECT pr.*, f.name as faculty_name
        FROM promotion_requests pr 
        JOIN faculty f ON pr.faculty_id = f.id 
        ORDER BY pr.created_at DESC 
        LIMIT 5");
    
    if($recent_query) {
        while($row = mysqli_fetch_assoc($recent_query)) {
            $row['current_position'] = 'N/A';
            $recent_applications[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Career Advancement System</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
        }

        .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #6a11cb, #2575fc); }
        .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #ff7e5f, #feb47b); }
        .stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #2ed573, #1dd1a1); }
        .stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #ff4757, #ff3838); }

        .stat-trend {
            font-size: 14px;
            font-weight: 600;
            color: var(--success-color);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card:nth-child(1) .stat-value { color: #6a11cb; }
        .stat-card:nth-child(2) .stat-value { color: #ff7e5f; }
        .stat-card:nth-child(3) .stat-value { color: #2ed573; }
        .stat-card:nth-child(4) .stat-value { color: #ff4757; }

        .stat-label {
            color: var(--gray-text);
            font-size: 14px;
        }

        /* Two Column Layout */
        .dashboard-columns {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .dashboard-columns {
                grid-template-columns: 1fr;
            }
        }

        /* Recent Applications */
        .applications-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-text);
        }

        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .applications-table {
            width: 100%;
            border-collapse: collapse;
        }

        .applications-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #e9ecef;
            color: var(--gray-text);
            font-weight: 600;
            font-size: 14px;
        }

        .applications-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .applications-table tr:hover {
            background: #f8f9fa;
        }

        .faculty-name {
            font-weight: 600;
            color: var(--dark-text);
        }

        .application-date {
            color: var(--gray-text);
            font-size: 14px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: rgba(255, 165, 2, 0.1);
            color: #ffa502;
        }

        .status-approved {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
        }

        .status-rejected {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
        }

        /* Quick Stats Cards */
        .quick-stats {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .stats-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-text);
        }

        .stats-grid-small {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            background: #f8f9fa;
        }

        .stat-item .value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-item .label {
            font-size: 12px;
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
            
            .dashboard-columns {
                grid-template-columns: 1fr;
            }
            
            .applications-table {
                display: block;
                overflow-x: auto;
            }
            
            .stats-grid-small {
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
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="logo-text">
                    <h2>CareerFlow</h2>
                    <p>Admin Panel</p>
                </div>
            </div>
            
            <div class="profile-section">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h4><?php echo htmlspecialchars($admin_name); ?></h4>
                    <span class="role">Administrator</span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">
            <div class="nav-section">
                <h3>Dashboard</h3>
                <div class="nav-links">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard Overview</span>
                    </a>
                    <a href="admin_analytics.php" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Analytics</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Promotion Management</h3>
                <div class="nav-links">
                    <a href="admin_promotion_request.php" class="nav-link">
                        <i class="fas fa-medal"></i>
                        <span>Promotion Applications</span>
                    </a>
                    <a href="admin_faculty_ranking.php" class="nav-link">
                        <i class="fas fa-trophy"></i>
                        <span>Faculty Ranking</span>
                    </a>
                    <a href="admin_eligibility_check.php" class="nav-link">
                        <i class="fas fa-check-circle"></i>
                        <span>Eligibility Check</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Faculty Management</h3>
                <div class="nav-links">
                    <a href="admin_faculty_list.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>All Faculty</span>
                    </a>
                    <a href="admin_add_faculty.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Faculty</span>
                    </a>
                    <a href="admin_research_uploads.php" class="nav-link">
                        <i class="fas fa-flask"></i>
                        <span>Research Documents</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Support & Requests</h3>
                <div class="nav-links">
                    <a href="admin_password_requests.php" class="nav-link">
                        <i class="fas fa-key"></i>
                        <span>Password Requests</span>
                        <?php if($password_requests > 0): ?>
                        <span style="background: var(--danger-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                            <?php echo $password_requests; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <a href="admin_reports.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Generate Reports</span>
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
                <h1>Admin Dashboard</h1>
            </div>
            <div class="header-right">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <?php if($pending > 0): ?>
                    <span class="notification-badge"><?php echo $pending; ?></span>
                    <?php endif; ?>
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
                <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
                <p>Manage faculty promotions, review applications, and monitor system activity from your admin dashboard.</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-trend">
                            Total Applications
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $total; ?></div>
                    <div class="stat-label">Promotion Applications</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-trend">
                            Needs Review
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $pending; ?></div>
                    <div class="stat-label">Pending Applications</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-trend">
                            Approved
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $approved; ?></div>
                    <div class="stat-label">Successful Promotions</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-trend">
                            Total Faculty
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $faculty_count; ?></div>
                    <div class="stat-label">Registered Faculty</div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="dashboard-columns">
                <!-- Recent Applications -->
                <div class="applications-section">
                    <div class="section-header">
                        <h2>Recent Promotion Applications</h2>
                        <a href="admin_promotion_request.php" class="view-all">View All →</a>
                    </div>
                    
                    <?php if(count($recent_applications) > 0): ?>
                    <table class="applications-table">
                        <thead>
                            <tr>
                                <th>Faculty Name</th>
                                <th>Current Position</th>
                                <th>Target Position</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_applications as $application): 
                                $status = isset($application['status']) ? $application['status'] : 'Pending';
                                $status_class = 'status-' . strtolower($status);
                                $current_position = isset($application['current_position']) ? $application['current_position'] : 'N/A';
                                $target_position = isset($application['target_position']) ? $application['target_position'] : 'N/A';
                                $created_date = isset($application['created_at']) ? $application['created_at'] : date('Y-m-d H:i:s');
                            ?>
                            <tr>
                                <td>
                                    <div class="faculty-name"><?php echo htmlspecialchars($application['faculty_name'] ?? 'Unknown'); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($current_position); ?></td>
                                <td><?php echo htmlspecialchars($target_position); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="application-date">
                                        <?php echo date('M d, Y', strtotime($created_date)); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--gray-text);">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                        <h3 style="margin-bottom: 10px;">No Applications Yet</h3>
                        <p>No promotion applications have been submitted recently.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats Sidebar -->
                <div class="quick-stats">
                    <div class="stats-card">
                        <h3>Application Status</h3>
                        <div class="stats-grid-small">
                            <div class="stat-item">
                                <div class="value" style="color: #ffa502;"><?php echo $pending; ?></div>
                                <div class="label">Pending</div>
                            </div>
                            <div class="stat-item">
                                <div class="value" style="color: #2ed573;"><?php echo $approved; ?></div>
                                <div class="label">Approved</div>
                            </div>
                            <div class="stat-item">
                                <div class="value" style="color: #ff4757;"><?php echo $rejected; ?></div>
                                <div class="label">Rejected</div>
                            </div>
                            <div class="stat-item">
                                <div class="value" style="color: #6a11cb;"><?php echo $total; ?></div>
                                <div class="label">Total</div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card">
                        <h3>System Statistics</h3>
                        <div class="stats-grid-small">
                            <div class="stat-item">
                                <div class="value"><?php echo $faculty_count; ?></div>
                                <div class="label">Faculty</div>
                            </div>
                            <div class="stat-item">
                                <div class="value"><?php echo $research_count; ?></div>
                                <div class="label">Research</div>
                            </div>
                            <div class="stat-item">
                                <div class="value"><?php echo $password_requests; ?></div>
                                <div class="label">Requests</div>
                            </div>
                            <div class="stat-item">
                                <div class="value"><?php echo date('M Y'); ?></div>
                                <div class="label">This Month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="admin_promotion_request.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="action-info">
                            <h4>Review Applications</h4>
                            <p>Review and process pending promotion applications</p>
                        </div>
                    </a>

                    <a href="admin_password_requests.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="action-info">
                            <h4>Password Requests</h4>
                            <p>Manage faculty password reset requests</p>
                        </div>
                    </a>

                    <a href="admin_add_faculty.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="action-info">
                            <h4>Add Faculty</h4>
                            <p>Register new faculty members to the system</p>
                        </div>
                    </a>

                    <a href="admin_research_uploads.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="action-info">
                            <h4>Research Documents</h4>
                            <p>View and manage faculty research submissions</p>
                        </div>
                    </a>

                    <a href="admin_faculty_ranking.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="action-info">
                            <h4>Faculty Ranking</h4>
                            <p>View faculty performance and API score rankings</p>
                        </div>
                    </a>

                    <a href="admin_reports.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-info">
                            <h4>Generate Reports</h4>
                            <p>Create detailed reports and analytics</p>
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

        // Add hover effects to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.action-card, .stat-card, .stats-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });

            // Add click animation to notification button
            const notificationBtn = document.querySelector('.notification-btn');
            if (notificationBtn) {
                notificationBtn.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                        window.location.href = 'admin_promotion_request.php';
                    }, 200);
                });
            }
        });

        // Update date and time
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
    </script>
</body>
</html>