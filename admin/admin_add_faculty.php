<?php
session_start();
include "config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : "admin@university.edu";

// Fetch statistics for notification badge
$pending = 0;
$pending_query = mysqli_query($conn, "SELECT COUNT(*) AS c FROM promotion_requests WHERE status='Pending'");
if($pending_query) {
    $pending_result = mysqli_fetch_assoc($pending_query);
    $pending = $pending_result['c'];
}

// Handle form submission
$success_message = "";
$error_message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $experience = intval($_POST['experience']);
    
    // Check if email already exists
    $check_email = mysqli_query($conn, "SELECT id FROM faculty WHERE email = '$email'");
    if(mysqli_num_rows($check_email) > 0) {
        $error_message = "Email already exists! Please use a different email.";
    } else {
        // Default image
        $imageName = "default_avatar.png";
        
        if(!empty($_FILES['profile_image']['name'])){
            $targetDir = "uploads/";
            // Create uploads directory if it doesn't exist
            if(!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $imageName = time() . "_" . basename($_FILES["profile_image"]["name"]);
            $targetFile = $targetDir . $imageName;
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
            if($check !== false) {
                // Check file size (2MB limit)
                if ($_FILES["profile_image"]["size"] > 2 * 1024 * 1024) {
                    $error_message = "Sorry, your file is too large. Maximum size is 2MB.";
                } else {
                    // Allow certain file formats
                    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                        $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    } else {
                        if(move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                            // File uploaded successfully
                        } else {
                            $error_message = "Sorry, there was an error uploading your file.";
                        }
                    }
                }
            } else {
                $error_message = "File is not an image.";
            }
        }
        
        if(empty($error_message)) {
            // SIMPLE INSERT QUERY - Only use columns that definitely exist
            $query = "INSERT INTO faculty (name, email, password, department, qualification, experience, profile_image, role) 
                      VALUES ('$name', '$email', '$password', '$department', '$qualification', '$experience', '$imageName', 'faculty')";
            
            if(mysqli_query($conn, $query)){
                $success_message = "Faculty member added successfully!";
                // Reset form fields on success
                $_POST = array();
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Faculty | Career Advancement System</title>
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

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--gray-text);
            font-size: 16px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 15px;
        }

        .form-group label .required {
            color: var(--danger-color);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        /* File Upload */
        .file-upload-container {
            border: 2px dashed #e0e0e0;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
        }

        .file-upload-container:hover {
            border-color: var(--primary-color);
            background: rgba(106, 17, 203, 0.02);
        }

        .file-upload-container input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
            justify-content: center;
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
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            min-width: 200px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.2);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: var(--dark-text);
            border: 1px solid #e9ecef;
            min-width: 150px;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        /* Messages */
        .alert {
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
            border-left: 5px solid;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border-color: var(--success-color);
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.1);
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .alert i {
            font-size: 20px;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }

        .form-section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .form-section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .form-section-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark-text);
        }

        /* Form Tips */
        .form-tips {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }

        .form-tips h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-tips ul {
            list-style: none;
            padding-left: 0;
        }

        .form-tips li {
            padding: 8px 0;
            color: var(--gray-text);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .form-tips li i {
            color: var(--primary-color);
            margin-top: 3px;
        }

        /* Password Indicator */
        .password-strength {
            margin-top: 10px;
            font-size: 14px;
        }

        .strength-bar {
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 5px;
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
            
            .form-container {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
        }

        /* Profile Preview */
        .profile-preview {
            text-align: center;
            padding: 20px;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 600;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                </div>
            </div>

            <div class="nav-section">
                <h3>Promotion Management</h3>
                <div class="nav-links">
                    <a href="admin_promotion_request.php" class="nav-link">
                        <i class="fas fa-medal"></i>
                        <span>Promotion Applications</span>
                        <?php if($pending > 0): ?>
                        <span style="background: var(--danger-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: auto;">
                            <?php echo $pending; ?>
                        </span>
                        <?php endif; ?>
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
                    <a href="admin_add_faculty.php" class="nav-link active">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Faculty</span>
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
                <h1>Add Faculty Member</h1>
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
                <h1>👨‍🏫 Register New Faculty</h1>
                <div class="page-actions">
                    <a href="admin_faculty_list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Success!</strong>
                        <p><?php echo $success_message; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Error!</strong>
                        <p><?php echo $error_message; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Container -->
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data" id="facultyForm">
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <div class="form-section-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="form-section-title">Personal Information</div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name <span class="required">*</span></label>
                                <input type="text" name="name" placeholder="Enter full name" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Email Address <span class="required">*</span></label>
                                <input type="email" name="email" placeholder="Enter university email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Password <span class="required">*</span></label>
                                <input type="password" name="password" id="password" placeholder="Enter password" required 
                                       minlength="6">
                                <div class="password-strength">
                                    <div>Password strength: <span id="strengthText">None</span></div>
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthBar"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Confirm Password <span class="required">*</span></label>
                                <input type="password" name="confirm_password" id="confirm_password" 
                                       placeholder="Confirm password" required>
                                <div id="passwordMatch" style="font-size: 14px; margin-top: 5px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <div class="form-section-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="form-section-title">Professional Information</div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Department <span class="required">*</span></label>
                                <select name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Mathematics" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                                    <option value="Physics" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Physics') ? 'selected' : ''; ?>>Physics</option>
                                    <option value="Chemistry" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Chemistry') ? 'selected' : ''; ?>>Chemistry</option>
                                    <option value="Biology" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Biology') ? 'selected' : ''; ?>>Biology</option>
                                    <option value="Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                    <option value="Business" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Business') ? 'selected' : ''; ?>>Business</option>
                                    <option value="Other" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Experience (Years) <span class="required">*</span></label>
                                <input type="number" name="experience" placeholder="Enter years of experience" required 
                                       min="0" max="50" 
                                       value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : '0'; ?>">
                            </div>

                            <div class="form-group full-width">
                                <label>Highest Qualification <span class="required">*</span></label>
                                <input type="text" name="qualification" placeholder="e.g., Ph.D. in Computer Science, M.Tech, etc." required
                                       value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Profile Photo Section -->
                   

                    <!-- Form Tips -->
                    <div class="form-tips">
                        <h3><i class="fas fa-lightbulb"></i> Quick Tips</h3>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Use the faculty member's official university email address</li>
                            <li><i class="fas fa-check-circle"></i> Password must be at least 6 characters long</li>
                            <li><i class="fas fa-check-circle"></i> Profile photo is optional but recommended</li>
                            <li><i class="fas fa-check-circle"></i> Make sure all information is accurate before submitting</li>
                        </ul>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Register Faculty Member
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                            Reset Form
                        </button>
                    </div>
                </form>
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

        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchIndicator = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchIndicator.textContent = '';
                matchIndicator.style.color = '';
            } else if (password === confirmPassword) {
                matchIndicator.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
                matchIndicator.style.color = '#2ed573';
            } else {
                matchIndicator.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
                matchIndicator.style.color = '#ff4757';
            }
        }

        // Check password strength
        function checkPasswordStrength(password) {
            let strength = 0;
            const strengthText = document.getElementById('strengthText');
            const strengthBar = document.getElementById('strengthBar');
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            const strengthColors = ['#ff4757', '#ff6b81', '#ffa502', '#2ed573', '#2ed573'];
            const strengthLabels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            strength = Math.min(strength, 4);
            strengthText.textContent = strengthLabels[strength];
            strengthText.style.color = strengthColors[strength];
            strengthBar.style.width = ((strength + 1) * 20) + '%';
            strengthBar.style.background = strengthColors[strength];
        }

        // File upload preview
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('avatarPreview');
            const fileInfo = document.getElementById('fileInfo');
            
            if (file) {
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    preview.innerHTML = '<i class="fas fa-user"></i>';
                    fileInfo.innerHTML = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    fileInfo.innerHTML = `
                        <div style="background: #f8f9fa; padding: 10px; border-radius: 8px;">
                            <div><strong>Selected file:</strong> ${file.name}</div>
                            <div><strong>Size:</strong> ${(file.size / 1024).toFixed(1)} KB</div>
                            <div><strong>Type:</strong> ${file.type}</div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<i class="fas fa-user"></i>';
                fileInfo.innerHTML = '';
            }
        });

        // Form validation
        document.getElementById('facultyForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match! Please make sure both passwords are identical.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            return true;
        });

        // Event listeners
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

        // Auto-generate preview from name
        document.querySelector('input[name="name"]').addEventListener('input', function() {
            if (!document.getElementById('fileInput').files.length) {
                const name = this.value.trim();
                const preview = document.getElementById('avatarPreview');
                if (name) {
                    const initials = name.split(' ').map(word => word[0]).join('').toUpperCase().substr(0, 2);
                    preview.innerHTML = initials;
                } else {
                    preview.innerHTML = '<i class="fas fa-user"></i>';
                }
            }
        });

        // Initialize date
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

        updateDateTime();
        setInterval(updateDateTime, 60000);

        // Form reset handler
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            document.getElementById('avatarPreview').innerHTML = '<i class="fas fa-user"></i>';
            document.getElementById('fileInfo').innerHTML = '';
            document.getElementById('passwordMatch').innerHTML = '';
            document.getElementById('strengthText').textContent = 'None';
            document.getElementById('strengthBar').style.width = '0%';
        });
    </script>
</body>
</html>