<?php
include "config.php";
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$action = $_GET['action'];

$status = ($action == "approve") ? "Approved" : "Rejected";

// Update status in promotion_requests table
$update = "UPDATE promotion_requests SET status='$status' WHERE id='$id'";
mysqli_query($conn, $update);

// Get application details for confirmation
$app_query = mysqli_query($conn, "SELECT pr.*, f.name, f.department 
                                   FROM promotion_requests pr 
                                   JOIN faculty f ON pr.faculty_id = f.id 
                                   WHERE pr.id='$id'");
$application = mysqli_fetch_assoc($app_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Updated | Career Advancement System</title>
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .status-icon.approved {
            background: linear-gradient(135deg, rgba(46, 213, 115, 0.1), rgba(46, 213, 115, 0.2));
            color: var(--success-color);
            border: 3px solid var(--success-color);
        }

        .status-icon.rejected {
            background: linear-gradient(135deg, rgba(255, 71, 87, 0.1), rgba(255, 71, 87, 0.2));
            color: var(--error-color);
            border: 3px solid var(--error-color);
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
        .loading-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin: 30px 0;
        }

        .loading-progress {
            height: 100%;
            width: 0%;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            animation: loading 2s ease-in-out forwards;
        }

        @keyframes loading {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        /* Status Message Box */
        .status-message {
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .status-message.info {
            background-color: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
            border-left: 4px solid var(--secondary-color);
        }

        .status-message.success {
            background-color: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
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
            
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-label {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-card">
            <!-- Status Icon -->
            <div class="status-icon <?php echo $action; ?>">
                <?php if($action == "approve"): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-times-circle"></i>
                <?php endif; ?>
            </div>

            <!-- Status Title -->
            <h1 class="status-title">
                <?php echo $status; ?> Successfully!
            </h1>

            <!-- Status Subtitle -->
            <p class="status-subtitle">
                <?php if($action == "approve"): ?>
                    The promotion application has been approved successfully.
                <?php else: ?>
                    The promotion application has been rejected.
                <?php endif; ?>
            </p>

            <!-- Loading Animation -->
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>

            <!-- Application Details -->
            <?php if($application): ?>
            <div class="application-details">
                <h3 class="details-header">
                    <i class="fas fa-file-alt"></i> Application Summary
                </h3>
                
                <div class="detail-item">
                    <div class="detail-label">Applicant:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($application['name']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Department:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($application['department']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Applied For:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($application['promotion_to']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Application ID:</div>
                    <div class="detail-value">#<?php echo htmlspecialchars($id); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status Updated:</div>
                    <div class="detail-value"><?php echo date('F j, Y, h:i A'); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status Message -->
            <div class="status-message <?php echo $action == 'approve' ? 'success' : 'info'; ?>">
                <i class="fas fa-info-circle"></i>
                <span>
                    <?php if($action == "approve"): ?>
                        Applicant will be notified about the approval via email. The promotion process will now proceed to the next stage.
                    <?php else: ?>
                        Applicant will be notified about the rejection with appropriate reasons. They can reapply after addressing the concerns.
                    <?php endif; ?>
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="admin_promotion_request.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Applications
                </a>
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Admin Dashboard
                </a>
            </div>

            <!-- Auto Redirect Timer -->
            <div style="margin-top: 25px; font-size: 0.9rem; color: var(--gray-text);">
                <i class="fas fa-clock"></i> Redirecting in <span id="countdown">5</span> seconds...
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Admin Portal</p>
            <p>Action performed by: <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'System Administrator'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto redirect after 5 seconds
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'admin_promotion_request.php';
                }
            }, 1000);
            
            // Add some visual effects
            const statusIcon = document.querySelector('.status-icon');
            setTimeout(() => {
                statusIcon.style.animation = 'none';
                setTimeout(() => {
                    statusIcon.style.animation = 'pulse 2s infinite';
                }, 50);
            }, 2000);
            
            // Show success animation for approval
            <?php if($action == "approve"): ?>
            setTimeout(() => {
                const confettiCount = 100;
                const colors = ['#2ed573', '#2575fc', '#6a11cb', '#ff416c'];
                
                for (let i = 0; i < confettiCount; i++) {
                    const confetti = document.createElement('div');
                    confetti.style.position = 'fixed';
                    confetti.style.width = '10px';
                    confetti.style.height = '10px';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.borderRadius = '50%';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.top = '-20px';
                    confetti.style.opacity = '0.8';
                    confetti.style.zIndex = '9999';
                    document.body.appendChild(confetti);
                    
                    // Animate confetti
                    const animation = confetti.animate([
                        { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                        { transform: `translateY(${window.innerHeight + 20}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                    ], {
                        duration: Math.random() * 3000 + 2000,
                        easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                    });
                    
                    // Remove confetti after animation
                    animation.onfinish = () => confetti.remove();
                }
            }, 500);
            <?php endif; ?>
            
            // Disable back button to prevent resubmission
            history.pushState(null, null, location.href);
            window.onpopstate = function() {
                history.go(1);
            };
        });
    </script>
</body>
</html>