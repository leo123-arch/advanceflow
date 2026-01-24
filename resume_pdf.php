<?php
// Start output buffering at the very beginning
ob_start();
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

// Fetch API scores
$api_query = mysqli_query($conn, "SELECT api_score, cat1, cat2, cat3, created_at 
                                 FROM promotion_applications 
                                 WHERE faculty_id='$faculty_id' 
                                 ORDER BY created_at DESC 
                                 LIMIT 1");
$api_data = mysqli_fetch_assoc($api_query);

// Check if research_uploads table exists and fetch research contributions
$research_data = [];
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'research_uploads'");
if(mysqli_num_rows($table_check) > 0) {
    $research_query = mysqli_query($conn, "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id'");
    while($row = mysqli_fetch_assoc($research_query)) {
        $research_data[] = $row;
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_resume'])) {
    try {
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Check if headers already sent
        if (headers_sent()) {
            throw new Exception('Headers already sent. Cannot generate PDF.');
        }
        
        // Check if FPDF exists
        if (!file_exists('fpdf186/fpdf.php')) {
            throw new Exception('FPDF library not found. Please ensure fpdf186 directory exists with fpdf.php inside.');
        }
        
        require_once "fpdf186/fpdf.php";
        
        // Get selected sections
        $include_personal = isset($_POST['include_personal']) ? 1 : 0;
        $include_education = isset($_POST['include_education']) ? 1 : 0;
        $include_api = isset($_POST['include_api']) ? 1 : 0;
        $include_research = isset($_POST['include_research']) ? 1 : 0;
        $include_teaching = isset($_POST['include_teaching']) ? 1 : 0;
        
        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Header
        $pdf->SetFont('Arial','B',20);
        $pdf->SetTextColor(41, 128, 185);
        $pdf->Cell(0,15,"ACADEMIC CURRICULUM VITAE",0,1,'C');
        $pdf->Ln(5);
        
        // Personal Information Section
        if($include_personal) {
            $pdf->SetFont('Arial','B',16);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0,10,"PERSONAL INFORMATION",0,1,'L');
            $pdf->SetFont('Arial','',12);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(0,7,"Name: ".$faculty['name'],0,1);
            $pdf->Cell(0,7,"Department: ".$faculty['department'],0,1);
            $pdf->Cell(0,7,"Email: ".$faculty['email'],0,1);
            $pdf->Cell(0,7,"Qualification: ".$faculty['qualification'],0,1);
            $pdf->Cell(0,7,"Experience: ".$faculty['experience']." years",0,1);
            $pdf->Cell(0,7,"Role: ".ucfirst($faculty['role']),0,1);
            $pdf->Ln(10);
        }
        
        // Education Section
        if($include_education) {
            $pdf->SetFont('Arial','B',16);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0,10,"EDUCATIONAL QUALIFICATIONS",0,1,'L');
            $pdf->SetFont('Arial','',12);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(0,7,$faculty['qualification']);
            $pdf->Ln(5);
        }
        
        // API Score Section
        if($include_api && $api_data) {
            $pdf->SetFont('Arial','B',16);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0,10,"ACADEMIC PERFORMANCE INDICATOR (API) SCORE",0,1,'L');
            $pdf->SetFont('Arial','',12);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(0,7,"Total API Score: ".$api_data['api_score'],0,1);
            $pdf->Cell(0,7,"Teaching Activities (Category I): ".$api_data['cat1']." points",0,1);
            $pdf->Cell(0,7,"Co-curricular Activities (Category II): ".$api_data['cat2']." points",0,1);
            $pdf->Cell(0,7,"Research Contributions (Category III): ".$api_data['cat3']." points",0,1);
            $pdf->Cell(0,7,"Last Calculated: ".date('d M, Y', strtotime($api_data['created_at'])),0,1);
            $pdf->Ln(5);
        }
        
        // Research Section
        if($include_research && !empty($research_data)) {
            $pdf->SetFont('Arial','B',16);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0,10,"RESEARCH CONTRIBUTIONS",0,1,'L');
            $pdf->SetFont('Arial','B',12);
            $pdf->SetTextColor(0,0,0);
            
            $counter = 1;
            foreach($research_data as $research) {
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(0,7,$counter.". ".($research['title'] ?? 'Research Item'),0,1);
                $pdf->SetFont('Arial','I',11);
                $pdf->Cell(0,6,"Type: ".($research['category'] ?? 'Research'),0,1);
                $pdf->SetFont('Arial','',11);
                if(!empty($research['description'])) {
                    $pdf->MultiCell(0,6,$research['description']);
                }
                $pdf->Ln(3);
                $counter++;
            }
            $pdf->Ln(5);
        }
        
        // Teaching Experience Section
        if($include_teaching) {
            $pdf->SetFont('Arial','B',16);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0,10,"TEACHING EXPERIENCE",0,1,'L');
            $pdf->SetFont('Arial','',12);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(0,7,"Total Teaching Experience: ".$faculty['experience']." years",0,1);
            $pdf->Ln(5);
        }
        
        // Footer
        $pdf->SetY(-30);
        $pdf->SetFont('Arial','I',10);
        $pdf->SetTextColor(100,100,100);
        $pdf->Cell(0,7,"Generated on: ".date('F j, Y'),0,1,'C');
        $pdf->Cell(0,7,"Career Advancement System | AI-Powered Resume Builder",0,1,'C');
        
        // Output PDF
        $filename = "Resume_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $faculty['name']) . "_" . date('Ymd') . ".pdf";
        
        // Force download
        $pdf->Output('D', $filename);
        exit();
        
    } catch (Exception $e) {
        // Log error
        error_log('PDF Generation Error: ' . $e->getMessage());
        
        // Restart output buffer for error display
        ob_start();
        
        // You can either show the error or redirect
        $error_message = 'Error generating PDF: ' . $e->getMessage();
        
        // If we're in development, show error
        if (isset($_GET['debug'])) {
            die($error_message);
        } else {
            // In production, redirect back with error message
            $_SESSION['error'] = $error_message;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// If we get here, continue with HTML output
ob_end_clean(); // Clean any previous output
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder | Career Advancement System</title>
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
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark-text);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            display: inline-block;
            margin-top: 20px;
            font-size: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Error Message */
        .error-message {
            background: #ff6b6b;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Main Content */
        .content-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
        }

        /* Resume Preview */
        .resume-preview {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            height: fit-content;
        }

        .preview-header {
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .preview-header h2 {
            font-size: 2rem;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .preview-header p {
            color: var(--gray-text);
            font-size: 1.1rem;
        }

        .preview-section {
            margin-bottom: 30px;
            padding: 25px;
            background: var(--light-bg);
            border-radius: 15px;
            border-left: 4px solid var(--primary-color);
        }

        .section-title {
            font-size: 1.4rem;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .info-item {
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-text);
            min-width: 120px;
        }

        .info-value {
            color: var(--gray-text);
            flex: 1;
        }

        .api-score-display {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            color: white;
            margin-bottom: 20px;
        }

        .api-total {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .api-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .category-breakdown {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .category-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .category-score {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .category-name {
            font-size: 0.9rem;
            color: var(--gray-text);
        }

        /* Research Items */
        .research-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 3px solid var(--secondary-color);
        }

        .research-title {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .research-type {
            display: inline-block;
            background: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        .research-desc {
            color: var(--gray-text);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Settings Panel */
        .settings-panel {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            height: fit-content;
        }

        .settings-header {
            border-bottom: 2px solid var(--light-bg);
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .settings-header h3 {
            font-size: 1.5rem;
            color: var(--dark-text);
            font-weight: 700;
        }

        .settings-group {
            margin-bottom: 30px;
        }

        .settings-group h4 {
            font-size: 1.1rem;
            color: var(--dark-text);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-group h4 i {
            color: var(--primary-color);
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 10px;
            transition: all 0.3s;
        }

        .checkbox-item:hover {
            background: rgba(106, 17, 203, 0.05);
            transform: translateX(5px);
        }

        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .checkbox-item label {
            flex: 1;
            cursor: pointer;
            font-weight: 500;
            color: var(--dark-text);
        }

        .generate-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .generate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        .generate-btn:active {
            transform: translateY(-1px);
        }

        .generate-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 25px;
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 20px;
            text-align: center;
        }

        .back-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 50px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: var(--light-bg);
            border-radius: 15px;
            margin-top: 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: var(--gray-text);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--gray-text);
            margin-bottom: 20px;
        }

        .add-btn {
            display: inline-block;
            padding: 10px 25px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .add-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .resume-preview, .settings-panel {
                padding: 25px;
            }
            
            .category-breakdown {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .info-label {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> AI Resume Builder</h1>
            <p>Generate professional academic resumes automatically using your profile data and achievements</p>
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Faculty: <?php echo htmlspecialchars($faculty['name']); ?>
                | Department: <?php echo htmlspecialchars($faculty['department']); ?>
            </div>
        </div>

        <!-- Error Message (if any) -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Debug Info (only show if debug parameter is set) -->
        <?php if(isset($_GET['debug'])): ?>
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h3>Debug Information:</h3>
                <p>FPDF Exists: <?php echo file_exists('fpdf186/fpdf.php') ? 'Yes' : 'No'; ?></p>
                <p>FPDF Path: <?php echo realpath('fpdf186/fpdf.php'); ?></p>
                <p>Faculty Name: <?php echo $faculty['name']; ?></p>
                <p>API Data: <?php echo $api_data ? 'Available' : 'Not Available'; ?></p>
                <p>Research Items: <?php echo count($research_data); ?></p>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Resume Preview -->
            <div class="resume-preview">
                <div class="preview-header">
                    <h2>Curriculum Vitae</h2>
                    <p>Preview of your professional academic resume</p>
                </div>

                <!-- Personal Information -->
                <div class="preview-section">
                    <h3 class="section-title"><i class="fas fa-user-circle"></i> Personal Information</h3>
                    <div class="info-item">
                        <span class="info-label">Full Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($faculty['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Department:</span>
                        <span class="info-value"><?php echo htmlspecialchars($faculty['department']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($faculty['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Qualification:</span>
                        <span class="info-value"><?php echo htmlspecialchars($faculty['qualification']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Experience:</span>
                        <span class="info-value"><?php echo htmlspecialchars($faculty['experience']); ?> years</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role:</span>
                        <span class="info-value"><?php echo ucfirst($faculty['role']); ?></span>
                    </div>
                </div>

                <!-- API Score -->
                <?php if($api_data): ?>
                <div class="preview-section">
                    <h3 class="section-title"><i class="fas fa-chart-line"></i> Academic Performance</h3>
                    <div class="api-score-display">
                        <div class="api-total"><?php echo $api_data['api_score']; ?></div>
                        <div class="api-label">Total API Score</div>
                    </div>
                    <div class="category-breakdown">
                        <div class="category-item">
                            <div class="category-score"><?php echo $api_data['cat1']; ?></div>
                            <div class="category-name">Teaching</div>
                        </div>
                        <div class="category-item">
                            <div class="category-score"><?php echo $api_data['cat2']; ?></div>
                            <div class="category-name">Co-curricular</div>
                        </div>
                        <div class="category-item">
                            <div class="category-score"><?php echo $api_data['cat3']; ?></div>
                            <div class="category-name">Research</div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="preview-section">
                    <h3 class="section-title"><i class="fas fa-chart-line"></i> Academic Performance</h3>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h4>No API Score Calculated</h4>
                        <p>Calculate your API score to include it in your resume</p>
                        <a href="advanced_api_form.php" class="add-btn">Calculate API Score</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Research Contributions -->
                <?php if(!empty($research_data)): ?>
                <div class="preview-section">
                    <h3 class="section-title"><i class="fas fa-flask"></i> Research Contributions</h3>
                    <?php foreach($research_data as $research): ?>
                    <div class="research-item">
                        <div class="research-title"><?php echo htmlspecialchars($research['title'] ?? 'Research Item'); ?></div>
                        <span class="research-type"><?php echo htmlspecialchars($research['category'] ?? 'Research'); ?></span>
                        <?php if(!empty($research['description'])): ?>
                        <div class="research-desc"><?php echo htmlspecialchars($research['description']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="preview-section">
                    <h3 class="section-title"><i class="fas fa-flask"></i> Research Contributions</h3>
                    <div class="empty-state">
                        <i class="fas fa-flask"></i>
                        <h4>No Research Added</h4>
                        <p>Add your research publications and projects to include them in your resume</p>
                        <a href="faculty_research_upload.php" class="add-btn">Add Research</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Settings Panel -->
            <div class="settings-panel">
                <div class="settings-header">
                    <h3><i class="fas fa-cog"></i> Resume Settings</h3>
                    <p style="color: var(--gray-text); margin-top: 10px;">Select sections to include in your resume</p>
                </div>

                <form method="POST" action="" id="resumeForm">
                    <div class="settings-group">
                        <h4><i class="fas fa-check-square"></i> Select Sections</h4>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="personal" name="include_personal" checked>
                            <label for="personal">Personal Information</label>
                        </div>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="education" name="include_education" checked>
                            <label for="education">Educational Qualifications</label>
                        </div>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="api" name="include_api" <?php echo $api_data ? 'checked' : 'disabled'; ?>>
                            <label for="api">API Score Analysis <?php echo !$api_data ? '(Not Available)' : ''; ?></label>
                        </div>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="research" name="include_research" <?php echo !empty($research_data) ? 'checked' : 'disabled'; ?>>
                            <label for="research">Research Contributions <?php echo empty($research_data) ? '(Not Available)' : ''; ?></label>
                        </div>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="teaching" name="include_teaching" checked>
                            <label for="teaching">Teaching Experience</label>
                        </div>
                    </div>

                    <button type="submit" name="generate_resume" class="generate-btn" id="generateBtn">
                        <i class="fas fa-file-pdf"></i> Generate & Download Resume
                    </button>
                </form>

                <div style="text-align: center; margin-top: 25px;">
                    <a href="faculty_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <h4 style="color: var(--dark-text); margin-bottom: 10px; font-size: 1rem;">
                        <i class="fas fa-lightbulb"></i> Tips for Better Resume
                    </h4>
                    <ul style="color: var(--gray-text); font-size: 0.9rem; padding-left: 20px;">
                        <li>Update your profile information regularly</li>
                        <li>Add all research publications and projects</li>
                        <li>Calculate API score for accurate performance metrics</li>
                        <li>Include recent achievements and certifications</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | AI Resume Builder v2.0</p>
            <p>Resume generated based on data as of <?php echo date('F j, Y, h:i A'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generateBtn');
            const form = document.getElementById('resumeForm');
            
            form.addEventListener('submit', function(e) {
                // Disable button and show loading
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Resume...';
                
                // Check if at least one section is selected
                const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)');
                if (checkboxes.length === 0) {
                    alert('Please select at least one section to include in your resume.');
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate & Download Resume';
                    e.preventDefault();
                    return;
                }
                
                // Form will submit normally
            });
            
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
            // Check for disabled checkboxes
            const disabledCheckboxes = document.querySelectorAll('input[type="checkbox"]:disabled');
            disabledCheckboxes.forEach(cb => {
                const label = cb.nextElementSibling;
                if(label) {
                    label.style.opacity = '0.6';
                    label.style.cursor = 'not-allowed';
                }
            });
        });
    </script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 3c9ca7f4e7925fe3261a82e10ffb2dcc04d3d0a3
