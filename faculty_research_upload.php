<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Research Document | Career Advancement System</title>
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
            --paper-color: #3498db;
            --book-color: #2ecc71;
            --conf-color: #9b59b6;
            --patent-color: #e74c3c;
            --project-color: #ff9800;
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px 0;
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 25px 30px;
            border-bottom: 2px solid var(--light-bg);
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-dept {
            opacity: 0.9;
            font-size: 0.85rem;
        }

        .sidebar-nav {
            padding: 0 25px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: var(--gray-text);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .sidebar-nav a:hover {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        }

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 25px;
            margin-top: 30px;
            border-top: 2px solid var(--light-bg);
            text-align: center;
            color: var(--gray-text);
            font-size: 0.85rem;
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            max-width: 700px;
            line-height: 1.6;
        }

        /* Upload Form */
        .upload-form {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .upload-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .form-header p {
            color: var(--gray-text);
            font-size: 1.1rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            display: block;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group label i {
            color: var(--primary-color);
            width: 20px;
        }

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e1e5ee;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .form-group input[type="text"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        /* File Upload Area */
        .file-upload-area {
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            background: rgba(106, 17, 203, 0.05);
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .file-upload-area:hover {
            background: rgba(106, 17, 203, 0.1);
            border-color: var(--secondary-color);
        }

        .file-upload-area i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: block;
        }

        .file-upload-text {
            font-size: 1.1rem;
            color: var(--dark-text);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .file-upload-subtext {
            color: var(--gray-text);
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-preview {
            margin-top: 20px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 10px;
            display: none;
        }

        .file-preview.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .file-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .file-details {
            flex: 1;
        }

        .file-name {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .file-size {
            color: var(--gray-text);
            font-size: 0.9rem;
        }

        /* Category Options */
        .category-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .category-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .category-option:hover {
            border-color: var(--primary-color);
            background: rgba(106, 17, 203, 0.05);
        }

        .category-option.selected {
            border-color: var(--primary-color);
            background: rgba(106, 17, 203, 0.1);
        }

        .category-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .icon-paper { background: linear-gradient(135deg, var(--paper-color), #2980b9); }
        .icon-book { background: linear-gradient(135deg, var(--book-color), #27ae60); }
        .icon-conference { background: linear-gradient(135deg, var(--conf-color), #8e44ad); }
        .icon-patent { background: linear-gradient(135deg, var(--patent-color), #c0392b); }
        .icon-project { background: linear-gradient(135deg, var(--project-color), #d35400); }

        .category-label {
            font-weight: 600;
            color: var(--dark-text);
        }

        /* Submit Button */
        .submit-btn {
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
            margin-top: 20px;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* AI Features */
        .ai-features {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: center;
        }

        .ai-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin: 10px 0;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        /* Upload Guidelines */
        .upload-guidelines {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
            border-left: 4px solid var(--primary-color);
        }

        .guidelines-header {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .guidelines-header i {
            color: var(--primary-color);
        }

        .guidelines-list {
            list-style: none;
            padding-left: 0;
        }

        .guidelines-list li {
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
            color: var(--gray-text);
        }

        .guidelines-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success-color);
            font-weight: bold;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
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

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }
            
            .main {
                margin-left: 250px;
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            
            .main {
                margin-left: 0;
                padding: 20px;
            }
            
            .upload-form {
                padding: 25px;
            }
            
            .category-options {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .main {
                padding: 15px;
            }
            
            .upload-form {
                padding: 20px;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> Career System</h2>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($faculty['name']); ?></div>
                <div class="user-dept"><?php echo htmlspecialchars($faculty['department']); ?></div>
            </div>
        </div>

        <div class="sidebar-nav">
            <!-- ONLY THESE 4 MENU ITEMS -->
            <a href="faculty_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="faculty_research_upload.php" class="active">
                <i class="fas fa-upload"></i> Upload Research
            </a>
            <a href="view_research_uploads.php">
                <i class="fas fa-eye"></i> View Uploads
            </a>
            <a href="faculty_research_analytics.php">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="logout.php" style="color: #ff4757;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="sidebar-footer">
            <p>© <?php echo date('Y'); ?> Career System</p>
            <p>Research Portal v2.0</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-cloud-upload-alt"></i> Upload Research Document</h1>
            <p>Upload your research papers, publications, and academic documents with AI-powered categorization</p>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-item">
                <div class="stat-value">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="stat-label">AI-Powered Classification</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="stat-label">Fast Processing</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-label">Secure Upload</div>
            </div>
        </div>

        <!-- Upload Form -->
        <form action="save_research_upload.php" method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
            <div class="form-header">
                <h2><i class="fas fa-file-upload"></i> Document Details</h2>
                <p>Fill in the details and upload your research document</p>
            </div>

            <!-- Document Title -->
            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> Title of Document</label>
                <input type="text" name="title" id="title" placeholder="Enter the title of your research document" required>
            </div>

            <!-- Category Selection -->
            <div class="form-group">
                <label><i class="fas fa-tags"></i> Select Category</label>
                <div class="category-options" id="categoryOptions">
                    <div class="category-option" data-value="Journal Paper">
                        <div class="category-icon icon-paper">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <span class="category-label">Journal Paper</span>
                    </div>
                    <div class="category-option" data-value="Conference Paper">
                        <div class="category-icon icon-conference">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="category-label">Conference Paper</span>
                    </div>
                    <div class="category-option" data-value="Book Chapter">
                        <div class="category-icon icon-book">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="category-label">Book Chapter</span>
                    </div>
                    <div class="category-option" data-value="Research Proposal">
                        <div class="category-icon icon-project">
                            <i class="fas fa-flask"></i>
                        </div>
                        <span class="category-label">Research Proposal</span>
                    </div>
                    <div class="category-option" data-value="Patent">
                        <div class="category-icon icon-patent">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <span class="category-label">Patent</span>
                    </div>
                </div>
                <input type="hidden" name="category" id="selectedCategory" required>
            </div>

            <!-- File Upload -->
            <div class="form-group">
                <label><i class="fas fa-file-upload"></i> Upload File</label>
                <div class="file-upload-area" id="fileUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div class="file-upload-text">Choose a file or drag & drop here</div>
                    <div class="file-upload-subtext">PDF, JPG, PNG up to 10MB</div>
                    <input type="file" name="file" id="fileInput" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="file-preview" id="filePreview">
                    <div class="file-info">
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="file-details">
                            <div class="file-name" id="fileName">No file selected</div>
                            <div class="file-size" id="fileSize">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Features -->
            <div class="ai-features">
                <h3 style="margin-bottom: 15px; font-size: 1.2rem; opacity: 0.9;">
                    <i class="fas fa-robot"></i> AI-Powered Features
                </h3>
                <div class="ai-badge">
                    <i class="fas fa-magic"></i> Automatic Text Extraction & Classification
                </div>
                <p style="margin-top: 15px; opacity: 0.9; font-size: 0.95rem;">
                    Your document will be automatically analyzed and categorized using AI algorithms
                </p>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-upload"></i> Upload Document
            </button>
        </form>

        <!-- Upload Guidelines -->
        <div class="upload-guidelines">
            <h3 class="guidelines-header">
                <i class="fas fa-clipboard-check"></i> Upload Guidelines
            </h3>
            <ul class="guidelines-list">
                <li>Only PDF, JPG, JPEG, and PNG files are allowed</li>
                <li>Maximum file size: 10MB per document</li>
                <li>Ensure documents are clear and readable</li>
                <li>Use descriptive titles for better categorization</li>
                <li>AI will automatically extract text and categorize your document</li>
                <li>Documents will be securely stored and accessible anytime</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Research Upload Portal</p>
            <p>Secure AI-powered document processing system</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category selection
            const categoryOptions = document.querySelectorAll('.category-option');
            const selectedCategoryInput = document.getElementById('selectedCategory');
            
            categoryOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    categoryOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Set hidden input value
                    const value = this.getAttribute('data-value');
                    selectedCategoryInput.value = value;
                    
                    // Add animation
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            });

            // File upload preview
            const fileInput = document.getElementById('fileInput');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            
            fileUploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    
                    // Update preview
                    fileName.textContent = file.name;
                    fileSize.textContent = `${fileSizeMB} MB`;
                    
                    // Update icon based on file type
                    const fileIcon = filePreview.querySelector('.file-icon i');
                    if (file.type.includes('pdf')) {
                        fileIcon.className = 'fas fa-file-pdf';
                        fileIcon.parentElement.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                    } else if (file.type.includes('image')) {
                        fileIcon.className = 'fas fa-file-image';
                        fileIcon.parentElement.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
                    }
                    
                    // Show preview
                    filePreview.classList.add('active');
                    
                    // Add success styling to upload area
                    fileUploadArea.style.borderColor = '#2ecc71';
                    fileUploadArea.style.background = 'rgba(46, 204, 113, 0.1)';
                    
                    // Check file size
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size exceeds 10MB limit. Please choose a smaller file.');
                        this.value = '';
                        filePreview.classList.remove('active');
                        fileUploadArea.style.borderColor = 'var(--primary-color)';
                        fileUploadArea.style.background = 'rgba(106, 17, 203, 0.05)';
                    }
                }
            });

            // Drag and drop functionality
            fileUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.borderColor = 'var(--secondary-color)';
                this.style.background = 'rgba(37, 117, 252, 0.1)';
                this.style.transform = 'scale(1.02)';
            });

            fileUploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.style.borderColor = 'var(--primary-color)';
                this.style.background = 'rgba(106, 17, 203, 0.05)';
                this.style.transform = 'scale(1)';
            });

            fileUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderColor = 'var(--primary-color)';
                this.style.background = 'rgba(106, 17, 203, 0.05)';
                this.style.transform = 'scale(1)';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });

            // Form validation
            const form = document.getElementById('uploadForm');
            const submitBtn = document.getElementById('submitBtn');
            
            form.addEventListener('submit', function(e) {
                // Validate category selection
                if (!selectedCategoryInput.value) {
                    e.preventDefault();
                    alert('Please select a category for your document.');
                    return;
                }
                
                // Validate file
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Please select a file to upload.');
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                
                // Form will submit normally
            });

            // Auto-select first category
            if (categoryOptions.length > 0) {
                categoryOptions[0].click();
            }

            // Auto-scroll to top
            window.scrollTo(0, 0);
        });
    </script>
</body>
</html>