<?php
session_start();
include "config.php";

// Allow only admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";

// Get statistics
$stats_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_docs,
        COUNT(DISTINCT faculty_id) as active_faculty,
        SUM(CASE WHEN category = 'Journal Paper' THEN 1 ELSE 0 END) as journals,
        SUM(CASE WHEN category = 'Conference Paper' THEN 1 ELSE 0 END) as conferences,
        SUM(CASE WHEN category = 'Book Chapter' THEN 1 ELSE 0 END) as book_chapters,
        SUM(CASE WHEN category = 'Research Proposal' THEN 1 ELSE 0 END) as proposals,
        SUM(CASE WHEN category = 'Patent' THEN 1 ELSE 0 END) as patents,
        SUM(CASE WHEN category = 'Technical Report' THEN 1 ELSE 0 END) as reports
    FROM research_uploads
");
$stats = mysqli_fetch_assoc($stats_query) ?: [];

// Fetch uploads with faculty details
$query = mysqli_query($conn, "
    SELECT 
        r.*, 
        f.name AS faculty_name,
        f.department,
        f.email,
        COUNT(DISTINCT r2.id) as total_uploads
    FROM research_uploads r
    LEFT JOIN faculty f ON r.faculty_id = f.id
    LEFT JOIN research_uploads r2 ON f.id = r2.faculty_id
    GROUP BY r.id
    ORDER BY r.uploaded_at DESC
");

// Get category distribution for chart
$categories_query = mysqli_query($conn, "
    SELECT 
        category,
        COUNT(*) as count
    FROM research_uploads
    GROUP BY category
    ORDER BY count DESC
");

// Get top faculty by uploads
$top_faculty_query = mysqli_query($conn, "
    SELECT 
        f.name,
        f.department,
        COUNT(r.id) as upload_count
    FROM faculty f
    LEFT JOIN research_uploads r ON f.id = r.faculty_id
    GROUP BY f.id
    HAVING upload_count > 0
    ORDER BY upload_count DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Documents | Admin Dashboard</title>
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

        .stat-card:nth-child(2) {
            border-top-color: var(--secondary-color);
        }

        .stat-card:nth-child(3) {
            border-top-color: var(--success-color);
        }

        .stat-card:nth-child(4) {
            border-top-color: var(--warning-color);
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

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, var(--secondary-color), #1e90ff);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, var(--success-color), #25b764);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, var(--warning-color), #ff9f43);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-card:nth-child(1) .stat-value {
            color: var(--primary-color);
        }

        .stat-card:nth-child(2) .stat-value {
            color: var(--secondary-color);
        }

        .stat-card:nth-child(3) .stat-value {
            color: var(--success-color);
        }

        .stat-card:nth-child(4) .stat-value {
            color: var(--warning-color);
        }

        .stat-label {
            color: var(--gray-text);
            font-size: 1rem;
        }

        /* Category Distribution */
        .category-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .category-item {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .category-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .category-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .category-info h4 {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .category-count {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Research Table */
        .research-table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            overflow-x: auto;
        }

        .table-filters {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 25px;
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
            flex: 1;
        }

        .research-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .research-table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .research-table th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .research-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .research-table tbody tr:hover {
            background: #f8f9fa;
        }

        .research-table td {
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

        /* Category Badge */
        .category-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }

        .category-journal {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(106, 17, 203, 0.2);
        }

        .category-conference {
            background: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(37, 117, 252, 0.2);
        }

        .category-book {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 213, 115, 0.2);
        }

        .category-proposal {
            background: rgba(255, 165, 2, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 165, 2, 0.2);
        }

        .category-patent {
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
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: #f8f9fa;
            color: var(--gray-text);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        /* Top Faculty */
        .top-faculty-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .top-faculty-section h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark-text);
        }

        .faculty-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .faculty-rank-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .rank-number {
            width: 40px;
            height: 40px;
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

        .rank-1 {
            background: linear-gradient(135deg, gold, #ffd700);
        }

        .rank-2 {
            background: linear-gradient(135deg, silver, #c0c0c0);
        }

        .rank-3 {
            background: linear-gradient(135deg, #cd7f32, #a0522d);
        }

        .faculty-rank-info {
            flex: 1;
        }

        .faculty-rank-info h4 {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 3px;
        }

        .faculty-rank-info .department {
            color: var(--gray-text);
            font-size: 0.85rem;
        }

        .upload-count {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        /* No Documents */
        .no-documents {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-text);
        }

        .no-documents i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.3;
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
            
            .research-table {
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
            
            .category-grid {
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
                    <i class="fas fa-flask"></i>
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
                    <a href="admin_promotion_request.php" class="nav-link">
                        <i class="fas fa-medal"></i>
                        <span>Promotion Applications</span>
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
                    <a href="admin_research_uploads.php" class="nav-link active">
                        <i class="fas fa-flask"></i>
                        <span>Research Documents</span>
                        <?php if(isset($stats['total_docs']) && $stats['total_docs'] > 0): ?>
                        <span style="background: var(--primary-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                            <?php echo $stats['total_docs']; ?>
                        </span>
                        <?php endif; ?>
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
                <h1>Research Documents</h1>
            </div>
            <div class="header-right">
                <div style="color: var(--gray-text); font-size: 14px;">
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Faculty Research Documents</h1>
                <div class="header-actions">
                    <button class="action-btn" onclick="refreshPage()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="action-btn" onclick="exportResearchData()">
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
                    <div class="stat-value"><?php echo isset($stats['total_docs']) ? $stats['total_docs'] : 0; ?></div>
                    <div class="stat-label">Total Documents</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo isset($stats['active_faculty']) ? $stats['active_faculty'] : 0; ?></div>
                    <div class="stat-label">Active Faculty</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-value"><?php echo isset($stats['journals']) ? $stats['journals'] : 0; ?></div>
                    <div class="stat-label">Journal Papers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-microphone"></i>
                    </div>
                    <div class="stat-value"><?php echo isset($stats['conferences']) ? $stats['conferences'] : 0; ?></div>
                    <div class="stat-label">Conference Papers</div>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="category-section">
                <div class="section-header">
                    <h2>Research Categories</h2>
                    <div style="color: var(--gray-text); font-size: 0.9rem;">
                        <?php 
                        $total_categories = mysqli_num_rows($categories_query);
                        echo "$total_categories categories";
                        ?>
                    </div>
                </div>
                <div class="category-grid">
                    <?php 
                    if(mysqli_num_rows($categories_query) > 0):
                        mysqli_data_seek($categories_query, 0); // Reset pointer
                        while($cat = mysqli_fetch_assoc($categories_query)):
                            $icon_class = getCategoryIcon($cat['category']);
                            $badge_class = getCategoryBadgeClass($cat['category']);
                    ?>
                    <div class="category-item">
                        <div class="category-icon">
                            <i class="<?php echo $icon_class; ?>"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo htmlspecialchars($cat['category']); ?></h4>
                            <div class="category-count"><?php echo $cat['count']; ?> docs</div>
                        </div>
                        <span class="category-badge <?php echo $badge_class; ?>">
                            <?php echo $cat['category']; ?>
                        </span>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="no-documents">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Categories Found</h3>
                        <p>Research documents haven't been categorized yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Research Documents Table -->
            <div class="research-table-container">
                <div class="section-header">
                    <h2>All Research Documents</h2>
                    <div style="color: var(--gray-text); font-size: 0.9rem;">
                        <?php 
                        $total_docs = mysqli_num_rows($query);
                        echo "$total_docs documents found";
                        ?>
                    </div>
                </div>
                
                <div class="table-filters">
                    <select class="filter-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="Journal Paper">Journal Papers</option>
                        <option value="Conference Paper">Conference Papers</option>
                        <option value="Book Chapter">Book Chapters</option>
                        <option value="Research Proposal">Research Proposals</option>
                        <option value="Patent">Patents</option>
                        <option value="Technical Report">Technical Reports</option>
                    </select>
                    <select class="filter-select" id="facultyFilter">
                        <option value="">All Faculty</option>
                        <?php
                        $faculty_list = mysqli_query($conn, "SELECT DISTINCT f.id, f.name FROM research_uploads r JOIN faculty f ON r.faculty_id = f.id ORDER BY f.name");
                        while($fac = mysqli_fetch_assoc($faculty_list)) {
                            echo '<option value="' . htmlspecialchars($fac['name']) . '">' . htmlspecialchars($fac['name']) . '</option>';
                        }
                        ?>
                    </select>
                    <input type="text" class="search-box" id="searchInput" placeholder="Search by title or department...">
                    <button class="action-btn" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>

                <?php if(mysqli_num_rows($query) > 0): ?>
                <table class="research-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Faculty</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Document</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($query, 0); // Reset pointer
                        while($row = mysqli_fetch_assoc($query)): 
                            $badge_class = getCategoryBadgeClass($row['category']);
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $row['id']; ?></strong>
                            </td>
                            <td>
                                <div class="faculty-info">
                                    <div class="faculty-avatar">
                                        <?php echo strtoupper(substr($row['faculty_name'], 0, 1)); ?>
                                    </div>
                                    <div class="faculty-details">
                                        <h4><?php echo htmlspecialchars($row['faculty_name']); ?></h4>
                                        <div class="department"><?php echo htmlspecialchars($row['department']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--dark-text); margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--gray-text);">
                                    Uploads: <?php echo $row['total_uploads']; ?>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="../uploads/research/<?php echo $row['filename']; ?>" target="_blank" class="document-link">
                                    <i class="fas fa-file-pdf"></i> View Document
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--dark-text);">
                                    <?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--gray-text);">
                                    <?php echo date('h:i A', strtotime($row['uploaded_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewDocument(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn" onclick="emailFaculty('<?php echo $row['email']; ?>')">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="action-btn" onclick="downloadDocument('<?php echo $row['filename']; ?>')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-documents">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Research Documents Found</h3>
                    <p>No faculty members have uploaded research documents yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Top Faculty -->
            <div class="top-faculty-section">
                <h3>Top Contributing Faculty</h3>
                <div class="faculty-list">
                    <?php if(mysqli_num_rows($top_faculty_query) > 0): 
                        $rank = 1;
                        while($faculty = mysqli_fetch_assoc($top_faculty_query)):
                    ?>
                    <div class="faculty-rank-item">
                        <div class="rank-number rank-<?php echo $rank; ?>">
                            <?php echo $rank; ?>
                        </div>
                        <div class="faculty-rank-info">
                            <h4><?php echo htmlspecialchars($faculty['name']); ?></h4>
                            <div class="department"><?php echo htmlspecialchars($faculty['department']); ?></div>
                        </div>
                        <div class="upload-count">
                            <?php echo $faculty['upload_count']; ?> docs
                        </div>
                    </div>
                    <?php 
                        $rank++;
                        endwhile; 
                    else:
                    ?>
                    <div class="no-documents">
                        <i class="fas fa-users"></i>
                        <h3>No Active Faculty</h3>
                        <p>No faculty members have uploaded research documents yet.</p>
                    </div>
                    <?php endif; ?>
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

        // Filter functionality
        function applyFilters() {
            const categoryFilter = document.getElementById('categoryFilter').value;
            const facultyFilter = document.getElementById('facultyFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            const rows = document.querySelectorAll('.research-table tbody tr');
            
            rows.forEach(row => {
                const category = row.querySelector('.category-badge').textContent.trim();
                const facultyName = row.querySelector('.faculty-details h4').textContent.toLowerCase();
                const title = row.querySelector('td:nth-child(3) div:first-child').textContent.toLowerCase();
                const department = row.querySelector('.department').textContent.toLowerCase();
                
                let showRow = true;
                
                // Category filter
                if (categoryFilter && category !== categoryFilter) {
                    showRow = false;
                }
                
                // Faculty filter
                if (facultyFilter && facultyName !== facultyFilter.toLowerCase()) {
                    showRow = false;
                }
                
                // Search filter
                if (searchTerm && !title.includes(searchTerm) && !department.includes(searchTerm) && !facultyName.includes(searchTerm)) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
            
            showNotification('Filters applied successfully!', 'success');
        }

        function refreshPage() {
            window.location.reload();
        }

        function exportResearchData() {
            showNotification('Exporting research data...', 'info');
            // In real implementation, make an AJAX call to export data
        }

        function viewDocument(docId) {
            showNotification(`Viewing document #${docId}`, 'info');
            // In real implementation: window.open(`view_document.php?id=${docId}`, '_blank');
        }

        function emailFaculty(email) {
            window.location.href = `mailto:${email}`;
        }

        function downloadDocument(filename) {
            window.open(`../uploads/research/${filename}`, '_blank');
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

        // Enter key for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });

        // Clear filters
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('categoryFilter').addEventListener('change', function() {
                if (this.value === '') applyFilters();
            });
            
            document.getElementById('facultyFilter').addEventListener('change', function() {
                if (this.value === '') applyFilters();
            });
        });
    </script>
</body>
</html>

<?php
// Helper functions
function getCategoryIcon($category) {
    $icons = [
        'Journal Paper' => 'fas fa-newspaper',
        'Conference Paper' => 'fas fa-microphone',
        'Book Chapter' => 'fas fa-book',
        'Research Proposal' => 'fas fa-file-alt',
        'Patent' => 'fas fa-certificate',
        'Technical Report' => 'fas fa-chart-bar'
    ];
    return $icons[$category] ?? 'fas fa-file-alt';
}

function getCategoryBadgeClass($category) {
    $classes = [
        'Journal Paper' => 'category-journal',
        'Conference Paper' => 'category-conference',
        'Book Chapter' => 'category-book',
        'Research Proposal' => 'category-proposal',
        'Patent' => 'category-patent',
        'Technical Report' => 'category-report'
    ];
    return $classes[$category] ?? 'category-journal';
}
?>