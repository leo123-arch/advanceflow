<?php
session_start();
<<<<<<< HEAD
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Advanced API Score Calculator</title>
    <link rel="stylesheet" href="./css/advanced_api_form.css">

</head>
<body>

<div class="main">
    <h1>Advanced API Score (UGC Format)</h1>

    <form action="calculate_advanced_api.php" method="POST" class="form-box">

        <h2>Category I: Teaching Activities</h2>

        <label>Teaching Hours per Week</label>
        <input type="number" name="teaching_hours" required>

        <label>Student Feedback Score (0-10)</label>
        <input type="number" name="feedback" min="0" max="10" required>

        <label>Lesson Planning / Mentorship</label>
        <input type="number" name="mentorship" min="0" required>


        <h2>Category II: Co-Curricular Activities</h2>

        <label>Extension Activities (NSS/NCC/Clubs)</label>
        <input type="number" name="extension" required>

        <label>Professional Development Programs Attended</label>
        <input type="number" name="pdp" required>

        <label>Administrative Responsibilities (Coordinator/Convener)</label>
        <input type="number" name="admin" required>


        <h2>Category III: Research & Academic Contributions</h2>

        <label>Research Papers Published</label>
        <input type="number" name="papers" required>

        <label>Books/Chapters Published</label>
        <input type="number" name="books" required>

        <label>Conference Presentations</label>
        <input type="number" name="conference" required>

        <label>Patents Filed/Granted</label>
        <input type="number" name="patents" required>

        <label>Research Projects Completed</label>
        <input type="number" name="projects" required>

        <button type="submit" class="btn">Calculate API Score</button>
    </form>
</div>

