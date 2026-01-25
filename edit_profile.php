<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch profile data
$query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$user = mysqli_fetch_assoc($query);
$faculty_name = $user['name'];

// Default image detection
$defaultImage = "default_avatar.png"; // default fallback
$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : $defaultImage;

// Fetch additional profile stats
$stats_query = mysqli_query($conn, 
    "SELECT 
        COUNT(*) as total_applications,
        AVG(api_score) as avg_api_score
     FROM promotion_applications WHERE faculty_id='$faculty_id'");
$stats = mysqli_fetch_assoc($stats_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Career Advancement System</title>
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

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Profile Editor Container */
        .profile-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 992px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        /* Left Panel - Profile Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .profile-image-container {
            position: relative;
            margin-bottom: 25px;
        }

        .profile-img {
            width: 180px;
            height: 180px;
            border-radius: 20px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
            font-weight: 600;
            overflow: hidden;
        }

        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-upload-label {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 25px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 14px;
        }

        .image-upload-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        }

        #profile-image-input {
            display: none;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 30px;
        }

        .stat-item {
            background: var(--light-bg);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Right Panel - Edit Form */
        .edit-form-card {
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
            font-size: 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
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
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
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

        .form-textarea {
            min-height: 100px;
            resize: vertical;
            padding: 15px;
            padding-left: 45px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
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

        .btn-danger {
            background: linear-gradient(to right, var(--danger-color), #c0392b);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(255, 71, 87, 0.3);
        }

        /* Password Change Section */
        .password-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
        }

        .password-section h4 {
            font-size: 18px;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            
            .profile-card {
                padding: 25px;
            }
            
            .edit-form-card {
                padding: 25px;
            }
        }

        @media (min-width: 769px) {
            .menu-toggle {
                display: none;
            }
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

        .alert-success {
            background-color: #e7f7ef;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert-error {
            background-color: #ffeaea;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .alert i {
            font-size: 20px;
        }

        /* Profile Preview */
        .profile-preview {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .preview-img {
            max-width: 80%;
            max-height: 80%;
            border-radius: 10px;
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
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="logo-text">
                    <h2>CareerFlow</h2>
                    <p>Advancement System</p>
                </div>
            </div>
            
            <div class="profile-section">
                <div class="profile-avatar">
                    <?php if($profile_image): ?>
                        <img src="uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<?php echo strtoupper(substr($faculty_name, 0, 1)); ?>';">
                    <?php else: ?>
                        <?php echo strtoupper(substr($faculty_name, 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h4><?php echo htmlspecialchars($faculty_name); ?></h4>
                    <span class="role"><?php echo ucfirst($user['role'] ?? 'Faculty'); ?></span>
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
                    <a href="edit_profile.php" class="nav-link active">
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
                <h1>Edit Profile</h1>
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
            <div class="welcome-banner" style="background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%); border-radius: 20px; padding: 30px; color: white; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(106, 17, 203, 0.2);">
                <h2><i class="fas fa-user-edit"></i> Profile Management</h2>
                <p>Update your personal information, profile picture, and account details</p>
            </div>

            <!-- Profile Container -->
           
                <!-- Right Panel - Edit Form -->
                <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="edit-form-card">
                    <div class="form-header">
                        <h3><i class="fas fa-user-cog"></i> Personal Information</h3>
                        <p>Update your personal and professional details</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Department <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-building"></i>
                                <input type="text" name="department" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['department']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Qualification <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-graduation-cap"></i>
                                <input type="text" name="qualification" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['qualification']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Experience (Years) <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="fas fa-briefcase"></i>
                                <input type="number" name="experience" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['experience']); ?>" min="0" max="50" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Biography</label>
                        <div class="input-with-icon">
                            <i class="fas fa-edit"></i>
                            <textarea name="biography" class="form-control form-textarea" 
                                      placeholder="Write a short biography about yourself..."><?php echo htmlspecialchars($user['biography'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Password Change Section -->
                    <div class="password-section">
                        <h4><i class="fas fa-lock"></i> Change Password</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Current Password</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-key"></i>
                                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="faculty_dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash-alt"></i> Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Image Preview Modal -->
    <div class="profile-preview" id="profile-preview-modal">
        <img src="" alt="Profile Preview" class="preview-img" id="preview-img">
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Profile image preview
        const profileImageInput = document.getElementById('profile-image-input');
        const profilePreview = document.getElementById('profile-preview');
        const previewModal = document.getElementById('profile-preview-modal');
        const previewImg = document.getElementById('preview-img');

        profileImageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Update preview in profile card
                    profilePreview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" style="width:100%;height:100%;object-fit:cover;">`;
                    
                    // Set preview for modal
                    previewImg.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Show preview modal on image click
        profilePreview.addEventListener('click', function() {
            const imgSrc = profilePreview.querySelector('img')?.src;
            if (imgSrc) {
                previewImg.src = imgSrc;
                previewModal.style.display = 'flex';
            }
        });

        // Close preview modal
        previewModal.addEventListener('click', function() {
            previewModal.style.display = 'none';
        });

        // Confirm account deletion
        function confirmDelete() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone!')) {
                // Redirect to delete account page
                window.location.href = 'delete_account.php';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
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

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const emailInput = form.querySelector('input[name="email"]');
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address!');
                    emailInput.focus();
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>