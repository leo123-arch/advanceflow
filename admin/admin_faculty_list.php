<?php
session_start();
include "config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : "admin@university.edu";

// Fetch statistics from promotion_requests table
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests"))['c'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Pending'"))['c'];

// Try to get password requests count
$password_requests = 0;
$password_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'password_reset_requests'");
if(mysqli_num_rows($password_table_exists) > 0) {
    $password_result = mysqli_query($conn, "SELECT COUNT(*) AS c FROM password_reset_requests WHERE status='pending'");
    if($password_result) {
        $password_requests = mysqli_fetch_assoc($password_result)['c'];
    }
}

// First, let's check what columns exist in the faculty table
$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM faculty");
$faculty_columns = [];
$available_columns = [];
while($col = mysqli_fetch_assoc($columns_result)) {
    $faculty_columns[] = $col['Field'];
    $available_columns[$col['Field']] = true;
}

// Debug: Show available columns
// echo "<pre>Available columns: " . print_r($faculty_columns, true) . "</pre>";

// Build search query based on available columns
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$department = isset($_GET['department']) ? mysqli_real_escape_string($conn, $_GET['department']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query = "SELECT * FROM faculty WHERE 1=1";

if (!empty($search)) {
    $search_conditions = [];
    
    // Check which columns are available for searching
    if(isset($available_columns['name'])) {
        $search_conditions[] = "name LIKE '%$search%'";
    }
    if(isset($available_columns['email'])) {
        $search_conditions[] = "email LIKE '%$search%'";
    }
    if(isset($available_columns['employee_id'])) {
        $search_conditions[] = "employee_id LIKE '%$search%'";
    }
    if(isset($available_columns['faculty_id'])) {
        $search_conditions[] = "faculty_id LIKE '%$search%'";
    }
    if(isset($available_columns['id'])) {
        $search_conditions[] = "id LIKE '%$search%'";
    }
    
    if(!empty($search_conditions)) {
        $query .= " AND (" . implode(" OR ", $search_conditions) . ")";
    }
}

if (!empty($department) && $department != 'all' && isset($available_columns['department'])) {
    $query .= " AND department = '$department'";
}

if (!empty($status_filter) && $status_filter != 'all' && isset($available_columns['status'])) {
    $query .= " AND status = '$status_filter'";
}

// Determine ordering column
$order_by = 'name';
if(isset($available_columns['name'])) {
    $order_by = 'name';
} elseif(isset($available_columns['id'])) {
    $order_by = 'id';
}

$query .= " ORDER BY $order_by ASC";

// Get distinct departments for filter
$departments = [];
if(isset($available_columns['department'])) {
    $dept_query = mysqli_query($conn, "SELECT DISTINCT department FROM faculty WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
    while($dept = mysqli_fetch_assoc($dept_query)) {
        $departments[] = $dept['department'];
    }
}

// Execute the query
$result = mysqli_query($conn, $query);
if(!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Pagination
$per_page = 10;
$total_faculty = mysqli_num_rows($result);
$total_pages = ceil($total_faculty / $per_page);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $per_page;

// Re-fetch with pagination
$pagination_query = $query . " LIMIT $offset, $per_page";
$result = mysqli_query($conn, $pagination_query);
if(!$result) {
    die("Pagination query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Management | Career Advancement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Include all CSS from the dashboard here */
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

        /* Sidebar Navigation - Same as dashboard */
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
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-text);
        }

        .page-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .filter-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-text);
        }

        .filter-select {
            padding: 15px 20px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            background: white;
            min-width: 180px;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.2);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: var(--dark-text);
            border: 1px solid #e9ecef;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        /* Faculty Table */
        .faculty-table-container {
            background: white;
            border-radius: 20px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .table-header {
            padding: 25px 25px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-text);
        }

        .table-header .count {
            color: var(--primary-color);
            font-weight: 600;
        }

        .faculty-table {
            width: 100%;
            border-collapse: collapse;
        }

        .faculty-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .faculty-table th {
            padding: 20px;
            text-align: left;
            font-weight: 600;
            color: var(--gray-text);
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .faculty-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .faculty-table tbody tr:hover {
            background: #f8f9fa;
        }

        .faculty-table td {
            padding: 20px;
            vertical-align: middle;
        }

        .faculty-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .faculty-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        .faculty-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .faculty-details h4 {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .faculty-details .email {
            color: var(--gray-text);
            font-size: 14px;
        }

        .department-badge {
            padding: 8px 15px;
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
        }

        .status-inactive {
            background: rgba(255, 71, 87, 0.1);
            color: var(--danger-color);
        }

        .status-pending {
            background: rgba(255, 165, 2, 0.1);
            color: var(--warning-color);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
            color: var(--gray-text);
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-btn.view {
            color: var(--primary-color);
        }

        .action-btn.view:hover {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.1));
        }

        .action-btn.edit {
            color: var(--warning-color);
        }

        .action-btn.edit:hover {
            background: rgba(255, 165, 2, 0.1);
        }

        .action-btn.delete {
            color: var(--danger-color);
        }

        .action-btn.delete:hover {
            background: rgba(255, 71, 87, 0.1);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #e9ecef;
            color: var(--gray-text);
            cursor: pointer;
            transition: all 0.3s;
        }

        .pagination-btn:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }

        .pagination-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-text);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--dark-text);
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
            
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .faculty-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
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
                    <a href="admin_faculty_list.php" class="nav-link active">
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
                <h1>Faculty Management</h1>
            </div>
            <div class="header-right">
                <button class="notification-btn" onclick="window.location.href='admin_promotion_request.php'">
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
            <!-- Page Header -->
            <div class="page-header">
                <h1>👩‍🏫 Faculty Members</h1>
                <div class="page-actions">
                    <a href="admin_add_faculty.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Add Faculty
                    </a>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        Print List
                    </button>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <form method="GET" action="" class="filter-row">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Search by name, email, or ID..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <?php if(isset($available_columns['department']) && !empty($departments)): ?>
                    <select name="department" class="filter-select">
                        <option value="all">All Departments</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" 
                                <?php echo $department == $dept ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    
                    <?php if(isset($available_columns['status'])): ?>
                    <select name="status" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    
                    <?php if($search || ($department != 'all' && !empty($department)) || ($status_filter != 'all' && !empty($status_filter))): ?>
                    <a href="admin_faculty_list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Clear Filters
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Faculty Table -->
            <div class="faculty-table-container">
                <div class="table-header">
                    <h3>Faculty Members <span class="count">(<?php echo $total_faculty; ?>)</span></h3>
                    <div style="color: var(--gray-text); font-size: 14px;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </div>
                </div>
                
                <?php if(mysqli_num_rows($result) > 0): ?>
                <table class="faculty-table">
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <?php if(isset($available_columns['department'])): ?>
                            <th>Department</th>
                            <?php endif; ?>
                            <?php 
                            // Determine which designation/position column to show
                            $show_designation = false;
                            $designation_col_name = '';
                            
                            if(isset($available_columns['current_designation'])) {
                                $show_designation = true;
                                $designation_col_name = 'current_designation';
                            } elseif(isset($available_columns['designation'])) {
                                $show_designation = true;
                                $designation_col_name = 'designation';
                            } elseif(isset($available_columns['position'])) {
                                $show_designation = true;
                                $designation_col_name = 'position';
                            } elseif(isset($available_columns['current_position'])) {
                                $show_designation = true;
                                $designation_col_name = 'current_position';
                            } elseif(isset($available_columns['job_title'])) {
                                $show_designation = true;
                                $designation_col_name = 'job_title';
                            }
                            
                            if($show_designation): ?>
                            <th>Position</th>
                            <?php endif; ?>
                            
                            <?php if(isset($available_columns['qualification'])): ?>
                            <th>Qualification</th>
                            <?php endif; ?>
                            
                            <?php if(isset($available_columns['experience'])): ?>
                            <th>Experience</th>
                            <?php endif; ?>
                            
                            <?php if(isset($available_columns['status'])): ?>
                            <th>Status</th>
                            <?php endif; ?>
                            
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $image = isset($available_columns['profile_image']) && !empty($row['profile_image']) && file_exists("uploads/" . $row['profile_image']) 
                                    ? "uploads/" . $row['profile_image'] 
                                    : null;
                            
                            // Determine status
                            $status = 'active';
                            $status_class = 'status-active';
                            if(isset($available_columns['status']) && isset($row['status'])) {
                                $status = $row['status'];
                                $status_class = 'status-' . $status;
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="faculty-info">
                                    <div class="faculty-avatar">
                                        <?php if($image): ?>
                                            <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($row['name'] ?? 'Unknown'); ?>">
                                        <?php else: ?>
                                            <?php echo isset($row['name']) ? strtoupper(substr($row['name'], 0, 1)) : '?'; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="faculty-details">
                                        <h4><?php echo htmlspecialchars($row['name'] ?? 'Unknown'); ?></h4>
                                        <?php if(isset($available_columns['email'])): ?>
                                        <div class="email"><?php echo htmlspecialchars($row['email'] ?? ''); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <?php if(isset($available_columns['department'])): ?>
                            <td>
                                <span class="department-badge">
                                    <?php echo htmlspecialchars($row['department'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            
                            <?php if($show_designation): ?>
                            <td>
                                <?php echo htmlspecialchars($row[$designation_col_name] ?? 'N/A'); ?>
                            </td>
                            <?php endif; ?>
                            
                            <?php if(isset($available_columns['qualification'])): ?>
                            <td>
                                <?php echo htmlspecialchars($row['qualification'] ?? 'N/A'); ?>
                            </td>
                            <?php endif; ?>
                            
                            <?php if(isset($available_columns['experience'])): ?>
                            <td>
                                <?php echo htmlspecialchars($row['experience'] ?? '0'); ?> yrs
                            </td>
                            <?php endif; ?>
                            
                            <?php if(isset($available_columns['status'])): ?>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            
                            <td>
                                <div class="action-buttons">
                                    <?php if(isset($row['id'])): ?>
                                    <a href="view_faculty.php?id=<?php echo $row['id']; ?>" 
                                       class="action-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_faculty.php?id=<?php echo $row['id']; ?>" 
                                       class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="action-btn delete" 
                                            onclick="confirmDelete(<?php echo $row['id']; ?>)" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No Faculty Found</h3>
                    <p>No faculty members match your search criteria.</p>
                    <?php if($search || ($department != 'all' && !empty($department)) || ($status_filter != 'all' && !empty($status_filter))): ?>
                    <a href="admin_faculty_list.php" class="btn btn-primary" style="margin-top: 20px;">
                        Clear Filters
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $department != 'all' ? '&department=' . urlencode($department) : ''; ?><?php echo $status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''; ?>"
                       class="pagination-btn <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angles-left"></i>
                    </a>
                    <a href="?page=<?php echo max(1, $page-1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $department != 'all' ? '&department=' . urlencode($department) : ''; ?><?php echo $status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''; ?>"
                       class="pagination-btn <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for($i = $start; $i <= $end; $i++): 
                    ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $department != 'all' ? '&department=' . urlencode($department) : ''; ?><?php echo $status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''; ?>"
                       class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <a href="?page=<?php echo min($total_pages, $page+1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $department != 'all' ? '&department=' . urlencode($department) : ''; ?><?php echo $status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''; ?>"
                       class="pagination-btn <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $department != 'all' ? '&department=' . urlencode($department) : ''; ?><?php echo $status_filter != 'all' ? '&status=' . urlencode($status_filter) : ''; ?>"
                       class="pagination-btn <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angles-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                <div style="background: white; padding: 20px; border-radius: 15px; text-align: center;">
                    <h3 style="color: var(--gray-text); margin-bottom: 10px;">Total Faculty</h3>
                    <div style="font-size: 32px; font-weight: 700; color: var(--primary-color);">
                        <?php echo $total_faculty; ?>
                    </div>
                </div>
                <?php if(isset($available_columns['department'])): ?>
                <div style="background: white; padding: 20px; border-radius: 15px; text-align: center;">
                    <h3 style="color: var(--gray-text); margin-bottom: 10px;">Departments</h3>
                    <div style="font-size: 32px; font-weight: 700; color: var(--secondary-color);">
                        <?php echo count($departments); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if(isset($available_columns['status'])): 
                    $active_count = mysqli_fetch_assoc(mysqli_query($conn, 
                        "SELECT COUNT(*) as c FROM faculty WHERE status='active'"))['c'];
                ?>
                <div style="background: white; padding: 20px; border-radius: 15px; text-align: center;">
                    <h3 style="color: var(--gray-text); margin-bottom: 10px;">Active Faculty</h3>
                    <div style="font-size: 32px; font-weight: 700; color: var(--success-color);">
                        <?php echo $active_count; ?>
                    </div>
                </div>
                <?php endif; ?>
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

        // Delete confirmation
        function confirmDelete(facultyId) {
            if (confirm('Are you sure you want to delete this faculty member? This action cannot be undone.')) {
                window.location.href = 'delete_faculty.php?id=' + facultyId;
            }
        }

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

        // Initialize date
        updateDateTime();
        
        // Update time every minute
        setInterval(updateDateTime, 60000);

        // Search with debounce
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        }

        // Auto-submit filters on change
        const filterSelects = document.querySelectorAll('select[name="department"], select[name="status"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>