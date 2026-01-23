<<<<<<< HEAD
<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Career System</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<div class="container">

    <form class="login-box" method="POST">
        <h2>Faculty Registration</h2>

        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group">
            <label>Qualification</label>
            <input type="text" name="qualification" required>
        </div>

        <div class="input-group">
            <label>Experience (years)</label>
            <input type="number" name="experience" required>
        </div>

        <div class="input-group">
            <label>Role</label>
            <input type="text" name="role" required>
        </div>

          <div class="input-group">
            <label>Department</label>
            <input type="text" name="department" required>
        </div>

        <button type="submit" name="register" class="btn">Register</button>

        <p class="note">Already have an account?
            <a href="login.php">Login</a>
        </p>
    </form>

</div>

<?php
if(isset($_POST['register'])){
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // You can encrypt later
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $role = $_POST['role'];
    $department = $_POST['department'];
=======
<?php 
include "config.php"; 
session_start();

// Handle registration before any HTML output
if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    
    // Preserve form data in case of error
    $form_data = compact('name', 'email', 'qualification', 'experience', 'role', 'department');
>>>>>>> 90e527b (Initial commit)

    // Check if email exists
    $check = mysqli_query($conn, "SELECT * FROM faculty WHERE email='$email'");
    
    if(mysqli_num_rows($check) > 0){
<<<<<<< HEAD
        echo "<script>alert('Email already registered!');</script>";
    } else {
        // Insert into database
        $query = "INSERT INTO faculty (name, email, password, qualification, experience,role,department)
                  VALUES ('$name', '$email', '$password', '$qualification', '$experience','$role','$department')";

        if(mysqli_query($conn, $query)){
            echo "<script>alert('Registration Successful!'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error! Please try again.');</script>";
=======
        $registration_error = "Email already registered!";
    } else {
        // Insert into database
        $query = "INSERT INTO faculty (name, email, password, qualification, experience, role, department)
                  VALUES ('$name', '$email', '$password', '$qualification', '$experience', '$role', '$department')";

        if(mysqli_query($conn, $query)){
            $registration_success = "Registration Successful! Redirecting to login...";
            
            // JavaScript redirect after success
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                  </script>";
        } else {
            $registration_error = "Error! Please try again. " . mysqli_error($conn);
>>>>>>> 90e527b (Initial commit)
        }
    }
}
?>

<<<<<<< HEAD
</body>
</html>
=======
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Career Advancement System</title>
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
            background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-box {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .register-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #2575fc, #6a11cb);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .register-header p {
            color: #666;
            font-size: 15px;
        }

        .input-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
            flex: 1;
        }

        .input-group.full-width {
            flex: none;
            width: 100%;
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

        .input-group input, .input-group select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: #6a11cb;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 40px;
            color: #6a11cb;
            font-size: 18px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
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
            box-shadow: 0 7px 15px rgba(106, 17, 203, 0.3);
        }

        .btn1 {
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

        .btn1:hover {
            background-color: rgba(37, 117, 252, 0.1);
            transform: translateY(-2px);
        }

        .note {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 15px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .note a {
            color: #2575fc;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .note a:hover {
            color: #6a11cb;
            text-decoration: underline;
        }

        .faculty-icon {
            text-align: center;
            margin-bottom: 20px;
        }

        .faculty-icon i {
            font-size: 50px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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

        @media (max-width: 768px) {
            .input-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-box {
                padding: 30px 25px;
            }
        }

        @media (max-width: 480px) {
            .register-box {
                padding: 25px 20px;
            }
            
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <form class="register-box" method="POST" action="">
        <div class="faculty-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        
        <div class="register-header">
            <h2>Faculty Registration</h2>
            <p>Join the Career Advancement & Development System</p>
        </div>

        <?php if(isset($registration_error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($registration_error); ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($registration_success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($registration_success); ?></span>
            </div>
        <?php endif; ?>

        <div class="input-row">
            <div class="input-group">
                <label>Full Name <span class="required">*</span></label>
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="Enter your full name" required 
                       value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
            </div>

            <div class="input-group">
                <label>Email <span class="required">*</span></label>
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required
                       value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
            </div>
        </div>

        <div class="input-row">
            <div class="input-group">
                <label>Password <span class="required">*</span></label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Create a password" required 
                       oninput="checkPasswordStrength()">
                <div class="password-strength">
                    <div class="strength-bar" id="strength-bar"></div>
                </div>
            </div>

            <div class="input-group">
                <label>Qualification <span class="required">*</span></label>
                <i class="fas fa-graduation-cap"></i>
                <input type="text" name="qualification" placeholder="e.g., Ph.D., M.Tech, etc." required
                       value="<?php echo isset($form_data['qualification']) ? htmlspecialchars($form_data['qualification']) : ''; ?>">
            </div>
        </div>

        <div class="input-row">
            <div class="input-group">
                <label>Experience (years) <span class="required">*</span></label>
                <i class="fas fa-briefcase"></i>
                <input type="number" name="experience" min="0" max="50" placeholder="0" required
                       value="<?php echo isset($form_data['experience']) ? htmlspecialchars($form_data['experience']) : ''; ?>">
            </div>

            <div class="input-group">
                <label>Department <span class="required">*</span></label>
                <i class="fas fa-building"></i>
                <input type="text" name="department" placeholder="e.g., Computer Science" required
                       value="<?php echo isset($form_data['department']) ? htmlspecialchars($form_data['department']) : ''; ?>">
            </div>
        </div>

        <div class="input-group full-width">
            <label>Role <span class="required">*</span></label>
            <i class="fas fa-user-tag"></i>
            <select name="role" required>
                <option value="">Select your role</option>
                <option value="faculty" <?php echo (isset($form_data['role']) && $form_data['role'] == 'faculty') ? 'selected' : ''; ?>>Faculty Member</option>
                <option value="admin" <?php echo (isset($form_data['role']) && $form_data['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                <option value="hod" <?php echo (isset($form_data['role']) && $form_data['role'] == 'hod') ? 'selected' : ''; ?>>Head of Department</option>
                <option value="coordinator" <?php echo (isset($form_data['role']) && $form_data['role'] == 'coordinator') ? 'selected' : ''; ?>>Program Coordinator</option>
            </select>
        </div>

        <button type="submit" name="register" class="btn">
            <i class="fas fa-user-plus"></i> Register Now
        </button>

        <p class="note">
            Already have an account? 
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login here</a>
        </p>
    </form>
</div>

<script>
    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
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
        const inputs = document.querySelectorAll('input, select');
        const alerts = document.querySelectorAll('.alert');
        
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                alerts.forEach(alert => {
                    alert.style.display = 'none';
                });
            });
        });
        
        // Set default experience to 0 if empty
        const experienceInput = document.querySelector('input[name="experience"]');
        if (experienceInput && !experienceInput.value) {
            experienceInput.value = '0';
        }
    });
</script>

</body>
</html>
>>>>>>> 90e527b (Initial commit)
