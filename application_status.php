<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$faculty_query = mysqli_query($conn, "SELECT name, role, email FROM faculty WHERE id='$faculty_id'");
$faculty_data = mysqli_fetch_assoc($faculty_query);
$faculty_name = $faculty_data['name'];

// Fetch all applications by this faculty
$query = mysqli_query($conn, 
    "SELECT * FROM promotion_applications WHERE faculty_id='$faculty_id' ORDER BY created_at DESC");

// Get counts for statistics
$stats_query = mysqli_query($conn,
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM promotion_applications WHERE faculty_id='$faculty_id'");
$stats = mysqli_fetch_assoc($stats_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status | Career Advancement System</title>
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
            --pending-color: #ffa502;
            --approved-color: #2ed573;
            --rejected-color: #ff4757;
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

        /* Sidebar Navigation - Same as Dashboard */
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .stat-card.total { border-left-color: var(--primary-color); }
        .stat-card.approved { border-left-color: var(--approved-color); }
        .stat-card.pending { border-left-color: var(--pending-color); }
        .stat-card.rejected { border-left-color: var(--rejected-color); }

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

        .stat-card.approved .stat-icon { background: linear-gradient(135deg, var(--approved-color), #27ae60); }
        .stat-card.pending .stat-icon { background: linear-gradient(135deg, var(--pending-color), #e67e22); }
        .stat-card.rejected .stat-icon { background: linear-gradient(135deg, var(--rejected-color), #c0392b); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card.approved .stat-value { background: linear-gradient(135deg, var(--approved-color), #27ae60); -webkit-background-clip: text; }
        .stat-card.pending .stat-value { background: linear-gradient(135deg, var(--pending-color), #e67e22); -webkit-background-clip: text; }
        .stat-card.rejected .stat-value { background: linear-gradient(135deg, var(--rejected-color), #c0392b); -webkit-background-clip: text; }

        .stat-label {
            color: var(--text-gray);
            font-size: 14px;
            font-weight: 500;
        }

        /* Applications Table */
        .applications-table {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .table-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-container {
            overflow-x: auto;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .status-table th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 15px;
            border: none;
        }

        .status-table th:first-child {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .status-table th:last-child {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .status-table td {
            padding: 20px 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
            font-size: 14.5px;
        }

        .status-table tr:hover td {
            background-color: rgba(106, 17, 203, 0.03);
        }

        .status-table tr:last-child td {
            border-bottom: none;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            min-width: 90px;
        }

        .status-pending {
            background: rgba(255, 165, 2, 0.1);
            color: var(--pending-color);
            border: 1px solid rgba(255, 165, 2, 0.3);
        }

        .status-approved {
            background: rgba(46, 213, 115, 0.1);
            color: var(--approved-color);
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .status-rejected {
            background: rgba(255, 71, 87, 0.1);
            color: var(--rejected-color);
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        /* API Score Display */
        .api-score {
            font-weight: 700;
            font-size: 16px;
            color: var(--primary-color);
        }

        /* Category Scores */
        .category-score {
            font-weight: 600;
            font-size: 14px;
        }

        .cat1-score { color: #6a11cb; }
        .cat2-score { color: #2575fc; }
        .cat3-score { color: #2ed573; }

        /* No Applications Message */
        .no-applications {
            text-align: center;
            padding: 60px 20px;
        }

        .no-applications i {
            font-size: 64px;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .no-applications h4 {
            font-size: 22px;
            color: var(--text-gray);
            margin-bottom: 15px;
        }

        .no-applications p {
            color: var(--text-gray);
            margin-bottom: 25px;
            max-width: 400px;
            margin: 0 auto 25px;
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
            
            .welcome-banner {
                padding: 25px;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }

        /* Date Format */
        .date-cell {
            font-size: 13.5px;
            color: var(--text-gray);
            font-weight: 500;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(106, 17, 203, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--light-bg);
            transform: translateY(-2px);
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
                    <i class="fas fa-chart-line"></i>
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
                    <a href="promotion_status.php" class="nav-link active">
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
                <h1>Application Status</h1>
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
                <h2><i class="fas fa-clipboard-check"></i> Application Status Dashboard</h2>
                <p>Track all your promotion applications, view status updates, and monitor your API scores</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Applications</div>
                </div>

                <div class="stat-card approved">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['approved'] ?? 0; ?></div>
                    <div class="stat-label">Approved</div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>

                <div class="stat-card rejected">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['rejected'] ?? 0; ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="applications-table">
                <div class="table-header">
                    <h3><i class="fas fa-list-alt"></i> All Promotion Applications</h3>
                    <div>
                        <a href="apply_promotion.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> New Application
                        </a>
                    </div>
                </div>

                <div class="table-container">
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>API Score</th>
                                    <th>Category I</th>
                                    <th>Category II</th>
                                    <th>Category III</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><span class="api-score"><?php echo $row['api_score']; ?> pts</span></td>
                                    <td><span class="category-score cat1-score"><?php echo $row['cat1']; ?> pts</span></td>
                                    <td><span class="category-score cat2-score"><?php echo $row['cat2']; ?> pts</span></td>
                                    <td><span class="category-score cat3-score"><?php echo $row['cat3']; ?> pts</span></td>
                                    <td>
                                        <?php if($row['status'] == "Pending"): ?>
                                            <span class="status-badge status-pending">Pending</span>
                                        <?php elseif($row['status'] == "Approved"): ?>
                                            <span class="status-badge status-approved">Approved</span>
                                        <?php elseif($row['status'] == "Rejected"): ?>
                                            <span class="status-badge status-rejected">Rejected</span>
                                        <?php else: ?>
                                            <span class="status-badge"><?php echo $row['status']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date-cell">
                                        <?php 
                                        $date = new DateTime($row['created_at']);
                                        echo $date->format('M d, Y');
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-applications">
                            <i class="fas fa-file-alt"></i>
                            <h4>No Applications Found</h4>
                            <p>You haven't submitted any promotion applications yet. Start your journey by submitting your first application.</p>
                            <a href="apply_promotion.php" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Apply for Promotion
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="advanced_api_form.php" class="btn btn-primary">
                    <i class="fas fa-calculator"></i> Calculate API Score
                </a>
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
            // Add hover effects
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
        });

        // Print functionality
        function printApplications() {
            window.print();
        }
    </script>
</body>
</html>