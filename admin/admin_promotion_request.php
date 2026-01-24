<?php
session_start();
include "config.php";

// Allow only admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";

// Get statistics with safe queries
$stats_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM promotion_requests
");
$stats = mysqli_fetch_assoc($stats_query) ?: ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

// Get all promotion requests with faculty details
$result = mysqli_query($conn, "
    SELECT 
        pr.*, 
        f.name,
        f.email,
        f.department,
        COALESCE(pa.api_score, 0) as api_score
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    LEFT JOIN promotion_applications pa ON pr.faculty_id = pa.faculty_id 
    ORDER BY 
        CASE WHEN pr.status = 'Pending' THEN 1
             WHEN pr.status = 'Approved' THEN 2
             ELSE 3 END,
        pr.created_at DESC
");

// Get recent activity - simplified without updated_at
$recent_updates = mysqli_query($conn, "
    SELECT 
        pr.id,
        f.name as faculty_name,
        pr.status,
        CONCAT('Application #', pr.id, ' was ', LOWER(pr.status)) as activity
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    WHERE pr.status IN ('Approved', 'Rejected')
    ORDER BY pr.id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Applications | Admin Dashboard</title>
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

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-top: 4px solid var(--primary-color);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-card.pending {
            border-top-color: var(--warning-color);
        }

        .stat-card.approved {
            border-top-color: var(--success-color);
        }

        .stat-card.rejected {
            border-top-color: var(--danger-color);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 24px;
        }

        .stat-card .stat-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card.pending .stat-icon {
            background: linear-gradient(135deg, var(--warning-color), #ff9f43);
        }

        .stat-card.approved .stat-icon {
            background: linear-gradient(135deg, var(--success-color), #25b764);
        }

        .stat-card.rejected .stat-icon {
            background: linear-gradient(135deg, var(--danger-color), #ff3547);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-card.pending .stat-value {
            color: var(--warning-color);
        }

        .stat-card.approved .stat-value {
            color: var(--success-color);
        }

        .stat-card.rejected .stat-value {
            color: var(--danger-color);
        }

        .stat-label {
            color: var(--gray-text);
            font-size: 1rem;
        }

        /* Applications Table */
        .applications-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            overflow-x: auto;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .table-filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: white;
            color: var(--dark-text);
            font-size: 14px;
            min-width: 150px;
        }

        .search-box {
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            min-width: 200px;
        }

        .applications-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        .applications-table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .applications-table th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .applications-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .applications-table tbody tr:hover {
            background: #f8f9fa;
        }

        .applications-table td {
            padding: 18px 15px;
            color: var(--dark-text);
            vertical-align: middle;
        }

        /* Faculty Info */
        .faculty-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .faculty-avatar {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            flex-shrink: 0;
        }

        .faculty-details h4 {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 3px;
        }

        .faculty-details .department {
            color: var(--gray-text);
            font-size: 0.85rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }

        .status-pending {
            background: rgba(255, 165, 2, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 165, 2, 0.2);
        }

        .status-approved {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 213, 115, 0.2);
        }

        .status-rejected {
            background: rgba(255, 71, 87, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(255, 71, 87, 0.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-approve {
            background: linear-gradient(135deg, var(--success-color), #25b764);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--danger-color), #ff3547);
            color: white;
        }

        .btn-view {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Document Link */
        .document-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            background: rgba(106, 17, 203, 0.1);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .document-link:hover {
            background: rgba(106, 17, 203, 0.2);
            transform: translateY(-2px);
        }

        /* Remarks */
        .remarks {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--gray-text);
            font-size: 0.9rem;
        }

        /* Recent Activity */
        .activity-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .activity-section h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark-text);
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-weight: 500;
            color: var(--dark-text);
            margin-bottom: 3px;
        }

        .activity-time {
            color: var(--gray-text);
            font-size: 0.85rem;
        }

        /* No Applications */
        .no-applications {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-text);
        }

        .no-applications i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        /* Action Modal */
        .action-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
                padding: 20px 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .table-filters {
                flex-direction: column;
                width: 100%;
            }
            
            .filter-select, .search-box {
                width: 100%;
            }
            
            .applications-table {
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
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
                    <a href="admin_dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard Overview</span>
                    </a>
                    <a href="admin_faculty_ranking.php" class="nav-link">
                        <i class="fas fa-trophy"></i>
                        <span>Faculty Ranking</span>
                    </a>
                </div>
            </div>

            <div class="nav-section">
                <h3>Promotion Management</h3>
                <div class="nav-links">
                    <a href="admin_promotion_request.php" class="nav-link active">
                        <i class="fas fa-medal"></i>
                        <span>Promotion Applications</span>
                        <?php if($stats['pending'] > 0): ?>
                        <span style="background: var(--danger-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                            <?php echo $stats['pending']; ?>
                        </span>
                        <?php endif; ?>
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
                <h1>Promotion Applications</h1>
            </div>
            <div class="header-right">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <?php if($stats['pending'] > 0): ?>
                    <span class="notification-badge"><?php echo $stats['pending']; ?></span>
                    <?php endif; ?>
                </button>
                <div style="color: var(--gray-text); font-size: 14px;">
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Promotion Applications Management</h1>
                <div class="header-actions">
                    <button class="action-btn btn-view" onclick="showAllApplications()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="action-btn btn-approve" onclick="exportApplications()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Applications</div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>

                <div class="stat-card approved">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label">Approved</div>
                </div>

                <div class="stat-card rejected">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['rejected']; ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="applications-container">
                <div class="section-header">
                    <h2>All Promotion Applications</h2>
                    <div class="table-filters">
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <input type="text" class="search-box" id="searchInput" placeholder="Search by name or department...">
                        <button class="action-btn btn-view" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>

                <?php if(mysqli_num_rows($result) > 0): ?>
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Faculty Member</th>
                            <th>Current Position</th>
                            <th>Applied For</th>
                            <th>API Score</th>
                            <th>Document</th>
                            <th>Remarks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $status_class = 'status-' . strtolower($row['status']);
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $row['id']; ?></strong>
                                <?php if(isset($row['created_at'])): ?>
                                <div style="font-size: 0.85rem; color: var(--gray-text); margin-top: 3px;">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="faculty-info">
                                    <div class="faculty-avatar">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                    </div>
                                    <div class="faculty-details">
                                        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                        <div class="department"><?php echo htmlspecialchars($row['department']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['current_position']); ?></td>
                            <td>
                                <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($row['promotion_to']); ?></strong>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: <?php echo $row['api_score'] >= 80 ? 'var(--success-color)' : 'var(--warning-color)'; ?>;">
                                    <?php echo round($row['api_score'], 2); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--gray-text);">
                                    <?php echo $row['api_score'] >= 80 ? 'Eligible' : 'Needs Improvement'; ?>
                                </div>
                            </td>
                            <td>
                                <?php if($row['document']): ?>
                                <a href="uploads/<?php echo $row['document']; ?>" target="_blank" class="document-link">
                                    <i class="fas fa-file-pdf"></i> View Document
                                </a>
                                <?php else: ?>
                                <span style="color: var(--gray-text); font-size: 0.9rem;">No document</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="remarks" title="<?php echo htmlspecialchars($row['remarks']); ?>">
                                    <?php echo htmlspecialchars($row['remarks']) ?: 'No remarks'; ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if($row['status'] == "Pending"): ?>
                                    <button class="action-btn btn-approve" onclick="approveApplication(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="action-btn btn-reject" onclick="rejectApplication(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <?php else: ?>
                                    <button class="action-btn btn-view" onclick="viewApplication(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-applications">
                    <i class="fas fa-inbox"></i>
                    <h3>No Promotion Applications Found</h3>
                    <p>There are currently no promotion applications to review.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="activity-section">
                <h3>Recent Activity</h3>
                <div class="activity-list">
                    <?php if(mysqli_num_rows($recent_updates) > 0): ?>
                        <?php while($activity = mysqli_fetch_assoc($recent_updates)): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <?php echo htmlspecialchars($activity['activity']); ?> for <?php echo htmlspecialchars($activity['faculty_name']); ?>
                                </div>
                                <div class="activity-time">
                                    Processed
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-content">
                                <div class="activity-text">
                                    No recent activity. All applications are pending review.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modal -->
    <div class="action-modal" id="actionModal">
        <div class="modal-content">
            <h2 id="modalTitle"></h2>
            <p id="modalMessage"></p>
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <button class="action-btn btn-reject" onclick="closeModal()">Cancel</button>
                <button class="action-btn btn-approve" id="modalConfirmBtn">Confirm</button>
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

        // Application actions
        let currentApplicationId = null;

        function approveApplication(appId) {
            currentApplicationId = appId;
            showModal(
                'Approve Application',
                'Are you sure you want to approve this promotion application? This action cannot be undone.',
                confirmApprove
            );
        }

        function rejectApplication(appId) {
            currentApplicationId = appId;
            showModal(
                'Reject Application',
                'Are you sure you want to reject this promotion application? Please provide a reason for rejection.',
                confirmReject
            );
        }

        function viewApplication(appId) {
            // In real implementation: window.location.href = `view_application.php?id=${appId}`;
            showNotification('Loading application details...', 'info');
        }

        function showModal(title, message, confirmCallback) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('modalConfirmBtn').onclick = confirmCallback;
            document.getElementById('actionModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('actionModal').style.display = 'none';
            currentApplicationId = null;
        }

        function confirmApprove() {
            if (currentApplicationId) {
                window.location.href = `approve.php?id=${currentApplicationId}&action=approve`;
            }
        }

        function confirmReject() {
            if (currentApplicationId) {
                window.location.href = `approve.php?id=${currentApplicationId}&action=reject`;
            }
        }

        // Filter functionality
        function applyFilters() {
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            const rows = document.querySelectorAll('.applications-table tbody tr');
            
            rows.forEach(row => {
                const status = row.querySelector('.status-badge').textContent.trim();
                const facultyName = row.querySelector('.faculty-details h4').textContent.toLowerCase();
                const department = row.querySelector('.department').textContent.toLowerCase();
                
                let showRow = true;
                
                // Status filter
                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }
                
                // Search filter
                if (searchTerm && !facultyName.includes(searchTerm) && !department.includes(searchTerm)) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
            
            showNotification('Filters applied successfully!', 'success');
        }

        function showAllApplications() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('searchInput').value = '';
            applyFilters();
        }

        function exportApplications() {
            showNotification('Exporting applications data...', 'info');
            // In real implementation, make an AJAX call to export data
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? '#2ed573' : type === 'info' ? '#2575fc' : '#ff4757'};
                color: white;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Close modal when clicking outside
        document.getElementById('actionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Enter key for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    </script>
</body>
</html>