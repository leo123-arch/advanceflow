<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Get faculty details before update
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$old_faculty_data = mysqli_fetch_assoc($faculty_query);

// Validate form submission
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: edit_profile.php");
    exit();
}

// Initialize variables
$success = false;
$error_message = '';
$image_uploaded = false;
$image_name = '';

// Get form data with validation
$name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$qualification = isset($_POST['qualification']) ? mysqli_real_escape_string($conn, $_POST['qualification']) : '';
$experience = isset($_POST['experience']) ? intval($_POST['experience']) : 0;
$department = isset($_POST['department']) ? mysqli_real_escape_string($conn, $_POST['department']) : '';
// Note: phone, address, biography columns don't exist in your database

// Validate required fields
if(empty($name) || empty($email) || empty($qualification) || empty($department)) {
    $error_message = "Please fill all required fields.";
} else {
    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } 
    // Check if email is being changed and if it already exists
    elseif($email != $old_faculty_data['email']) {
        $check_email = mysqli_query($conn, "SELECT id FROM faculty WHERE email='$email' AND id != '$faculty_id'");
        if(mysqli_num_rows($check_email) > 0) {
            $error_message = "Email already exists! Please use a different email.";
        }
    }
    
    // If no validation errors, proceed
    if(empty($error_message)) {
        // Handle Image Upload
        if(!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK){
            $upload_dir = "uploads/";
            
            // Check if uploads directory exists, create if not
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = $_FILES['profile_image']['name'];
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $file_size = $_FILES['profile_image']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Check file type
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if(!in_array($file_ext, $allowed_ext)) {
                $error_message = "Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.";
            } 
            // Check file size (5MB limit)
            elseif($file_size > 5 * 1024 * 1024) {
                $error_message = "Image size exceeds 5MB limit.";
            } else {
                // Generate unique filename
                $image_name = time() . "_" . uniqid() . "." . $file_ext;
                $target = $upload_dir . $image_name;
                
                if(move_uploaded_file($file_tmp, $target)) {
                    $image_uploaded = true;
                    
                    // Delete old profile image if exists and not default
                    if(!empty($old_faculty_data['profile_image']) && 
                       $old_faculty_data['profile_image'] != 'default_avatar.png' &&
                       file_exists($upload_dir . $old_faculty_data['profile_image'])) {
                        unlink($upload_dir . $old_faculty_data['profile_image']);
                    }
                } else {
                    $error_message = "Failed to upload image. Please try again.";
                }
            }
        }
        
        // If no upload errors, update database
        if(empty($error_message)) {
            // Build update query with only existing columns
            $update_fields = [
                "name='$name'",
                "email='$email'",
                "qualification='$qualification'",
                "experience='$experience'",
                "department='$department'"
            ];
            
            // Add profile image if uploaded
            if($image_uploaded) {
                $update_fields[] = "profile_image='$image_name'";
            }
            
            // Handle password change if provided
            if(isset($_POST['current_password']) && !empty($_POST['current_password']) &&
               isset($_POST['new_password']) && !empty($_POST['new_password'])) {
                $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
                $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
                $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
                
                // Verify current password
                if($current_password == $old_faculty_data['password']) {
                    if($new_password == $confirm_password) {
                        $update_fields[] = "password='$new_password'";
                    } else {
                        $error_message = "New passwords do not match!";
                    }
                } else {
                    $error_message = "Current password is incorrect!";
                }
            }
            
            // Execute update if no errors
            if(empty($error_message)) {
                $update_query = "UPDATE faculty SET " . implode(", ", $update_fields) . 
                               " WHERE id='$faculty_id'";
                
                if(mysqli_query($conn, $update_query)) {
                    $success = true;
                    
                    // Update session data
                    $_SESSION['faculty_name'] = $name;
                } else {
                    $error_message = "Database error: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Get updated faculty data
$updated_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$new_faculty_data = mysqli_fetch_assoc($updated_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Update | Career Advancement System</title>
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
            --error-color: #ff4757;
            --warning-color: #ffa502;
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            color: var(--dark-text);
        }

        .container {
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Status Card */
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        /* Status Icon */
        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
        }

        .status-icon.success {
            background: linear-gradient(135deg, rgba(46, 213, 115, 0.1), rgba(46, 213, 115, 0.2));
            color: var(--success-color);
            border: 3px solid var(--success-color);
            animation: pulse 2s infinite;
        }

        .status-icon.error {
            background: linear-gradient(135deg, rgba(255, 71, 87, 0.1), rgba(255, 71, 87, 0.2));
            color: var(--error-color);
            border: 3px solid var(--error-color);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Status Message */
        .status-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .status-subtitle {
            font-size: 1.2rem;
            color: var(--gray-text);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        /* Update Summary */
        .update-summary {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid var(--primary-color);
        }

        .summary-header {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-header i {
            color: var(--primary-color);
        }

        .update-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .update-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .update-label {
            font-weight: 600;
            color: var(--dark-text);
            min-width: 140px;
        }

        .update-value {
            color: var(--gray-text);
            flex: 1;
        }

        .changed {
            color: var(--success-color);
            font-weight: 600;
        }

        /* Error Message */
        .error-message {
            background: rgba(255, 71, 87, 0.1);
            color: var(--error-color);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid var(--error-color);
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .error-message i {
            font-size: 1.5rem;
            margin-top: 2px;
        }

        /* Success Message */
        .success-message {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid var(--success-color);
            text-align: left;
        }

        .success-message i {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        /* Profile Preview */
        .profile-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 15px;
            border: 2px solid var(--light-bg);
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 600;
            overflow: hidden;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h4 {
            font-size: 1.3rem;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .profile-info p {
            color: var(--gray-text);
            font-size: 0.9rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 16px 30px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            min-width: 180px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--light-bg);
            transform: translateY(-3px);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .status-card {
                padding: 25px;
            }
            
            .status-title {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                min-width: auto;
            }
            
            .update-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .update-label {
                min-width: auto;
            }
            
            .profile-preview {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-card">
            
            <?php if($success): ?>
            
            <!-- SUCCESS STATE -->
            <div class="status-icon success">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="status-title">Profile Updated!</h1>
            
            <p class="status-subtitle">
                Your profile has been successfully updated.
            </p>

            <!-- Profile Preview -->
            <div class="profile-preview">
                <div class="profile-image">
                    <?php if($image_uploaded && !empty($image_name)): ?>
                        <img src="uploads/<?php echo htmlspecialchars($image_name); ?>" 
                             alt="Profile" 
                             onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<?php echo strtoupper(substr($name, 0, 1)); ?>';">
                    <?php elseif(!empty($new_faculty_data['profile_image']) && $new_faculty_data['profile_image'] != 'default_avatar.png'): ?>
                        <img src="uploads/<?php echo htmlspecialchars($new_faculty_data['profile_image']); ?>" 
                             alt="Profile" 
                             onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<?php echo strtoupper(substr($name, 0, 1)); ?>';">
                    <?php else: ?>
                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h4><?php echo htmlspecialchars($name); ?></h4>
                    <p><?php echo htmlspecialchars($department); ?> • <?php echo htmlspecialchars($qualification); ?></p>
                </div>
            </div>

            <!-- Update Summary -->
            <div class="update-summary">
                <h3 class="summary-header">
                    <i class="fas fa-sync-alt"></i> Changes Applied
                </h3>
                
                <?php 
                // Display changes for existing database fields only
                $fields_to_check = [
                    'name' => 'Name',
                    'email' => 'Email',
                    'department' => 'Department',
                    'qualification' => 'Qualification',
                    'experience' => 'Experience'
                ];
                
                foreach($fields_to_check as $field => $label):
                    if(isset($new_faculty_data[$field]) && isset($old_faculty_data[$field])):
                        if($new_faculty_data[$field] != $old_faculty_data[$field] && 
                           !(empty($new_faculty_data[$field]) && empty($old_faculty_data[$field]))):
                ?>
                <div class="update-item">
                    <div class="update-label"><?php echo $label; ?>:</div>
                    <div class="update-value">
                        <span style="text-decoration: line-through; color: #999;">
                            <?php echo !empty($old_faculty_data[$field]) ? htmlspecialchars($old_faculty_data[$field]) : 'Not set'; ?>
                        </span> 
                        → 
                        <span class="changed">
                            <?php echo !empty($new_faculty_data[$field]) ? htmlspecialchars($new_faculty_data[$field]) : 'Not set'; ?>
                        </span>
                    </div>
                </div>
                <?php 
                        endif;
                    endif;
                endforeach; 
                ?>
                
                <?php if($image_uploaded): ?>
                <div class="update-item">
                    <div class="update-label">Profile Image:</div>
                    <div class="update-value changed">
                        <i class="fas fa-image"></i> Updated successfully
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_POST['current_password']) && !empty($_POST['current_password'])): ?>
                <div class="update-item">
                    <div class="update-label">Password:</div>
                    <div class="update-value changed">
                        <i class="fas fa-key"></i> Updated successfully
                    </div>
                </div>
                <?php endif; ?> 
                
                <div class="update-item">
                    <div class="update-label">Updated On:</div>
                    <div class="update-value"><?php echo date('F j, Y, h:i A'); ?></div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <strong>Profile updated successfully!</strong> Your changes have been saved and will be reflected across the system.
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="faculty_dashboard.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
                <a href="edit_profile.php" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit Again
                </a>
            </div>

            <?php else: ?>
            
            <!-- ERROR STATE -->
            <div class="status-icon error">
                <i class="fas fa-exclamation-circle"></i>
            </div>

            <h1 class="status-title">Update Failed</h1>
            
            <p class="status-subtitle">
                We couldn't update your profile.
            </p>

            <!-- Error Message -->
            <?php if(!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Error Details:</strong><br>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Troubleshooting Tips -->
            <div style="background: rgba(255, 165, 2, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;">
                <h4 style="color: var(--warning-color); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-lightbulb"></i> Troubleshooting Tips
                </h4>
                <ul style="color: var(--gray-text); padding-left: 20px;">
                    <li>Check that all required fields are filled</li>
                    <li>Ensure email format is correct</li>
                    <li>Make sure your email is unique</li>
                    <li>Check image file type and size (max 5MB)</li>
                    <li>Verify your current password if changing password</li>
                    <li>Note: Phone, Address, and Biography fields are not available</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="edit_profile.php" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Try Again
                </a>
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <?php endif; ?>

            <!-- Auto Redirect Timer -->
            <div style="margin-top: 25px; font-size: 0.9rem; color: var(--gray-text);">
                <i class="fas fa-clock"></i> 
                <?php if($success): ?>
                    Redirecting to dashboard in <span id="countdown">5</span> seconds...
                <?php else: ?>
                    You can try updating again or return to dashboard
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Profile Update</p>
            <p>Reference: <?php echo date('Ymd-His'); ?>-<?php echo $faculty_id; ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($success): ?>
            // Auto redirect after 5 seconds for success
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'faculty_dashboard.php';
                }
            }, 1000);
            
            // Add celebration effect for successful update
            setTimeout(() => {
                const successIcon = document.querySelector('.status-icon.success');
                successIcon.style.animation = 'none';
                setTimeout(() => {
                    successIcon.style.animation = 'pulse 2s infinite';
                }, 50);
            }, 1000);
            
            // Disable back button to prevent resubmission
            history.pushState(null, null, location.href);
            window.onpopstate = function() {
                history.go(1);
            };
            <?php endif; ?>
        });
    </script>
</body>
</html>