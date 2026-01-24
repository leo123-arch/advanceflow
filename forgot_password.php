<?php 
include "config.php"; 
session_start();

// Preserve email in case of error
$form_email = '';
$success_message = '';
$error_message = '';

// Handle forgot password before any HTML output
if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $newpass = mysqli_real_escape_string($conn, $_POST['newpass']);
    
    // Preserve email in case of error
    $form_email = $email;

    // Check if faculty exists
    $q = mysqli_query($conn, "SELECT * FROM faculty WHERE email='$email'");

    if(mysqli_num_rows($q) == 1){
        $row = mysqli_fetch_assoc($q);
        $faculty_id = $row['id'];
        $faculty_name = $row['name'];

        // Insert request
        $insert = "INSERT INTO password_requests (faculty_id, new_password, request_date)
                   VALUES ('$faculty_id', '$newpass', NOW())";

        if(mysqli_query($conn, $insert)){
            $success_message = "Password change request submitted! Wait for admin approval.";
        } else {
            $error_message = "Error submitting request. Please try again.";
        }
    } 
    else {
        $error_message = "Email not found in our records!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Career Advancement System</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .forgot-box {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .forgot-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #2575fc, #6a11cb);
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .forgot-header p {
            color: #666;
            font-size: 15px;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .input-group label .required {
            color: #ff4757;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .input-group input:focus {
            outline: none;
            border-color: #ff416c;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(255, 65, 108, 0.1);
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 40px;
            color: #ff416c;
            font-size: 18px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(255, 65, 108, 0.3);
        }

        .btn-secondary {
            display: block;
            width: 100%;
            padding: 16px;
            background: transparent;
            color: #2575fc;
            border: 2px solid #2575fc;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .btn-secondary:hover {
            background-color: rgba(37, 117, 252, 0.1);
            transform: translateY(-2px);
        }

        .info-box {
            background-color: #f0f8ff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #2575fc;
        }

        .info-box h4 {
            color: #2575fc;
            margin-bottom: 8px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box p {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background-color: #ffeaea;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .alert-success {
            background-color: #e7f7ef;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .alert i {
            font-size: 20px;
        }

        .password-strength {
            margin-top: 5px;
            height: 4px;
            border-radius: 2px;
            background: #eee;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
            background: #ff4757;
        }

        .password-hints {
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }

        .password-hints ul {
            padding-left: 20px;
            margin-top: 5px;
        }

        .password-hints li {
            margin-bottom: 3px;
        }

        .faculty-icon {
            text-align: center;
            margin-bottom: 20px;
        }

        .faculty-icon i {
            font-size: 50px;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .back-link a {
            color: #2575fc;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #6a11cb;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .forgot-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <form class="forgot-box" method="POST" action="">
        <div class="faculty-icon">
            <i class="fas fa-key"></i>
        </div>
        
        <div class="forgot-header">
            <h2>Reset Your Password</h2>
            <p>Enter your email and choose a new password for your account</p>
        </div>

        <div class="info-box">
            <h4><i class="fas fa-info-circle"></i> Important Note</h4>
            <p>Password changes require administrator approval for security purposes. You'll be notified once your request is approved.</p>
        </div>

        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="input-group">
            <label>Email Address <span class="required">*</span></label>
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Enter your registered email" required 
                   value="<?php echo isset($form_email) ? htmlspecialchars($form_email) : ''; ?>">
        </div>

        <div class="input-group">
            <label>New Password <span class="required">*</span></label>
            <i class="fas fa-lock"></i>
            <input type="password" name="newpass" id="newpass" placeholder="Enter your new password" required 
                   oninput="checkPasswordStrength()">
            <div class="password-strength">
                <div class="strength-bar" id="strength-bar"></div>
            </div>
            <div class="password-hints">
                <strong>Password requirements:</strong>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Include at least one number</li>
                    <li>Special characters recommended</li>
                </ul>
            </div>
        </div>

        <button type="submit" name="submit" class="btn">
            <i class="fas fa-paper-plane"></i> Submit Request
        </button>

        <a href="login.php" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>

        <div class="back-link">
            <p>Remember your password? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<script>
    function checkPasswordStrength() {
        const password = document.getElementById('newpass').value;
        const strengthBar = document.getElementById('strength-bar');
        
        let strength = 0;
        let color = '#ff4757'; // Red
        
        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
        
        if (strength >= 75) {
            color = '#2ed573'; // Green
        } else if (strength >= 50) {
            color = '#ffa502'; // Orange
        } else if (strength >= 25) {
            color = '#ff7f00'; // Dark Orange
        }
        
        strengthBar.style.width = strength + '%';
        strengthBar.style.backgroundColor = color;
    }
    
    // Clear alerts on input focus
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input');
        const alerts = document.querySelectorAll('.alert');
        
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                alerts.forEach(alert => {
                    if(alert.classList.contains('alert-error')) {
                        alert.style.display = 'none';
                    }
                });
            });
        });
        
        // Auto-hide success message after 5 seconds
        const successAlert = document.querySelector('.alert-success');
        if(successAlert) {
            setTimeout(function() {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    successAlert.style.display = 'none';
                }, 500);
            }, 5000);
        }
    });
</script>

</body>
</html>