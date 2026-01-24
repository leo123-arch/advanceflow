<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id       = $_SESSION['faculty_id'];
$current_position = $_POST['current_position'];
$promotion_to     = $_POST['promotion_to'];
$remarks          = $_POST['remarks'];

// Get faculty details for display
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

// Handle Document Upload
$doc_name = "";
$upload_success = true;
$error_message = "";

if(!empty($_FILES['docs']['name'])){
    $upload_dir = "uploads/";
    
    // Check if uploads directory exists, create if not
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = $_FILES['docs']['name'];
    $file_tmp = $_FILES['docs']['tmp_name'];
    $file_size = $_FILES['docs']['size'];
    $file_error = $_FILES['docs']['error'];
    
    // Check for upload errors
    if($file_error === UPLOAD_ERR_OK) {
        // Check file type (PDF only)
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if($file_ext !== 'pdf') {
            $upload_success = false;
            $error_message = "Only PDF files are allowed.";
        } 
        // Check file size (10MB limit)
        elseif($file_size > 10 * 1024 * 1024) {
            $upload_success = false;
            $error_message = "File size exceeds 10MB limit.";
        } else {
            // Generate unique filename
            $doc_name = time() . "_" . uniqid() . "_" . preg_replace('/[^A-Za-z0-9.-]/', '_', $file_name);
            $target = $upload_dir . $doc_name;
            
            if(!move_uploaded_file($file_tmp, $target)) {
                $upload_success = false;
                $error_message = "Failed to move uploaded file.";
            }
        }
    } else {
        $upload_success = false;
        $error_message = "File upload error: " . $file_error;
    }
} else {
    $upload_success = false;
    $error_message = "No file was uploaded.";
}

