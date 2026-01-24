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

// Check if application ID is provided
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($app_id > 0) {
    // Fetch application details
    $sql = "
        SELECT pr.*, f.name, f.department, f.id AS faculty_owner
        FROM promotion_requests pr
        LEFT JOIN faculty f ON pr.faculty_id = f.id
        WHERE pr.id = '$app_id'
    ";
    
    $res = mysqli_query($conn, $sql);
    if(mysqli_num_rows($res) == 0) {
        $error_message = "Application not found!";
    } else {
        $data = mysqli_fetch_assoc($res);
        
        // Check ownership
        if($faculty_id != $data['faculty_owner']){
            $error_message = "Access denied. This is not your application.";
        } 
        // Check status
        elseif($data['status'] != "Approved"){
            $error_message = "Certificate only available for approved applications.";
        } else {
            $application_data = $data;
        }
    }
}

// Fetch all approved applications
$approved_query = mysqli_query($conn, "
    SELECT pr.*, f.name, f.department 
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    WHERE pr.faculty_id = '$faculty_id' 
    AND pr.status = 'Approved'
    ORDER BY pr.created_at DESC
");

// Count certificates
$count_query = mysqli_query($conn, "
    SELECT COUNT(*) as total_certificates
    FROM promotion_requests 
    WHERE faculty_id = '$faculty_id' 
    AND status = 'Approved'
");
$cert_stats = mysqli_fetch_assoc($count_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Certificate | Career Advancement System</title>
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

        /* Certificate Banner */
        .certificate-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(106, 17, 203, 0.2);
        }

        .certificate-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .certificate-banner h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .certificate-banner p {
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

        .stat-card.pending { border-left-color: var(--warning-color); }
        .stat-card.approved { border-left-color: var(--success-color); }

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

        .stat-card.pending .stat-icon { background: linear-gradient(135deg, var(--warning-color), #e67e22); }
        .stat-card.approved .stat-icon { background: linear-gradient(135deg, var(--success-color), #27ae60); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card.pending .stat-value { background: linear-gradient(135deg, var(--warning-color), #e67e22); -webkit-background-clip: text; }
        .stat-card.approved .stat-value { background: linear-gradient(135deg, var(--success-color), #27ae60); -webkit-background-clip: text; }

        .stat-label {
            color: var(--text-gray);
            font-size: 14px;
            font-weight: 500;
        }

        /* Certificate Preview */
        .certificate-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            text-align: center;
        }

        .certificate-header {
            margin-bottom: 30px;
        }

        .certificate-header h3 {
            font-size: 26px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .certificate-header p {
            color: var(--text-gray);
            font-size: 16px;
        }

        .certificate-preview {
            background: linear-gradient(135deg, #fffbf0, #fff5e6);
            border: 3px solid #d4af37;
            border-radius: 15px;
            padding: 40px;
            margin: 0 auto 30px;
            max-width: 800px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .certificate-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M20,50 Q50,20 80,50 Q50,80 20,50 Z" fill="none" stroke="%23d4af37" stroke-width="0.5" opacity="0.3"/></svg>');
        }

        .certificate-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
        }

        .certificate-title {
            font-size: 28px;
            font-weight: 700;
            color: #8b4513;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .certificate-content {
            font-size: 18px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 30px;
        }

        .recipient-name {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .certificate-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-item {
            background: rgba(212, 175, 55, 0.1);
            padding: 15px;
            border-radius: 10px;
            border-left: 3px solid #d4af37;
        }

        .detail-label {
            font-size: 12px;
            color: #8b4513;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
            margin-top: 5px;
        }

        .signature-area {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px dashed #d4af37;
        }

        .signature {
            font-family: 'Brush Script MT', cursive;
            font-size: 24px;
            color: #8b4513;
            margin-bottom: 10px;
        }

        .signature-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Download Button */
        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 40px;
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(46, 213, 115, 0.3);
            margin-top: 20px;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(46, 213, 115, 0.4);
        }

        .download-btn:active {
            transform: translateY(-1px);
        }

        /* Certificates Table */
        .certificates-table {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .table-header {
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

        /* Action Buttons */
        .action-btn {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .action-download {
            background: linear-gradient(to right, var(--success-color), #27ae60);
            color: white;
        }

        .action-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 213, 115, 0.3);
        }

        /* No Certificates Message */
        .no-certificates {
            text-align: center;
            padding: 60px 20px;
        }

        .no-certificates i {
            font-size: 64px;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .no-certificates h4 {
            font-size: 22px;
            color: var(--text-gray);
            margin-bottom: 15px;
        }

        .no-certificates p {
            color: var(--text-gray);
            margin-bottom: 25px;
            max-width: 400px;
            margin: 0 auto 25px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background-color: #ffeaea;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .alert-success {
            background-color: #e7f7ef;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert i {
            font-size: 20px;
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
            
            .certificate-banner {
                padding: 25px;
            }
            
            .certificate-preview {
                padding: 25px;
            }
            
            .certificate-title {
                font-size: 22px;
            }
            
            .recipient-name {
                font-size: 24px;
            }
            
            .certificate-content {
                font-size: 16px;
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
                    <i class="fas fa-certificate"></i>
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
                    <a href="promotion_status.php" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Check Status</span>
                    </a>
                    <a href="download_certificate.php" class="nav-link active">
                        <i class="fas fa-certificate"></i>
                        <span>Download Certificate</span>
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
                <h1>Download Certificate</h1>
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
            <div class="certificate-banner">
                <h2><i class="fas fa-award"></i> Promotion Certificates</h2>
                <p>Download your approved promotion certificates and view your achievements</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="stat-value"><?php echo $cert_stats['total_certificates'] ?? 0; ?></div>
                    <div class="stat-label">Available Certificates</div>
                </div>

                <div class="stat-card approved">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $cert_stats['total_certificates'] ?? 0; ?></div>
                    <div class="stat-label">Approved Promotions</div>
                </div>
            </div>

            <?php if(isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if(isset($application_data)): ?>
                <!-- Certificate Preview -->
                <div class="certificate-container">
                    <div class="certificate-header">
                        <h3><i class="fas fa-certificate"></i> Promotion Certificate</h3>
                        <p>Preview and download your promotion certificate</p>
                    </div>

                    <div class="certificate-preview">
                        <div class="certificate-logo">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="certificate-title">Certificate of Promotion</div>
                        <div class="certificate-content">
                            This is to certify that
                        </div>
                        <div class="recipient-name">
                            <?php echo htmlspecialchars($application_data['name']); ?>
                        </div>
                        <div class="certificate-content">
                            Department of <?php echo htmlspecialchars($application_data['department']); ?>
                            <br><br>
                            has successfully been promoted from the position of 
                            <strong><?php echo htmlspecialchars($application_data['current_position']); ?></strong>
                            to
                            <strong><?php echo htmlspecialchars($application_data['promotion_to']); ?></strong>
                            based on outstanding academic achievements and professional contributions.
                        </div>
                        
                        <div class="certificate-details">
                            <div class="detail-item">
                                <div class="detail-label">Promotion To</div>
                                <div class="detail-value"><?php echo htmlspecialchars($application_data['promotion_to']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">From Position</div>
                                <div class="detail-value"><?php echo htmlspecialchars($application_data['current_position']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Date of Approval</div>
                                <div class="detail-value"><?php echo date('d M Y', strtotime($application_data['created_at'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Certificate ID</div>
                                <div class="detail-value">#<?php echo str_pad($application_data['id'], 6, '0', STR_PAD_LEFT); ?></div>
                            </div>
                        </div>

                        <div class="signature-area">
                            <div class="signature">Dr. John Smith</div>
                            <div class="signature-label">Principal / Director</div>
                        </div>
                    </div>

                    <a href="generate_certificate.php?id=<?php echo $app_id; ?>" class="download-btn">
                        <i class="fas fa-download"></i> Download Certificate (PDF)
                    </a>
                </div>
            <?php endif; ?>

            <!-- Available Certificates -->
            <div class="certificates-table">
                <div class="table-header">
                    <h3><i class="fas fa-file-alt"></i> Available Certificates</h3>
                </div>

                <div class="table-container">
                    <?php if(mysqli_num_rows($approved_query) > 0): ?>
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>Certificate ID</th>
                                    <th>Promotion From</th>
                                    <th>Promotion To</th>
                                    <th>Approval Date</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($cert = mysqli_fetch_assoc($approved_query)): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($cert['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cert['current_position']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['promotion_to']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($cert['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($cert['department']); ?></td>
                                    <td>
                                        <a href="download_certificate.php?id=<?php echo $cert['id']; ?>" class="action-btn action-download">
                                            <i class="fas fa-eye"></i> Preview
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-certificates">
                            <i class="fas fa-certificate"></i>
                            <h4>No Certificates Available</h4>
                            <p>You don't have any approved promotions yet. Once your promotion is approved, you'll be able to download certificates here.</p>
                            <a href="apply_promotion.php" class="action-btn action-download">
                                <i class="fas fa-paper-plane"></i> Apply for Promotion
                            </a>
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
            // Add hover effects to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
            
            // Print certificate functionality
            const printBtn = document.querySelector('.print-btn');
            if (printBtn) {
                printBtn.addEventListener('click', function() {
                    window.print();
                });
            }
        });

        // Confirmation for downloading certificate
        function confirmDownload() {
            return confirm("Download your promotion certificate? This will generate a PDF file.");
        }
    </script>
</body>
</html>