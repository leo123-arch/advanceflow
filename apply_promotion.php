<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch user details
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Promotion | Career Advancement System</title>
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
            --accent-color: #ff416c;
            --success-color: #2ed573;
            --light-bg: #f8f9fa;
            --dark-text: #2d3436;
            --gray-text: #636e72;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
            color: var(--dark-text);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease;
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
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Main Form */
        .main-form {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .main-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        /* User Info Badge */
        .user-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .user-info i {
            font-size: 1.5rem;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-dept {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Form Styles */
        .form-box {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group label i {
            color: var(--primary-color);
            width: 20px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 15px 20px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .form-group input:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.8;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* File Upload Styling */
        .file-upload-container {
            position: relative;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .file-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            background-color: rgba(106, 17, 203, 0.05);
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input:hover {
            background-color: rgba(106, 17, 203, 0.1);
        }

        .file-info {
            margin-top: 8px;
            font-size: 0.9rem;
            color: var(--gray-text);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Select Dropdown Styling */
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236a11cb' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 16px;
            padding-right: 50px;
        }

        /* Submit Button */
        .btn {
            padding: 18px 35px;
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

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn i {
            font-size: 1.2rem;
        }

        /* Back Button */
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

        /* Requirements Section */
        .requirements {
            background: var(--light-bg);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border-left: 4px solid var(--accent-color);
        }

        .requirements h3 {
            font-size: 1.3rem;
            color: var(--dark-text);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .requirements h3 i {
            color: var(--accent-color);
        }

        .requirements ul {
            list-style: none;
            padding-left: 0;
        }

        .requirements li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
            color: var(--gray-text);
        }

        .requirements li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success-color);
            font-weight: bold;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
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
            
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2.2rem;
            }
            
            .main-form {
                padding: 25px;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .btn {
                width: 100%;
                padding: 16px;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group {
            animation: slideIn 0.5s ease;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
        .form-group:nth-child(7) { animation-delay: 0.7s; }
        .form-group:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-arrow-up"></i> Apply for Promotion</h1>
            <p>Submit your application for academic promotion with supporting documents</p>
        </div>

        <!-- Main Form -->
        <div class="main-form">
            <!-- User Info -->
            <div class="user-info">
                <i class="fas fa-user-graduate"></i>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="user-dept"><?php echo htmlspecialchars($user['department']); ?> Department</div>
                </div>
            </div>

            <!-- Promotion Application Form -->
            <form action="process_promotion.php" method="POST" enctype="multipart/form-data" class="form-box">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Faculty Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-building"></i> Department</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['department']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Current Position</label>
                    <select name="current_position" required>
                        <option value="">Select Current Position</option>
                        <option value="Assistant Professor">Assistant Professor</option>
                        <option value="Associate Professor">Associate Professor</option>
                        <option value="Professor">Professor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-target"></i> Applying For</label>
                    <select name="promotion_to" required>
                        <option value="">Select Position to Apply For</option>
                        <option value="Associate Professor">Associate Professor</option>
                        <option value="Professor">Professor</option>
                        <option value="Senior Professor">Senior Professor</option>
                    </select>
                </div>

                <div class="form-group file-upload-container">
                    <label class="file-upload-label"><i class="fas fa-file-pdf"></i> Upload Supporting Documents</label>
                    <input type="file" name="docs" accept="application/pdf" class="file-input" required>
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i> PDF format only, max size 10MB
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Remarks (Optional)</label>
                    <textarea name="remarks" placeholder="Provide any additional information or notes for the administration review committee..."></textarea>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </form>

            <!-- Requirements Section -->
            <div class="requirements">
                <h3><i class="fas fa-clipboard-check"></i> Application Requirements</h3>
                <ul>
                    <li>All fields are mandatory except remarks</li>
                    <li>Only PDF documents are accepted for upload</li>
                    <li>Maximum file size: 10MB</li>
                    <li>Include all relevant certificates and achievements</li>
                    <li>Applications will be reviewed within 15 working days</li>
                </ul>
            </div>

            <!-- Back Button -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="faculty_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Promotion Application Portal</p>
            <p>Application submitted on: <?php echo date('F j, Y, h:i A'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
            // File input styling
            const fileInput = document.querySelector('input[type="file"]');
            fileInput.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    const fileName = this.files[0].name;
                    const fileSize = (this.files[0].size / (1024 * 1024)).toFixed(2); // Convert to MB
                    
                    // Update file info display
                    const fileInfo = document.querySelector('.file-info');
                    fileInfo.innerHTML = `<i class="fas fa-check-circle" style="color: #2ed573;"></i> Selected: ${fileName} (${fileSize} MB)`;
                    
                    // Add success styling to file input
                    this.style.borderColor = '#2ed573';
                    this.style.backgroundColor = 'rgba(46, 213, 115, 0.1)';
                }
            });
            
            // Form validation
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function(e) {
                // Check if file is PDF
                const fileInput = this.querySelector('input[type="file"]');
                if (fileInput.files.length > 0) {
                    const fileName = fileInput.files[0].name.toLowerCase();
                    if (!fileName.endsWith('.pdf')) {
                        e.preventDefault();
                        alert('Please upload only PDF files.');
                        return;
                    }
                    
                    // Check file size (10MB limit)
                    const fileSize = fileInput.files[0].size;
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    
                    if (fileSize > maxSize) {
                        e.preventDefault();
                        alert('File size exceeds 10MB limit. Please upload a smaller file.');
                        return;
                    }
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Application...';
            });
            
            // Select dropdown enhancement
            const selectElements = document.querySelectorAll('select');
            selectElements.forEach(select => {
                select.addEventListener('focus', function() {
                    this.style.backgroundColor = 'white';
                    this.style.boxShadow = '0 0 0 3px rgba(106, 17, 203, 0.1)';
                });
                
                select.addEventListener('blur', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>