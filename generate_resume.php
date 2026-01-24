<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty data
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

if(!$faculty) {
    header("Location: login.php");
    exit();
}

// Research stats
$research_stats = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT 
        SUM(category='Paper') AS papers,
        SUM(category='Book') AS books,
        SUM(category='Conference') AS conferences,
        SUM(category='Patent') AS patents
     FROM research_uploads
     WHERE faculty_id='$faculty_id'"
));

// Fetch individual research items (checking available columns)
$research_items_query = mysqli_query($conn,
    "SELECT * FROM research_uploads 
     WHERE faculty_id='$faculty_id' 
     ORDER BY id DESC"
);
$research_items = [];
while($row = mysqli_fetch_assoc($research_items_query)) {
    $research_items[] = $row;
}

// API score
$api_score_result = mysqli_query($conn,
    "SELECT api_score, cat1, cat2, cat3, created_at 
     FROM promotion_applications 
     WHERE faculty_id='$faculty_id' 
     ORDER BY id DESC LIMIT 1"
);
$api_score_data = mysqli_fetch_assoc($api_score_result);

// Teaching activities count
$teaching_activities = 0;
$teaching_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'teaching_activities'");
if(mysqli_num_rows($teaching_table_check) > 0) {
    $teaching_result = mysqli_query($conn,
        "SELECT COUNT(id) as count FROM teaching_activities WHERE faculty_id='$faculty_id'"
    );
    $teaching_data = mysqli_fetch_assoc($teaching_result);
    $teaching_activities = $teaching_data['count'] ?? 0;
}

// Get promotion status
$promotion_status_result = mysqli_query($conn,
    "SELECT status FROM promotion_applications 
     WHERE faculty_id='$faculty_id' 
     ORDER BY id DESC LIMIT 1"
);
$promotion_status_data = mysqli_fetch_assoc($promotion_status_result);
$promotion_status = $promotion_status_data['status'] ?? 'Not Applied';

// ---------------- AI SUMMARY LOGIC ----------------
function generateAISummary($faculty, $research_stats, $api_score_data, $teaching_activities) {
    $summary = "";
    
    // Opening based on experience
    if($faculty['experience'] >= 10) {
        $summary .= "Seasoned academic professional with over " . $faculty['experience'] . " years of experience in " . $faculty['department'] . ". ";
    } elseif($faculty['experience'] >= 5) {
        $summary .= "Experienced faculty member with " . $faculty['experience'] . " years of dedicated service in " . $faculty['department'] . ". ";
    } else {
        $summary .= "Dedicated academic professional with " . $faculty['experience'] . " years of experience in " . $faculty['department'] . ". ";
    }
    
    // Add research achievements
    $research_points = 0;
    if(isset($research_stats['papers']) && $research_stats['papers'] >= 10) {
        $summary .= "Extensive research portfolio with " . $research_stats['papers'] . " publications in peer-reviewed journals. ";
        $research_points += 2;
    } elseif(isset($research_stats['papers']) && $research_stats['papers'] >= 5) {
        $summary .= "Strong research background with " . $research_stats['papers'] . " research publications. ";
        $research_points += 1;
    } elseif(isset($research_stats['papers']) && $research_stats['papers'] > 0) {
        $summary .= "Active researcher with " . $research_stats['papers'] . " published papers. ";
    }
    
    if(isset($research_stats['patents']) && $research_stats['patents'] > 0) {
        $summary .= "Innovative contributor with " . $research_stats['patents'] . " patents. ";
        $research_points += 2;
    }
    
    if(isset($research_stats['books']) && $research_stats['books'] > 0) {
        $summary .= "Authored " . $research_stats['books'] . " books/chapters. ";
        $research_points += 1;
    }
    
    if(isset($research_stats['conferences']) && $research_stats['conferences'] >= 5) {
        $summary .= "Active participant in " . $research_stats['conferences'] . " academic conferences. ";
    }
    
    // Add teaching expertise
    if($teaching_activities >= 10) {
        $summary .= "Committed to excellence in teaching with extensive classroom experience. ";
    } elseif($teaching_activities >= 5) {
        $summary .= "Demonstrated teaching proficiency with diverse pedagogical approaches. ";
    }
    
    // Add API score mention if available
    if($api_score_data && isset($api_score_data['api_score'])) {
        $summary .= "Achieved an API score of " . $api_score_data['api_score'] . ", ";
        if($api_score_data['api_score'] >= 90) {
            $summary .= "demonstrating exceptional academic performance. ";
        } elseif($api_score_data['api_score'] >= 80) {
            $summary .= "meeting promotion eligibility criteria. ";
        } elseif($api_score_data['api_score'] >= 70) {
            $summary .= "showing strong academic contributions. ";
        } else {
            $summary .= "with consistent academic engagement. ";
        }
    }
    
    // Add specialization based on department
    $department = strtolower($faculty['department']);
    if(strpos($department, 'computer') !== false || strpos($department, 'it') !== false) {
        $summary .= "Specialized in computing technologies and digital innovation. ";
    } elseif(strpos($department, 'science') !== false) {
        $summary .= "Expertise in scientific research methodologies. ";
    } elseif(strpos($department, 'engineering') !== false) {
        $summary .= "Technical expertise in engineering principles and applications. ";
    } elseif(strpos($department, 'management') !== false) {
        $summary .= "Strategic approach to academic and administrative leadership. ";
    }
    
    // Closing statement
    if($research_points >= 3) {
        $summary .= "A research-driven academic committed to advancing knowledge and mentoring the next generation of professionals.";
    } else {
        $summary .= "Dedicated to academic excellence, student success, and institutional development.";
    }
    
    return $summary;
}

