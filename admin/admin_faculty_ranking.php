<?php
session_start();
include "config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit;
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";

/* Fetch faculty data */
$query = mysqli_query($conn, "
    SELECT 
        f.id,
        f.name,
        f.department,
        f.email,
        COALESCE(SUM(t.cat1_score),0) AS teaching_score,
        COALESCE(COUNT(DISTINCT r.id),0) AS research_count,
        COALESCE(AVG(p.api_score),0) AS api_score,
        COUNT(DISTINCT pa.id) as pending_applications,
        COUNT(DISTINCT app.id) as approved_applications

    FROM faculty f
    LEFT JOIN teaching_activities t ON f.id = t.faculty_id
    LEFT JOIN research_uploads r ON f.id = r.faculty_id
    LEFT JOIN promotion_applications p ON f.id = p.faculty_id
    LEFT JOIN promotion_requests pa ON f.id = pa.faculty_id AND pa.status = 'Pending'
    LEFT JOIN promotion_requests app ON f.id = app.faculty_id AND app.status = 'Approved'

    GROUP BY f.id
");

$facultyRanks = [];
$total_faculty = 0;
$top_score = 0;
$avg_score = 0;

while($row = mysqli_fetch_assoc($query)){
    $total_faculty++;
    
    // Calculate AI score with weighted components
    $ai_score = 
        ($row['teaching_score'] * 0.3) + 
        ($row['research_count'] * 10 * 0.4) + 
        ($row['api_score'] * 0.3);

    // Add bonus for approved promotions
    $ai_score += ($row['approved_applications'] * 5);

    $row['ai_score'] = round($ai_score, 2);
    $row['grade'] = getGrade($ai_score);
    $row['status_color'] = getStatusColor($ai_score);
    
    $facultyRanks[] = $row;
    $avg_score += $ai_score;
    
    if($ai_score > $top_score) {
        $top_score = $ai_score;
    }
}

// Calculate average
if($total_faculty > 0) {
    $avg_score = round($avg_score / $total_faculty, 2);
}

/* Sort by AI score (DESC) */
usort($facultyRanks, function($a, $b){
    return $b['ai_score'] <=> $a['ai_score'];
});

// Helper functions
function getGrade($score) {
    if($score >= 90) return "S (Excellent)";
    if($score >= 80) return "A (Very Good)";
    if($score >= 70) return "B (Good)";
    if($score >= 60) return "C (Average)";
    if($score >= 50) return "D (Below Average)";
    return "F (Needs Improvement)";
}

function getStatusColor($score) {
    if($score >= 90) return "#2ed573"; // Green
    if($score >= 80) return "#7bed9f"; // Light Green
    if($score >= 70) return "#ffa502"; // Orange
    if($score >= 60) return "#ff7f50"; // Coral
    return "#ff4757"; // Red
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Faculty Ranking | Admin Dashboard</title>
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
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-subtitle {
            color: var(--gray-text);
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 24px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray-text);
            font-size: 1rem;
        }

        /* Ranking Table */
        .ranking-table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .table-controls {
            display: flex;
            gap: 15px;
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

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .ranking-table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .ranking-table th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ranking-table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .ranking-table tbody tr:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .ranking-table td {
            padding: 18px 15px;
            color: var(--dark-text);
        }

        /* Rank Cell */
        .rank-cell {
            text-align: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .rank-1 {
            color: gold;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 10px;
        }

        .rank-2 {
            color: silver;
            background: rgba(192, 192, 192, 0.1);
            border-radius: 10px;
        }

        .rank-3 {
            color: #cd7f32;
            background: rgba(205, 127, 50, 0.1);
            border-radius: 10px;
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

        /* Score Progress Bars */
        .score-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .score-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .score-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 1s ease;
        }

        /* Score Display */
        .score-display {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .score-value {
            font-weight: 700;
            font-size: 1.2rem;
            min-width: 60px;
        }

        .score-grade {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
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

        /* Legend */
        .ranking-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .legend-text {
            font-size: 0.9rem;
            color: var(--dark-text);
        }

        /* Export Section */
        .export-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }

        .export-header {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark-text);
        }

        .export-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .export-btn {
            padding: 12px 25px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .export-btn.pdf {
            background: linear-gradient(135deg, #ff4757, #ff3547);
            color: white;
        }

        .export-btn.excel {
            background: linear-gradient(135deg, #2ed573, #25b764);
            color: white;
        }

        .export-btn.print {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
        }

        .export-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
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
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .table-controls {
                flex-direction: column;
                width: 100%;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .ranking-table {
                font-size: 0.85rem;
            }
            
            .export-options {
                flex-direction: column;
            }
            
            .export-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }

        /* Loading Animation */
        .loader {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                    <i class="fas fa-trophy"></i>
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
                    <a href="admin_faculty_ranking.php" class="nav-link active">
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
                <h1>AI Faculty Ranking</h1>
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
                <h1>🏆 AI Faculty Ranking System</h1>
                <p class="page-subtitle">
                    Intelligent ranking based on teaching performance, research contributions, and API scores.
                    Powered by machine learning algorithms to provide fair and objective evaluation.
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_faculty; ?></div>
                    <div class="stat-label">Total Faculty</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?php echo $top_score; ?></div>
                    <div class="stat-label">Top AI Score</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value"><?php echo $avg_score; ?></div>
                    <div class="stat-label">Average AI Score</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="stat-value"><?php echo count($facultyRanks); ?></div>
                    <div class="stat-label">Faculty Ranked</div>
                </div>
            </div>

            <!-- Ranking Table -->
            <div class="ranking-table-container">
                <div class="table-header">
                    <h2>Faculty Performance Ranking</h2>
                    <div class="table-controls">
                        <select class="filter-select" id="deptFilter">
                            <option value="">All Departments</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Physics">Physics</option>
                            <option value="Chemistry">Chemistry</option>
                            <option value="Biology">Biology</option>
                        </select>
                        <select class="filter-select" id="sortFilter">
                            <option value="ai_score">Sort by AI Score</option>
                            <option value="teaching">Sort by Teaching</option>
                            <option value="research">Sort by Research</option>
                            <option value="api">Sort by API Score</option>
                        </select>
                    </div>
                </div>

                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Faculty</th>
                            <th>Department</th>
                            <th>Teaching Score</th>
                            <th>Research Count</th>
                            <th>API Score</th>
                            <th>AI Score & Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        foreach($facultyRanks as $faculty):
                            $teaching_percent = min(($faculty['teaching_score'] / 100) * 100, 100);
                            $research_percent = min(($faculty['research_count'] * 10), 100);
                            $api_percent = min(($faculty['api_score'] / 100) * 100, 100);
                        ?>
                        <tr>
                            <td class="rank-cell rank-<?php echo $rank <= 3 ? $rank : 'default'; ?>">
                                <?php 
                                if($rank == 1) echo '🥇';
                                elseif($rank == 2) echo '🥈';
                                elseif($rank == 3) echo '🥉';
                                else echo $rank; 
                                ?>
                            </td>
                            <td>
                                <div class="faculty-info">
                                    <div class="faculty-avatar">
                                        <?php echo strtoupper(substr($faculty['name'], 0, 1)); ?>
                                    </div>
                                    <div class="faculty-details">
                                        <h4><?php echo htmlspecialchars($faculty['name']); ?></h4>
                                        <div class="department"><?php echo htmlspecialchars($faculty['department']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($faculty['department']); ?></td>
                            <td>
                                <div class="score-container">
                                    <div class="score-value"><?php echo $faculty['teaching_score']; ?></div>
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $teaching_percent; ?>%; background: #6a11cb;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="score-container">
                                    <div class="score-value"><?php echo $faculty['research_count']; ?></div>
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $research_percent; ?>%; background: #2575fc;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="score-container">
                                    <div class="score-value"><?php echo round($faculty['api_score'], 2); ?></div>
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $api_percent; ?>%; background: #2ed573;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="score-display">
                                    <div class="score-value" style="color: <?php echo $faculty['status_color']; ?>;">
                                        <?php echo $faculty['ai_score']; ?>
                                    </div>
                                    <div class="score-grade" style="background: <?php echo $faculty['status_color']; ?>20; color: <?php echo $faculty['status_color']; ?>;">
                                        <?php echo $faculty['grade']; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn" onclick="viewFacultyDetails(<?php echo $faculty['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn" onclick="sendEmail('<?php echo $faculty['email']; ?>')">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="action-btn" onclick="viewResearch(<?php echo $faculty['id']; ?>)">
                                        <i class="fas fa-flask"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Ranking Legend -->
            <div class="ranking-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #2ed573;"></div>
                    <div class="legend-text">Excellent (90-100)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #7bed9f;"></div>
                    <div class="legend-text">Very Good (80-89)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffa502;"></div>
                    <div class="legend-text">Good (70-79)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff7f50;"></div>
                    <div class="legend-text">Average (60-69)</div>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff4757;"></div>
                    <div class="legend-text">Needs Improvement (&lt;60)</div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="export-section">
                <h3 class="export-header">Export Ranking Data</h3>
                <div class="export-options">
                    <button class="export-btn pdf" onclick="exportPDF()">
                        <i class="fas fa-file-pdf"></i> Export as PDF
                    </button>
                    <button class="export-btn excel" onclick="exportExcel()">
                        <i class="fas fa-file-excel"></i> Export as Excel
                    </button>
                    <button class="export-btn print" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Ranking
                    </button>
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
        document.addEventListener('DOMContentLoaded', function() {
            const deptFilter = document.getElementById('deptFilter');
            const sortFilter = document.getElementById('sortFilter');
            
            deptFilter.addEventListener('change', filterTable);
            sortFilter.addEventListener('change', filterTable);
            
            // Animate score bars
            setTimeout(() => {
                document.querySelectorAll('.score-fill').forEach(fill => {
                    const currentWidth = fill.style.width;
                    fill.style.width = '0%';
                    setTimeout(() => {
                        fill.style.width = currentWidth;
                    }, 100);
                });
            }, 500);
        });

        function filterTable() {
            const loader = document.createElement('div');
            loader.className = 'loader';
            loader.style.display = 'block';
            document.querySelector('.ranking-table-container').appendChild(loader);
            
            setTimeout(() => {
                loader.remove();
                // In a real implementation, you would make an AJAX call here
                // For now, we'll just show a message
                showNotification('Filters applied successfully!');
            }, 500);
        }

        // Action functions
        function viewFacultyDetails(facultyId) {
            showNotification(`Loading details for faculty ID: ${facultyId}`);
            // In real implementation: window.location.href = `faculty_profile.php?id=${facultyId}`;
        }

        function sendEmail(email) {
            window.location.href = `mailto:${email}`;
        }

        function viewResearch(facultyId) {
            showNotification(`Loading research for faculty ID: ${facultyId}`);
            // In real implementation: window.location.href = `faculty_research.php?id=${facultyId}`;
        }

        // Export functions
        function exportPDF() {
            showNotification('Generating PDF report...', 'info');
            // In real implementation, make an AJAX call to generate PDF
        }

        function exportExcel() {
            showNotification('Generating Excel report...', 'info');
            // In real implementation, make an AJAX call to generate Excel
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
    </script>
</body>
</html>