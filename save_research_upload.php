<?php
session_start();
include "config.php";
require 'vendor/autoload.php';  // For PDF parser
require 'ai_classifier.php';    // AI model

use Smalot\PdfParser\Parser;

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Get title safely
$title = "";
if(isset($_POST['title'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
} else {
    // Try to get title from filename if not provided
    if(isset($_FILES["file"]["name"])) {
        $title = pathinfo($_FILES["file"]["name"], PATHINFO_FILENAME);
        $title = mysqli_real_escape_string($conn, $title);
    }
}

// Get faculty info for display
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

$uploadDir = "uploads/research/";
if(!is_dir($uploadDir)){ 
    mkdir($uploadDir, 0777, true); 
}

$fileName = time() . "_" . basename($_FILES["file"]["name"]);
$originalFileName = $_FILES["file"]["name"];
$targetFile = $uploadDir . $fileName;

$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['pdf','jpg','jpeg','png'];
$fileSize = $_FILES["file"]["size"] / 1024 / 1024; // Convert to MB

// Start HTML output
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Research Upload | Career Advancement System</title>
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
            --error-color: #ff4757;
            --info-color: #17a2b8;
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .status-icon.processing {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.2));
            color: var(--primary-color);
            border: 3px solid var(--primary-color);
        }

        .status-icon.success {
            background: linear-gradient(135deg, rgba(46, 213, 115, 0.1), rgba(46, 213, 115, 0.2));
            color: var(--success-color);
            border: 3px solid var(--success-color);
        }

        .status-icon.error {
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

        /* Processing Steps */
        .processing-steps {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 40px 0;
            position: relative;
        }

        .processing-step {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            text-align: left;
            position: relative;
            overflow: hidden;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        .processing-step.active {
            border-left-color: var(--primary-color);
            background: rgba(106, 17, 203, 0.05);
        }

        .processing-step.completed {
            border-left-color: var(--success-color);
            background: rgba(46, 213, 115, 0.05);
        }

        .processing-step.error {
            border-left-color: var(--error-color);
            background: rgba(255, 71, 87, 0.05);
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .step-icon.processing {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .step-icon.success {
            background: linear-gradient(135deg, var(--success-color), #25b764);
            color: white;
        }

        .step-icon.error {
            background: linear-gradient(135deg, var(--error-color), #ff3547);
            color: white;
        }

        .step-content {
            flex: 1;
        }

        .step-title {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .step-description {
            color: var(--gray-text);
            font-size: 0.95rem;
        }

        /* Upload Details */
        .upload-details {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid var(--info-color);
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
            color: var(--info-color);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .detail-value {
            color: var(--dark-text);
            font-weight: 500;
            word-break: break-word;
        }

        /* AI Detection Result */
        .ai-result {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .ai-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            margin: 10px 0;
            border: 2px solid rgba(255, 255, 255, 0.3);
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
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="processing-card">
            
            <div class="status-icon processing">
                <i class="fas fa-brain"></i>
            </div>

            <h1 class="status-title">Processing Research Upload</h1>
            <p class="status-subtitle">AI is analyzing your document and classifying it automatically...</p>

            <!-- Processing Steps -->
            <div class="processing-steps">
                
                <?php
                // File validation step
                if(!in_array($ext, $allowed)): 
                ?>
                <div class="processing-step error">
                    <div class="step-icon error">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">File Validation Failed</div>
                        <div class="step-description">Invalid file type. Allowed formats: PDF, JPG, JPEG, PNG</div>
                    </div>
                </div>
                <?php 
                    echo "<script>
                        setTimeout(() => {
                            alert('Invalid file type. Allowed: PDF, JPG, JPEG, PNG');
                            history.back();
                        }, 1000);
                    </script>";
                    exit();
                else: 
                ?>
                <div class="processing-step completed">
                    <div class="step-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">File Validated Successfully</div>
                        <div class="step-description">File type: .<?php echo strtoupper($ext); ?> | Size: <?php echo number_format($fileSize, 2); ?> MB</div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // File upload step
                if(!move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)): 
                ?>
                <div class="processing-step error">
                    <div class="step-icon error">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">File Upload Failed</div>
                        <div class="step-description">Could not move uploaded file to server storage</div>
                    </div>
                </div>
                <?php 
                    echo "<script>
                        setTimeout(() => {
                            alert('File upload failed!');
                            history.back();
                        }, 1000);
                    </script>";
                    exit();
                else: 
                ?>
                <div class="processing-step completed">
                    <div class="step-icon success">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">File Uploaded Successfully</div>
                        <div class="step-description">Document stored securely on server</div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                /* -------------------------
                     AI: Extract Text
                ---------------------------*/
                $text = "";
                $extraction_method = "";

                if($ext == "pdf"){
                    $extraction_method = "PDF Text Extraction";
                    try {
                        $parser = new Parser();
                        $pdf = $parser->parseFile($targetFile);
                        $text = $pdf->getText();
                        $extraction_success = true;
                    } catch (Exception $e) {
                        $text = $title . " " . $fileName;  // fallback if PDF invalid
                        $extraction_method = "Fallback Text Generation";
                        $extraction_success = false;
                    }
                } else {
                    // Image fallback (OCR not installed)
                    $text = $title . " " . $fileName;
                    $extraction_method = "Image Title Processing";
                    $extraction_success = true;
                }
                ?>

                <div class="processing-step <?php echo $extraction_success ? 'completed' : 'error'; ?>">
                    <div class="step-icon <?php echo $extraction_success ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo $extraction_success ? 'fa-file-alt' : 'fa-exclamation-triangle'; ?>"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Text Extraction <?php echo $extraction_success ? 'Completed' : 'Failed'; ?></div>
                        <div class="step-description"><?php echo $extraction_method; ?> | Extracted <?php echo strlen($text); ?> characters</div>
                    </div>
                </div>

                <?php
                /* -------------------------
                     AI: Predict Category
                ---------------------------*/
                $predictedCategory = classifyDocumentText($text, $fileName);
                ?>

                <div class="processing-step completed">
                    <div class="step-icon success">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">AI Classification Complete</div>
                        <div class="step-description">Document analyzed and categorized using machine learning</div>
                    </div>
                </div>

                <?php
                /* -------------------------
                     SAVE TO DATABASE
                     Check table structure first
                ---------------------------*/
                
                // First, check if the table exists and get its structure
                $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'research_uploads'");
                if(mysqli_num_rows($table_check) > 0) {
                    // Table exists, check columns
                    $columns_check = mysqli_query($conn, "DESCRIBE research_uploads");
                    $has_created_at = false;
                    $has_upload_date = false;
                    
                    while($column = mysqli_fetch_assoc($columns_check)) {
                        if($column['Field'] == 'created_at') {
                            $has_created_at = true;
                        }
                        if($column['Field'] == 'upload_date') {
                            $has_upload_date = true;
                        }
                    }
                    
                    // Build query based on available columns
                    if($has_created_at) {
                        $query = "INSERT INTO research_uploads (faculty_id, title, category, filename, created_at)
                                  VALUES ('$faculty_id', '$title', '$predictedCategory', '$fileName', NOW())";
                    } elseif($has_upload_date) {
                        $query = "INSERT INTO research_uploads (faculty_id, title, category, filename, upload_date)
                                  VALUES ('$faculty_id', '$title', '$predictedCategory', '$fileName', NOW())";
                    } else {
                        // If neither timestamp column exists, insert without it
                        $query = "INSERT INTO research_uploads (faculty_id, title, category, filename)
                                  VALUES ('$faculty_id', '$title', '$predictedCategory', '$fileName')";
                    }
                    
                    if(mysqli_query($conn, $query)) {
                        $db_success = true;
                        $upload_id = mysqli_insert_id($conn);
                    } else {
                        $db_success = false;
                        error_log("Database Insert Error: " . mysqli_error($conn));
                    }
                } else {
                    // Table doesn't exist, try to create it
                    $create_table_query = "CREATE TABLE IF NOT EXISTS research_uploads (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        faculty_id INT(11) NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        category VARCHAR(100) NOT NULL,
                        filename VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE
                    )";
                    
                    if(mysqli_query($conn, $create_table_query)) {
                        // Now insert the data
                        $query = "INSERT INTO research_uploads (faculty_id, title, category, filename)
                                  VALUES ('$faculty_id', '$title', '$predictedCategory', '$fileName')";
                        
                        if(mysqli_query($conn, $query)) {
                            $db_success = true;
                            $upload_id = mysqli_insert_id($conn);
                        } else {
                            $db_success = false;
                            error_log("Database Insert Error: " . mysqli_error($conn));
                        }
                    } else {
                        $db_success = false;
                        error_log("Table Creation Error: " . mysqli_error($conn));
                    }
                }
                ?>

                <div class="processing-step <?php echo $db_success ? 'completed' : 'error'; ?>">
                    <div class="step-icon <?php echo $db_success ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo $db_success ? 'fa-database' : 'fa-exclamation-triangle'; ?>"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Database Storage <?php echo $db_success ? 'Successful' : 'Failed'; ?></div>
                        <div class="step-description"><?php echo $db_success ? 'Data saved to research records' : 'Failed to save to database'; ?></div>
                    </div>
                </div>

            </div>

            <?php if($db_success): ?>

            <!-- Upload Details -->
            <div class="upload-details">
                <h3 class="details-header">
                    <i class="fas fa-file-upload"></i> Upload Summary
                </h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Research Title</div>
                        <div class="detail-value"><?php echo htmlspecialchars($title); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Original Filename</div>
                        <div class="detail-value"><?php echo htmlspecialchars($originalFileName); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">File Type</div>
                        <div class="detail-value"><?php echo strtoupper($ext); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">File Size</div>
                        <div class="detail-value"><?php echo number_format($fileSize, 2); ?> MB</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Uploaded By</div>
                        <div class="detail-value"><?php echo htmlspecialchars($faculty['name']); ?></div>
                    </div>
                    <?php if(isset($upload_id)): ?>
                    <div class="detail-item">
                        <div class="detail-label">Upload ID</div>
                        <div class="detail-value">#<?php echo $upload_id; ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Detection Result -->
            <div class="ai-result">
                <h3 style="margin-bottom: 15px; font-size: 1.2rem; opacity: 0.9;">
                    <i class="fas fa-robot"></i> AI Classification Result
                </h3>
                <div class="ai-badge">
                    <i class="fas fa-tag"></i> Category: <?php echo htmlspecialchars($predictedCategory); ?>
                </div>
                <p style="margin-top: 15px; opacity: 0.9; font-size: 0.95rem;">
                    This category was automatically detected using machine learning algorithms
                </p>
            </div>

            <!-- What's Next -->
            <div style="background: rgba(46, 213, 115, 0.1); padding: 20px; border-radius: 15px; margin: 20px 0; text-align: left;">
                <h4 style="color: var(--success-color); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-lightbulb"></i> What's Next?
                </h4>
                <ul style="color: var(--gray-text); padding-left: 20px;">
                    <li>Your research document has been added to your profile</li>
                    <li>It will be included in your API score calculations</li>
                    <li>You can view all your research uploads in the dashboard</li>
                    <li>Category classification helps in organizing your research portfolio</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="view_research_uploads.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Uploads
                </a>
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <!-- Auto Redirect Timer -->
            <div style="margin-top: 25px; font-size: 0.9rem; color: var(--gray-text);">
                <i class="fas fa-clock"></i> Redirecting to uploads page in <span id="countdown">5</span> seconds...
            </div>

            <?php else: ?>

            <!-- Error State -->
            <div style="background: rgba(255, 71, 87, 0.1); padding: 25px; border-radius: 15px; margin: 20px 0; border-left: 4px solid var(--error-color);">
                <h3 style="color: var(--error-color); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> Database Error
                </h3>
                <p style="color: var(--gray-text); margin-bottom: 15px;">
                    The document was uploaded successfully but we couldn't save it to the database.
                </p>
                <p style="color: var(--gray-text); font-size: 0.9rem; font-style: italic;">
                    Error: <?php echo htmlspecialchars(mysqli_error($conn)); ?>
                </p>
            </div>

            <!-- Action Buttons for Error -->
            <div class="action-buttons">
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="fas fa-undo"></i> Try Again
                </a>
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>

            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | AI Research Upload</p>
            <p>AI Processing ID: <?php echo date('Ymd-His'); ?>-<?php echo $faculty_id; ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($db_success): ?>
            // Auto redirect after 5 seconds for success
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'view_research_uploads.php';
                }
            }, 1000);
            
            // Add success celebration
            setTimeout(() => {
                const successIcon = document.querySelector('.status-icon.processing');
                successIcon.classList.remove('processing');
                successIcon.classList.add('success');
                successIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                
                const title = document.querySelector('.status-title');
                title.textContent = 'Upload Complete!';
                
                const subtitle = document.querySelector('.status-subtitle');
                subtitle.textContent = 'Research document uploaded and classified successfully';
                
                // Add subtle confetti for success
                const colors = ['#2ed573', '#2575fc', '#6a11cb', '#ff416c'];
                for (let i = 0; i < 25; i++) {
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
            }, 500);
            
            // Disable back button to prevent resubmission
            history.pushState(null, null, location.href);
            window.onpopstate = function() {
                history.go(1);
            };
            <?php endif; ?>
            
            // Add step animations
            const steps = document.querySelectorAll('.processing-step');
            steps.forEach((step, index) => {
                setTimeout(() => {
                    step.style.opacity = '1';
                    step.style.transform = 'translateX(0)';
                }, index * 300);
            });
        });
    </script>
</body>
</html>

<?php
// Flush output buffer
ob_end_flush();
?>