$ai_summary = generateAISummary($faculty, $research_stats, $api_score_data, $teaching_activities);

// Calculate strengths
function calculateStrengths($faculty, $research_stats, $api_score_data) {
    $strengths = [];
    
    if($faculty['experience'] >= 5) {
        $strengths[] = ['icon' => 'fas fa-chart-line', 'title' => 'Experience', 'desc' => $faculty['experience'] . ' years in academia'];
    }
    
    if(isset($research_stats['papers']) && $research_stats['papers'] >= 5) {
        $strengths[] = ['icon' => 'fas fa-newspaper', 'title' => 'Publications', 'desc' => $research_stats['papers'] . ' research papers'];
    }
    
    if($api_score_data && isset($api_score_data['api_score']) && $api_score_data['api_score'] >= 80) {
        $strengths[] = ['icon' => 'fas fa-medal', 'title' => 'API Score', 'desc' => $api_score_data['api_score'] . ' (Eligible for promotion)'];
    } elseif($api_score_data && isset($api_score_data['api_score'])) {
        $strengths[] = ['icon' => 'fas fa-chart-line', 'title' => 'API Score', 'desc' => $api_score_data['api_score'] . ' points'];
    }
    
    if(isset($research_stats['patents']) && $research_stats['patents'] > 0) {
        $strengths[] = ['icon' => 'fas fa-certificate', 'title' => 'Innovation', 'desc' => $research_stats['patents'] . ' patents filed'];
    }
    
    if(isset($research_stats['books']) && $research_stats['books'] > 0) {
        $strengths[] = ['icon' => 'fas fa-book', 'title' => 'Author', 'desc' => $research_stats['books'] . ' books/chapters'];
    }
    
    if(count($strengths) < 4) {
        $strengths[] = ['icon' => 'fas fa-chalkboard-teacher', 'title' => 'Teaching', 'desc' => 'Dedicated educator'];
        $strengths[] = ['icon' => 'fas fa-users', 'title' => 'Mentorship', 'desc' => 'Student guidance'];
        $strengths[] = ['icon' => 'fas fa-graduation-cap', 'title' => 'Qualifications', 'desc' => $faculty['qualification']];
    }
    
    return array_slice($strengths, 0, 6);
}