</body>
</html>
=======
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details for personalization
$query = mysqli_query($conn, "SELECT name FROM faculty WHERE id='$faculty_id'");
$user = mysqli_fetch_assoc($query);
$faculty_name = $user['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced API Score Calculator | Career Advancement System</title>
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
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header p {
            color: var(--text-gray);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .user-info {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-top: 15px;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.2);
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            counter-reset: step;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: var(--border-color);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-icon {
            width: 35px;
            height: 35px;
            background: white;
            border: 3px solid var(--border-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 600;
            color: var(--text-gray);
            transition: all 0.3s;
        }

        .step.active .step-icon {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .step.completed .step-icon {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }

        .step-label {
            font-size: 0.85rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        /* Form Container */
        .form-container {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            animation: slideUp 0.6s ease;
            margin-bottom: 30px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Category Styles */
        .category {
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 30px;
        }

        .category:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .category-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background: linear-gradient(to right, rgba(106, 17, 203, 0.05), rgba(37, 117, 252, 0.05));
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
        }

        .category-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.3rem;
        }

        .category-title {
            font-size: 1.4rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .category-subtitle {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-top: 3px;
        }

        /* Input Grid */
        .input-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .input-group {
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .input-group .required {
            color: var(--danger-color);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: var(--light-bg);
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .input-hint {
            font-size: 0.8rem;
            color: var(--text-gray);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-hint i {
            color: var(--warning-color);
        }

        .max-label {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            font-size: 0.85rem;
        }

        /* Button Styles */
        .button-container {
            text-align: center;
            margin-top: 40px;
        }

        .btn-calculate {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(106, 17, 203, 0.25);
        }

        .btn-calculate:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(106, 17, 203, 0.35);
        }

        .btn-calculate:active {
            transform: translateY(-1px);
        }

        /* Info Box */
        .info-box {
            background: linear-gradient(to right, #e3f2fd, #f3e5f5);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border-left: 5px solid var(--secondary-color);
        }

        .info-box h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box p {
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .info-box ul {
            padding-left: 20px;
            margin-top: 10px;
        }

        .info-box li {
            margin-bottom: 8px;
            color: var(--text-gray);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text-gray);
            font-size: 0.9rem;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .input-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .progress-steps {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .step {
                flex: none;
                width: calc(33.333% - 10px);
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 20px 15px;
            }
            
            .category-header {
                flex-direction: column;
                text-align: center;
            }
            
            .category-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .step {
                width: 100%;
            }
            
            .progress-steps::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-calculator"></i> Advanced API Score Calculator</h1>
            <p>UGC Format - Calculate your Academic Performance Indicator score based on teaching, research, and co-curricular activities</p>
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Faculty: <?php echo htmlspecialchars($faculty_name); ?>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active">
                <div class="step-icon">1</div>
                <div class="step-label">Enter Data</div>
            </div>
            <div class="step">
                <div class="step-icon">2</div>
                <div class="step-label">Calculate</div>
            </div>
            <div class="step">
                <div class="step-icon">3</div>
                <div class="step-label">Results</div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <form action="calculate_advanced_api.php" method="POST" id="apiForm">
                
                <!-- Category I: Teaching Activities -->
                <div class="category">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div>
                            <div class="category-title">Category I: Teaching & Learning Activities</div>
                            <div class="category-subtitle">Academic duties, student interaction, and instructional quality</div>
                        </div>
                    </div>
                    
                    <div class="input-grid">
                        <div class="input-group">
                            <label>Teaching Hours per Week <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-clock input-icon"></i>
                                <input type="number" name="teaching_hours" required min="0" max="40" 
                                       placeholder="e.g., 16" value="16">
                                <span class="max-label">Max: 40</span>
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Standard: 16 hours/week for Assistant Professor
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Student Feedback Score <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-star input-icon"></i>
                                <input type="number" name="feedback" required min="0" max="10" 
                                       placeholder="0-10" value="8" step="0.5">
                                <span class="max-label">/10</span>
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Scale: 0 (Poor) to 10 (Excellent)
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Mentorship & Guidance <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-hands-helping input-icon"></i>
                                <input type="number" name="mentorship" required min="0" 
                                       placeholder="e.g., 5" value="5">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Number of students mentored/projects guided
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Category II: Co-Curricular Activities -->
                <div class="category">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="category-title">Category II: Co-curricular & Extension Activities</div>
                            <div class="category-subtitle">Beyond classroom contributions and administrative roles</div>
                        </div>
                    </div>
                    
                    <div class="input-grid">
                        <div class="input-group">
                            <label>Extension Activities <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-hands input-icon"></i>
                                <input type="number" name="extension" required min="0" 
                                       placeholder="e.g., 3" value="2">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> NSS/NCC/Club activities organized
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Professional Development <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-graduation-cap input-icon"></i>
                                <input type="number" name="pdp" required min="0" 
                                       placeholder="e.g., 4" value="3">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Workshops/FDPs/Conferences attended
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Administrative Roles <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-tasks input-icon"></i>
                                <input type="number" name="admin" required min="0" 
                                       placeholder="e.g., 2" value="1">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Coordinator/Convener/Committee roles
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Category III: Research & Academic Contributions -->
                <div class="category">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div>
                            <div class="category-title">Category III: Research & Academic Contributions</div>
                            <div class="category-subtitle">Publications, patents, projects, and academic output</div>
                        </div>
                    </div>
                    
                    <div class="input-grid">
                        <div class="input-group">
                            <label>Research Papers Published <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-newspaper input-icon"></i>
                                <input type="number" name="papers" required min="0" 
                                       placeholder="e.g., 5" value="3">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Scopus/Web of Science/Peer-reviewed journals
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Books/Chapters Published <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-book input-icon"></i>
                                <input type="number" name="books" required min="0" 
                                       placeholder="e.g., 2" value="1">
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Conference Presentations <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-microphone input-icon"></i>
                                <input type="number" name="conference" required min="0" 
                                       placeholder="e.g., 4" value="2">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> National/International conferences
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Patents Filed/Granted <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-certificate input-icon"></i>
                                <input type="number" name="patents" required min="0" 
                                       placeholder="e.g., 1" value="0">
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Research Projects <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <i class="fas fa-project-diagram input-icon"></i>
                                <input type="number" name="projects" required min="0" 
                                       placeholder="e.g., 2" value="1">
                            </div>
                            <div class="input-hint">
                                <i class="fas fa-info-circle"></i> Funded research projects completed
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <h3><i class="fas fa-lightbulb"></i> Important Notes</h3>
                    <p>The API score is calculated based on UGC guidelines with the following weightage:</p>
                    <ul>
                        <li><strong>Category I (Teaching):</strong> 50% weightage</li>
                        <li><strong>Category II (Co-curricular):</strong> 30% weightage</li>
                        <li><strong>Category III (Research):</strong> 20% weightage</li>
                    </ul>
                    <p>Minimum required API score for promotion: <strong>75 points</strong></p>
                </div>
                
                <!-- Submit Button -->
                <div class="button-container">
                    <button type="submit" class="btn-calculate">
                        <i class="fas fa-calculator"></i>
                        Calculate My API Score
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | API Calculator v2.0</p>
            <p>Based on UGC Regulations for Promotion of Teachers</p>
        </div>
    </div>

    <script>
        // Form validation and interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('apiForm');
            const inputs = form.querySelectorAll('input[type="number"]');
            
            // Add input validation
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const min = parseInt(this.getAttribute('min')) || 0;
                    const max = parseInt(this.getAttribute('max')) || Infinity;
                    let value = parseInt(this.value) || 0;
                    
                    if (value < min) this.value = min;
                    if (value > max) this.value = max;
                    
                    // Update progress steps based on completion
                    updateProgress();
                });
                
                // Add focus effect
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Progress tracking
            function updateProgress() {
                const steps = document.querySelectorAll('.step');
                let completed = 0;
                
                // Check if Category I is completed
                const category1Inputs = Array.from(inputs).slice(0, 3);
                const cat1Filled = category1Inputs.filter(input => input.value.trim() !== '').length;
                if (cat1Filled === 3) completed++;
                
                // Check if Category II is completed
                const category2Inputs = Array.from(inputs).slice(3, 6);
                const cat2Filled = category2Inputs.filter(input => input.value.trim() !== '').length;
                if (cat2Filled === 3) completed++;
                
                // Update step indicators
                steps.forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    if (index < completed) {
                        step.classList.add('completed');
                    } else if (index === completed) {
                        step.classList.add('active');
                    }
                });
            }
            
            // Initialize progress
            updateProgress();
            
            // Form submission with validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const errorMessages = [];
                
                inputs.forEach(input => {
                    if (!input.value.trim() || parseInt(input.value) < 0) {
                        isValid = false;
                        input.style.borderColor = 'var(--danger-color)';
                        errorMessages.push(`Please enter a valid value for ${input.previousElementSibling?.textContent || 'this field'}`);
                    } else {
                        input.style.borderColor = '';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill all fields with valid values:\n\n' + errorMessages.join('\n'));
                } else {
                    // Show loading state
                    const submitBtn = form.querySelector('.btn-calculate');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 2 seconds if form doesn't submit
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 2000);
                }
            });
            
            // Auto-save form data (optional)
            function saveFormData() {
                const formData = {};
                inputs.forEach(input => {
                    formData[input.name] = input.value;
                });
                localStorage.setItem('apiFormData', JSON.stringify(formData));
            }
            
            // Load saved form data
            const savedData = localStorage.getItem('apiFormData');
            if (savedData) {
                const formData = JSON.parse(savedData);
                inputs.forEach(input => {
                    if (formData[input.name]) {
                        input.value = formData[input.name];
                    }
                });
                updateProgress();
            }
            
            // Auto-save on input change
            inputs.forEach(input => {
                input.addEventListener('change', saveFormData);
            });
        });
    </script>
</body>
</html>
>>>>>>> 90e527b (Initial commit)
