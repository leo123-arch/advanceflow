<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

// Get faculty_id from session FIRST
$faculty_id = $_SESSION['faculty_id'];

// Initialize variables
$current_position = "";
$promotion_to = "";
$remarks = "";
$doc_name = "";
$upload_success = false;
$db_success = false;
$error_message = "";
$application_id = 0;
$original_filename = "";

// Get faculty details for display
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

// Check if form was submitted
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data with proper validation
    $current_position = isset($_POST['current_position']) ? mysqli_real_escape_string($conn, $_POST['current_position']) : '';
    $promotion_to = isset($_POST['promotion_to']) ? mysqli_real_escape_string($conn, $_POST['promotion_to']) : '';
    $remarks = isset($_POST['remarks']) ? mysqli_real_escape_string($conn, $_POST['remarks']) : '';
    
    // Validate required fields
    if(empty($current_position) || empty($promotion_to)) {
        $error_message = "Current position and promotion to are required fields.";
        $upload_success = false;
    } else {
        // Handle Document Upload
        $upload_success = true;
        
        if(!empty($_FILES['docs']['name']) && $_FILES['docs']['error'] === UPLOAD_ERR_OK){
            $upload_dir = "uploads/";
            
            // Check if uploads directory exists, create if not
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = $_FILES['docs']['name'];
            $original_filename = $file_name; // Store original filename
            $file_tmp = $_FILES['docs']['tmp_name'];
            $file_size = $_FILES['docs']['size'];
            
            // Check file type (PDF only)
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['pdf'];
            
            if(!in_array($file_ext, $allowed_extensions)) {
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
            // File upload error or no file
            if(isset($_FILES['docs']['error'])) {
                switch($_FILES['docs']['error']) {
                    case UPLOAD_ERR_NO_FILE:
                        $upload_success = false;
                        $error_message = "No file was uploaded. Please upload your supporting document.";
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $upload_success = false;
                        $error_message = "File size exceeds limit.";
                        break;
                    default:
                        $upload_success = false;
                        $error_message = "File upload error.";
                }
            } else {
                $upload_success = false;
                $error_message = "No file was uploaded.";
            }
        }

        // Only proceed with database insertion if upload was successful
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
                $db_success = false;
            }
        }
    }
} else {
    // Form was not submitted via POST
    $error_message = "Invalid request method. Please submit the form properly.";
    $upload_success = false;
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
            max-width: 700px;
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

        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 16px 25px;
            border-radius: 12px;
            font-size: 1rem;
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
            flex: 1;
            max-width: 200px;
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

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            color: white;
            box-shadow: 0 8px 25px rgba(46, 213, 115, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(46, 213, 115, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffa502, #ff7f00);
            color: white;
            box-shadow: 0 8px 25px rgba(255, 165, 2, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(255, 165, 2, 0.4);
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

        .btn-outline {
            background: transparent;
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
        }

        .btn-outline:hover {
            background: rgba(37, 117, 252, 0.1);
            transform: translateY(-3px);
        }

        /* Download Application PDF Section */
        .download-pdf-section {
            background: rgba(106, 17, 203, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .download-pdf-section h4 {
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Download Certificate Button (only for approved applications) */
        .download-certificate-section {
            background: rgba(46, 213, 115, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid var(--success-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .download-certificate-section h4 {
            color: #27ae60;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Next Steps Info */
        .next-steps-info {
            background: rgba(37, 117, 252, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }

        .next-steps-info h4 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .next-steps-info ul {
            color: var(--gray-text);
            padding-left: 20px;
        }

        /* Troubleshooting Tips */
        .troubleshooting-tips {
            background: rgba(255, 165, 2, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }

        .troubleshooting-tips h4 {
            color: var(--warning-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .troubleshooting-tips ul {
            color: var(--gray-text);
            padding-left: 20px;
        }

        /* Auto Redirect Notice */
        .redirect-notice {
            margin-top: 25px;
            font-size: 0.9rem;
            color: var(--gray-text);
            padding: 10px;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .redirect-notice i {
            color: var(--primary-color);
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
                max-width: 100%;
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
            
            .download-pdf-section,
            .download-certificate-section {
                flex-direction: column;
                text-align: center;
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
                <strong>Application ID: #<?php echo $application_id; ?></strong>
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
                            <i class="fas fa-file-pdf"></i> <?php echo htmlspecialchars($original_filename); ?>
                        <?php else: ?>
                            No document uploaded
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-item">
                    
                    <div class="detail-value"><?php echo date('F j, Y, h:i A'); ?></div>
                </div>
                
                <?php if(!empty($remarks)): ?>
                <div class="detail-item">
                    <div class="detail-label">Remarks:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($remarks); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Download Application PDF Section -->
            <div class="download-pdf-section">
                <div>
                    <h4><i class="fas fa-file-pdf"></i> Download Application Copy</h4>
                    <p style="color: var(--gray-text); margin: 5px 0 0; font-size: 0.9rem;">
                        Download a PDF copy of your submitted application for your records.
                    </p>
                </div>
                <button onclick="downloadCertificateStylePDF()" class="btn btn-warning">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>

            <!-- Download Certificate Section (Conditional) -->
            <?php 
            // Check if there are any approved promotions for this faculty
            $approved_query = mysqli_query($conn, "
                SELECT pr.id, pr.status 
                FROM promotion_requests pr 
                WHERE pr.faculty_id = '$faculty_id' 
                AND pr.status = 'Approved' 
                ORDER BY pr.created_at DESC 
                LIMIT 1
            ");
            
            if(mysqli_num_rows($approved_query) > 0): 
                $approved_app = mysqli_fetch_assoc($approved_query);
            ?>
            <div class="download-certificate-section">
                <div>
                    <h4><i class="fas fa-award"></i> Download Previous Certificate</h4>
                    <p style="color: var(--gray-text); margin: 5px 0 0; font-size: 0.9rem;">
                        You have an approved promotion! Download your certificate.
                    </p>
                </div>
                <a href="download_certificate.php?id=<?php echo $approved_app['id']; ?>" class="btn btn-success">
                    <i class="fas fa-download"></i> Download Certificate
                </a>
            </div>
            <?php endif; ?>

            <!-- Next Steps Info -->
            <div class="next-steps-info">
                <h4>
                    <i class="fas fa-info-circle"></i> What Happens Next?
                </h4>
                <ul>
                    <li>Your application will be reviewed by the administration committee</li>
                    <li>Review process typically takes 10-15 working days</li>
                    <li>You will be notified via email about the decision</li>
                    <li>Check your application status in the Promotion Status page</li>
                </ul>
            </div>

            <!-- Action Buttons (Multiple Options) -->
            <div class="action-buttons">
                <!-- Button 1: View Application Status -->
                <a href="promotion_status.php" class="btn btn-primary">
                    <i class="fas fa-chart-bar"></i> View Status
                </a>
                
                <!-- Button 2: View All Applications -->
                <a href="promotion_status.php" class="btn btn-outline">
                    <i class="fas fa-list-check"></i> All Applications
                </a>
                
                <!-- Button 3: Apply for Another Promotion -->
                <a href="apply_promotion.php" class="btn btn-secondary">
                    <i class="fas fa-paper-plane"></i> Apply Again
                </a>
                
                <!-- Button 4: Dashboard -->
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <?php elseif(!$upload_success && !empty($error_message)): ?>
            
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
            <div class="troubleshooting-tips">
                <h4>
                    <i class="fas fa-lightbulb"></i> Troubleshooting Tips
                </h4>
                <ul>
                    <li>Ensure your file is in PDF format</li>
                    <li>Check that the file size is under 10MB</li>
                    <li>Try uploading a different file</li>
                    <li>Make sure you have stable internet connection</li>
                    <li>Contact support if the problem persists</li>
                </ul>
            </div>

            <!-- Action Buttons for Error State -->
            <div class="action-buttons">
                <a href="apply_promotion.php" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Try Again
                </a>
                
                <!-- Check existing applications even on error -->
                <a href="promotion_status.php" class="btn btn-outline">
                    <i class="fas fa-list-check"></i> View Applications
                </a>
                
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <?php else: ?>
            
            <!-- DEFAULT STATE (when page is accessed directly) -->
            <div class="status-icon" style="background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.2)); color: var(--primary-color); border: 3px solid var(--primary-color);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <h1 class="status-title">Invalid Access</h1>
            
            <p class="status-subtitle">
                This page is for processing promotion applications.<br>
                Please submit your application through the proper form.
            </p>

            <!-- Action Buttons for Default State -->
            <div class="action-buttons">
                <a href="apply_promotion.php" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Apply for Promotion
                </a>
                
                <a href="promotion_status.php" class="btn btn-outline">
                    <i class="fas fa-list-check"></i> View Applications
                </a>
                
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <?php endif; ?>

            <!-- Manual Action Required Notice -->
            <div class="redirect-notice">
                <i class="fas fa-hand-pointer"></i>
                <span>Please choose an action above to continue</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Promotion Application</p>
            <p>Reference: <?php echo date('Ymd-His'); ?>-<?php echo $faculty_id; ?></p>
        </div>
    </div>

    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        
        function downloadCertificateStylePDF() {
            const button = document.querySelector('.btn-warning');
            const originalText = button.innerHTML;
            
            try {
                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
                button.disabled = true;
                
                // Create PDF with certificate style
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });
                
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                
                // ===========================================
                // BACKGROUND - Gradient like your UI
                // ===========================================
                // Create gradient background
                const gradient = pdf.context2d.createLinearGradient(0, 0, pageWidth, pageHeight);
                gradient.addColorStop(0, '#667eea');
                gradient.addColorStop(1, '#764ba2');
                pdf.setFillColor(102, 126, 234); // Fallback color
                
                // Main content area with white background
                pdf.setFillColor(255, 255, 255);
                pdf.roundedRect(15, 15, pageWidth - 30, pageHeight - 30, 10, 10, 'F');
                
                // ===========================================
                // HEADER - Matching your certificate UI
                // ===========================================
                // Decorative top border
                pdf.setDrawColor(106, 17, 203); // Primary color
                pdf.setLineWidth(3);
                pdf.line(15, 15, pageWidth - 15, 15);
                
                // Logo/Icon
                pdf.setFontSize(48);
                pdf.setTextColor(106, 17, 203);
                pdf.text('✓', pageWidth / 2, 45, { align: 'center' });
                
                // Main Title
                pdf.setFontSize(32);
                pdf.setTextColor(106, 17, 203);
                pdf.text('APPLICATION SUBMITTED', pageWidth / 2, 65, { align: 'center' });
                
                // Subtitle
                pdf.setFontSize(18);
                pdf.setTextColor(37, 117, 252); // Secondary color
                pdf.text('Promotion Application Confirmation', pageWidth / 2, 75, { align: 'center' });
                
                // ===========================================
                // APPLICATION DETAILS - Styled like your UI
                // ===========================================
                // Application ID
                pdf.setFontSize(16);
                pdf.setTextColor(99, 110, 114); // Gray text
                pdf.text('Application ID:', 40, 95);
                pdf.setFontSize(18);
                pdf.setTextColor(106, 17, 203);
                pdf.text('#<?php echo $application_id; ?>', 90, 95);
                
                // Submission Date
                pdf.setFontSize(12);
                pdf.setTextColor(99, 110, 114);
              ;
                pdf.setFontSize(11);
              pdf.text('<?php echo htmlspecialchars(date("F j, Y, h:i A")); ?>', pageWidth - 50, 95, { align: 'right' });
                
                // ===========================================
                // APPLICANT INFORMATION SECTION
                // ===========================================
                // Section Header
                pdf.setFontSize(16);
                pdf.setTextColor(45, 52, 54); // Dark text
                pdf.text('Applicant Information', 40, 115);
                
                // Decorative line
                pdf.setDrawColor(106, 17, 203);
                pdf.setLineWidth(1);
                pdf.line(40, 118, pageWidth - 40, 118);
                
                // Name
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Name:', 40, 130);
                pdf.setFontSize(13);
                pdf.setTextColor(99, 110, 114);
                pdf.text('<?php echo htmlspecialchars($faculty["name"]); ?>', 80, 130);
                
                // Department
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Department:', 40, 140);
                pdf.setFontSize(13);
                pdf.setTextColor(99, 110, 114);
                pdf.text('<?php echo htmlspecialchars($faculty["department"]); ?>', 90, 140);
                
                // Email
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Email:', 40, 150);
                pdf.setFontSize(13);
                pdf.setTextColor(99, 110, 114);
                pdf.text('<?php echo htmlspecialchars($faculty["email"]); ?>', 70, 150);
                
                // ===========================================
                // APPLICATION DETAILS SECTION
                // ===========================================
                // Section Header
                pdf.setFontSize(16);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Application Details', 40, 170);
                
                // Decorative line
                pdf.setDrawColor(37, 117, 252); // Secondary color
                pdf.setLineWidth(1);
                pdf.line(40, 173, pageWidth - 40, 173);
                
                // Current Position
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Current Position:', 40, 185);
                pdf.setFontSize(13);
                pdf.setTextColor(99, 110, 114);
                pdf.text('<?php echo htmlspecialchars($current_position); ?>', 90, 185);
                
                // Promotion To
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Promotion To:', 40, 195);
                pdf.setFontSize(13);
                pdf.setTextColor(99, 110, 114);
                pdf.text('<?php echo htmlspecialchars($promotion_to); ?>', 85, 195);
                
                // Remarks (if any)
                <?php if(!empty($remarks)): ?>
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Remarks:', 40, 205);
                pdf.setFontSize(12);
                pdf.setTextColor(99, 110, 114);
                const remarksText = pdf.splitTextToSize('<?php echo htmlspecialchars($remarks); ?>', pageWidth - 100);
                pdf.text(remarksText, 80, 205);
                <?php endif; ?>
                
                // ===========================================
                // DOCUMENT INFORMATION
                // ===========================================
                // Section Header
                pdf.setFontSize(16);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Supporting Document', 40, <?php echo !empty($remarks) ? '225' : '215'; ?>);
                
                // Decorative line
                pdf.setDrawColor(255, 165, 2); // Warning color
                pdf.setLineWidth(1);
                pdf.line(40, <?php echo !empty($remarks) ? '228' : '218'; ?>, pageWidth - 40, <?php echo !empty($remarks) ? '228' : '218'; ?>);
                
                <?php if($doc_name): ?>
                // Document name
                pdf.setFontSize(14);
                pdf.setTextColor(45, 52, 54);
                pdf.text('Document:', 40, <?php echo !empty($remarks) ? '240' : '230'; ?>);
                pdf.setFontSize(13);
                pdf.setTextColor(99, 110, 114);
                pdf.text('<?php echo htmlspecialchars($original_filename); ?>', 75, <?php echo !empty($remarks) ? '240' : '230'; ?>);
                
                // Document status
                pdf.setFontSize(12);
                pdf.setTextColor(46, 213, 115); // Success color
                pdf.text('✓ Successfully Uploaded', 75, <?php echo !empty($remarks) ? '247' : '237'; ?>);
                <?php else: ?>
                pdf.setFontSize(14);
                pdf.setTextColor(99, 110, 114);
                pdf.text('No supporting document uploaded', 40, <?php echo !empty($remarks) ? '240' : '230'; ?>);
                <?php endif; ?>
                
                // ===========================================
                // STATUS SECTION
                // ===========================================
                // Status box
                const statusY = <?php echo $doc_name ? (!empty($remarks) ? '260' : '250') : (!empty($remarks) ? '250' : '240'); ?>;
                pdf.setFillColor(248, 249, 250); // Light background
                pdf.roundedRect(40, statusY, pageWidth - 80, 25, 5, 5, 'F');
                
                // Status text
                pdf.setFontSize(16);
                pdf.setTextColor(255, 165, 2); // Warning color for pending
                pdf.text('Status: PENDING REVIEW', pageWidth / 2, statusY + 15, { align: 'center' });
                
                // Status description
                pdf.setFontSize(11);
                pdf.setTextColor(99, 110, 114);
                pdf.text('Your application is now under review by the administration committee.', pageWidth / 2, statusY + 28, { align: 'center' });
                pdf.text('Expected review time: 10-15 working days', pageWidth / 2, statusY + 35, { align: 'center' });
                
                // ===========================================
                // FOOTER
                // ===========================================
                pdf.setFontSize(10);
                pdf.setTextColor(150, 150, 150);
               
                
                // ===========================================
                // SAVE PDF
                // ===========================================
                const fileName = 'Application_<?php echo str_replace(" ", "_", $faculty["name"]); ?>_<?php echo $application_id; ?>.pdf';
                pdf.save(fileName);
                
                // Show success message
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-check"></i> PDF Downloaded!';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                }, 500);
                
            } catch (error) {
                console.error('Error generating PDF:', error);
                button.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error!';
                button.disabled = false;
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
                
                alert('Failed to generate PDF. Please try again or contact support.');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if($upload_success && $db_success): ?>
            // Add celebration effect for successful submission
            setTimeout(() => {
                const successIcon = document.querySelector('.status-icon.success');
                successIcon.style.animation = 'none';
                setTimeout(() => {
                    successIcon.style.animation = 'pulse 2s infinite';
                }, 50);
            }, 1000);
            <?php endif; ?>
            
            // Disable back button to prevent resubmission
            history.pushState(null, null, location.href);
            window.onpopstate = function() {
                history.go(1);
            };
            
            // Add click tracking for buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    if(this.href) {
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                        this.style.pointerEvents = 'none';
                    }
                });
            });
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Alt + P: Download PDF
                if (e.altKey && e.key === 'p') {
                    e.preventDefault();
                    const pdfBtn = document.querySelector('.btn-warning');
                    if(pdfBtn) pdfBtn.click();
                }
            });
        });
    </script>
</body>
</html>