$strengths = calculateStrengths($faculty, $research_stats, $api_score_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Generated Resume | Career Advancement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 30px;
            color: var(--dark-text);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .resume-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 50px 60px;
            position: relative;
            overflow: hidden;
        }

        .header-bg {
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .header-content h1 {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .header-content h2 {
            font-size: 1.5rem;
            font-weight: 400;
            opacity: 0.9;
            margin-bottom: 25px;
        }

        .contact-info {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        .contact-item i {
            font-size: 1.2rem;
            color: var(--success-color);
        }

        /* Content Sections */
        .resume-content {
            padding: 50px 60px;
        }

        .section {
            margin-bottom: 50px;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary-color);
        }

        .section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            margin-right: 20px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* AI Summary */
        .ai-summary {
            background: linear-gradient(135deg, var(--light-bg), #e3f2fd);
            padding: 30px;
            border-radius: 15px;
            border-left: 5px solid var(--secondary-color);
            margin-top: 20px;
            line-height: 1.8;
            font-size: 1.1rem;
            position: relative;
        }

        .ai-badge {
            position: absolute;
            top: -15px;
            right: 20px;
            background: var(--accent-color);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        /* Key Strengths */
        .strengths-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .strength-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-top: 4px solid var(--secondary-color);
        }

        .strength-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .strength-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .strength-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .strength-desc {
            color: var(--gray-text);
            line-height: 1.5;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            border-color: var(--secondary-color);
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--gray-text);
            font-weight: 600;
        }

        /* API Score Card */
        .api-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .api-score {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .api-label {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .api-categories {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .category-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .category-name {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .category-score {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Research List */
        .research-list {
            margin-top: 20px;
        }

        .research-item {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--success-color);
            transition: all 0.3s;
        }

        .research-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .research-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .research-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            flex: 1;
        }

        .research-type {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .research-desc {
            color: var(--gray-text);
            line-height: 1.6;
            margin-bottom: 10px;
        }

        /* Promotion Status */
        .promotion-status {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .status-label {
            font-size: 1.2rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .status-value {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid var(--light-bg);
        }

        .btn {
            padding: 18px 35px;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(52, 152, 219, 0.4);
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
        .resume-footer {
            background: var(--light-bg);
            padding: 25px 60px;
            text-align: center;
            color: var(--gray-text);
            font-size: 0.9rem;
            border-top: 1px solid #e9ecef;
        }

        .ai-watermark {
            margin-top: 10px;
            font-style: italic;
            opacity: 0.8;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .action-buttons {
                display: none;
            }
            
            .btn {
                display: none;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .resume-header, .resume-content {
                padding: 30px 25px;
            }
            
            .header-content h1 {
                font-size: 2.2rem;
            }
            
            .header-content h2 {
                font-size: 1.2rem;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .strengths-grid, .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .api-categories {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="resume-header">
            <div class="header-bg"></div>
            <div class="header-content">
                <h1><?php echo htmlspecialchars($faculty['name']); ?></h1>
                <h2><?php echo htmlspecialchars($faculty['department']); ?> Department</h2>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($faculty['email']); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span><?php echo htmlspecialchars($faculty['qualification']); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-briefcase"></i>
                        <span><?php echo htmlspecialchars($faculty['experience']); ?> years experience</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-user-tag"></i>
                        <span><?php echo ucfirst($faculty['role']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="resume-content">
            <!-- Professional Summary -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="section-title">AI-Generated Professional Summary</div>
                </div>
                <div class="ai-summary">
                    <div class="ai-badge">AI-Powered Analysis</div>
                    <p><?php echo $ai_summary; ?></p>
                </div>
            </div>

            <!-- Key Strengths -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="section-title">Key Strengths</div>
                </div>
                <div class="strengths-grid">
                    <?php foreach($strengths as $strength): ?>
                    <div class="strength-card">
                        <div class="strength-icon">
                            <i class="<?php echo $strength['icon']; ?>"></i>
                        </div>
                        <div class="strength-title"><?php echo $strength['title']; ?></div>
                        <div class="strength-desc"><?php echo $strength['desc']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Academic Statistics -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="section-title">Academic Statistics</div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $faculty['experience']; ?></div>
                        <div class="stat-label">Years of Experience</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $research_stats['papers'] ?? 0; ?></div>
                        <div class="stat-label">Research Papers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $teaching_activities; ?></div>
                        <div class="stat-label">Teaching Activities</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $research_stats['conferences'] ?? 0; ?></div>
                        <div class="stat-label">Conference Presentations</div>
                    </div>
                </div>
            </div>

            <!-- API Score -->
            <?php if($api_score_data && isset($api_score_data['api_score'])): ?>
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="section-title">Academic Performance Indicator (API)</div>
                </div>
                <div class="api-card">
                    <div class="api-score"><?php echo $api_score_data['api_score']; ?></div>
                    <div class="api-label">Total API Score</div>
                    
                    <?php if(isset($api_score_data['cat1']) && isset($api_score_data['cat2']) && isset($api_score_data['cat3'])): ?>
                    <div class="api-categories">
                        <div class="category-item">
                            <div class="category-name">Teaching (Category I)</div>
                            <div class="category-score"><?php echo $api_score_data['cat1']; ?></div>
                        </div>
                        <div class="category-item">
                            <div class="category-name">Co-curricular (Category II)</div>
                            <div class="category-score"><?php echo $api_score_data['cat2']; ?></div>
                        </div>
                        <div class="category-item">
                            <div class="category-name">Research (Category III)</div>
                            <div class="category-score"><?php echo $api_score_data['cat3']; ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Promotion Status -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="section-title">Promotion Status</div>
                </div>
                <div class="promotion-status">
                    <div class="status-label">Current Application Status</div>
                    <div class="status-value"><?php echo ucwords($promotion_status); ?></div>
                </div>
            </div>

            <!-- Research Contributions -->
            <?php if(!empty($research_items)): ?>
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="section-title">Research Contributions</div>
                </div>
                <div class="research-list">
                    <?php foreach($research_items as $research): ?>
                    <div class="research-item">
                        <div class="research-header">
                            <div class="research-title"><?php echo htmlspecialchars($research['title'] ?? 'Untitled Research'); ?></div>
                            <div class="research-type"><?php echo htmlspecialchars($research['category'] ?? 'Research'); ?></div>
                        </div>
                        <?php if(!empty($research['description'])): ?>
                        <div class="research-desc"><?php echo htmlspecialchars($research['description']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($research['file_name'])): ?>
                        <div class="research-meta">
                            <small>File: <?php echo htmlspecialchars($research['file_name']); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="resume_pdf.php" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF Resume
                </a>
                <a href="faculty_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="resume-footer">
            <p>Generated on <?php echo date('F j, Y'); ?> | Career Advancement System</p>
            <p class="ai-watermark">This resume was intelligently generated using AI analysis of your academic profile and achievements.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to strength cards on scroll
            const strengthCards = document.querySelectorAll('.strength-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, { threshold: 0.1 });

            strengthCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });

            // Print functionality
            const printBtn = document.querySelector('.btn-primary');
            if(printBtn) {
                printBtn.addEventListener('click', function(e) {
                    if(e.target.closest('.btn-primary')) {
                        setTimeout(() => {
                            alert('Your professional resume is ready. For best printing results, use "Save as PDF" option in print dialog.');
                        }, 1000);
                    }
                });
            }

            // Highlight API score
            const apiScore = document.querySelector('.api-score');
            if(apiScore) {
                setTimeout(() => {
                    apiScore.style.transition = 'transform 0.5s';
                    apiScore.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        apiScore.style.transform = 'scale(1)';
                    }, 500);
                }, 1500);
            }
        });
    </script>
</body>
</html>