// Only proceed with database insertion if upload was successful
$db_success = false;
if($upload_success) {
    // Insert into new table
    $query = "INSERT INTO promotion_requests 
            (faculty_id, current_position, promotion_to, document, remarks, status, created_at)
            VALUES 
            ('$faculty_id', '$current_position', '$promotion_to', '$doc_name', '$remarks', 'Pending', NOW())";

    if(mysqli_query($conn, $query)) {
        $db_success = true;
        $application_id = mysqli_insert_id($conn);
    } else {
        $error_message = "Database error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Application | Career Advancement System</title>
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

        /* Processing Card */
        .processing-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .processing-card::before {
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

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }

        .progress-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: var(--gray-text);
            transition: all 0.3s;
        }

        .step-circle.completed {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .step-circle.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            animation: bounce 1s infinite;
        }

        .step-label {
            font-size: 0.9rem;
            color: var(--gray-text);
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Application Details */
        .application-details {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid var(--primary-color);
        }

        .details-header {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .details-header i {
            color: var(--primary-color);
        }

        .detail-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark-text);
            min-width: 140px;
        }

        .detail-value {
            color: var(--gray-text);
            flex: 1;
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

        /* Loading Animation */
        .loader {
            display: inline-block;
            width: 60px;
            height: 60px;
            margin: 20px 0;
        }

        .loader:after {
            content: " ";
            display: block;
            width: 40px;
            height: 40px;
            margin: 8px;
            border-radius: 50%;
            border: 4px solid var(--primary-color);
            border-color: var(--primary-color) transparent var(--primary-color) transparent;
            animation: loader 1.2s linear infinite;
        }

        @keyframes loader {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            
            .processing-card {
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
            
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-label {
                min-width: auto;
            }
            
            .progress-steps {
                flex-direction: column;
                gap: 30px;
            }
            
            .progress-steps::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="processing-card">
            
            <?php if($upload_success && $db_success): ?>
            
            <!-- SUCCESS STATE -->
            <div class="status-icon success">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="status-title">Application Submitted!</h1>
            
            <p class="status-subtitle">
                Your promotion application has been received successfully.<br>
                Application ID: <strong>#<?php echo $application_id; ?></strong>
            </p>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="progress-step">
                    <div class="step-circle completed"><i class="fas fa-check"></i></div>
                    <div class="step-label">Application Form</div>
                </div>
                <div class="progress-step">
                    <div class="step-circle completed"><i class="fas fa-check"></i></div>
                    <div class="step-label">Document Upload</div>
                </div>
                <div class="progress-step">
                    <div class="step-circle active">3</div>
                    <div class="step-label">Under Review</div>
                </div>
                <div class="progress-step">
                    <div class="step-circle">4</div>
                    <div class="step-label">Decision</div>
                </div>
            </div>

            <!-- Application Details -->
            <div class="application-details">
                <h3 class="details-header">
                    <i class="fas fa-file-alt"></i> Application Summary
                </h3>
                
                <div class="detail-item">
                    <div class="detail-label">Applicant:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($faculty['name']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Department:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($faculty['department']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Current Position:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($current_position); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Applying For:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($promotion_to); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Document:</div>
                    <div class="detail-value">
                        <?php if($doc_name): ?>
                            <i class="fas fa-file-pdf"></i> <?php echo htmlspecialchars($_FILES['docs']['name']); ?>
                        <?php else: ?>
                            No document uploaded
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Submission Date:</div>
                    <div class="detail-value"><?php echo date('F j, Y, h:i A'); ?></div>
                </div>
                
                <?php if(!empty($remarks)): ?>
                <div class="detail-item">
                    <div class="detail-label">Remarks:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($remarks); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Next Steps Info -->
            <div style="background: rgba(37, 117, 252, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;">
                <h4 style="color: var(--secondary-color); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-info-circle"></i> What Happens Next?
                </h4>
                <ul style="color: var(--gray-text); padding-left: 20px;">
                    <li>Your application will be reviewed by the administration committee</li>
                    <li>Review process typically takes 10-15 working days</li>
                    <li>You will be notified via email about the decision</li>
                    <li>Check your application status in the Promotion Status page</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="promotion_status.php" class="btn btn-primary">
                    <i class="fas fa-chart-bar"></i> View Status
                </a>
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <?php else: ?>
            
            <!-- ERROR STATE -->
            <div class="status-icon error">
                <i class="fas fa-exclamation-circle"></i>
            </div>

            <h1 class="status-title">Submission Failed</h1>
            
            <p class="status-subtitle">
                We encountered an issue while processing your application.
            </p>

            <!-- Error Message -->
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Error Details:</strong><br>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            </div>

            <!-- Troubleshooting Tips -->
            <div style="background: rgba(255, 165, 2, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;">
                <h4 style="color: var(--warning-color); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-lightbulb"></i> Troubleshooting Tips
                </h4>
                <ul style="color: var(--gray-text); padding-left: 20px;">
                    <li>Ensure your file is in PDF format</li>
                    <li>Check that the file size is under 10MB</li>
                    <li>Try uploading a different file</li>
                    <li>Make sure you have stable internet connection</li>
                    <li>Contact support if the problem persists</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="apply_promotion.php" class="btn btn-primary">
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
                <?php if($upload_success && $db_success): ?>
                    Redirecting to status page in <span id="countdown">5</span> seconds...
                <?php else: ?>
                    You can try submitting again or return to dashboard
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Promotion Application</p>
            <p>Reference: <?php echo date('Ymd-His'); ?>-<?php echo $faculty_id; ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($upload_success && $db_success): ?>
            // Auto redirect after 5 seconds for success
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'promotion_status.php';
                }
            }, 1000);
            
            // Add celebration effect for successful submission
            setTimeout(() => {
                // Add success animation
                const successIcon = document.querySelector('.status-icon.success');
                successIcon.style.animation = 'none';
                setTimeout(() => {
                    successIcon.style.animation = 'pulse 2s infinite';
                }, 50);
                
                // Add subtle confetti for success
                const colors = ['#2ed573', '#2575fc', '#6a11cb'];
                for (let i = 0; i < 30; i++) {
                    setTimeout(() => {
                        const dot = document.createElement('div');
                        dot.style.position = 'fixed';
                        dot.style.width = '6px';
                        dot.style.height = '6px';
                        dot.style.background = colors[Math.floor(Math.random() * colors.length)];
                        dot.style.borderRadius = '50%';
                        dot.style.left = Math.random() * 100 + 'vw';
                        dot.style.top = '-10px';
                        dot.style.opacity = '0.7';
                        dot.style.zIndex = '9999';
                        document.body.appendChild(dot);
                        
                        const animation = dot.animate([
                            { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                            { transform: `translateY(${window.innerHeight + 10}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                        ], {
                            duration: Math.random() * 2000 + 1000,
                            easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                        });
                        
                        animation.onfinish = () => dot.remove();
                    }, i * 100);
                }
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