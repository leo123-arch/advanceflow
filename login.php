<?php
// ================== PHP LOGIC (MUST BE AT TOP) ==================
session_start();
include "config.php";

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM faculty WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['faculty_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['faculty_name'] = $row['name'];

        if ($row['role'] === "admin") {
            header("Location: ./admin/admin_dashboard.php");
            exit();
        } else {
            header("Location: faculty_dashboard.php");
            exit();
        }
    } else {
        $error = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Career Advancement System</title>
    <!-- <link rel="stylesheet" href="./css/login.css"> -->
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

        .login-box {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(to right, #2575fc, #6a11cb);
            -webkit-background-clip: text;
            color: transparent;
        }

        .login-header p {
            color: #666;
            font-size: 15px;
        }

        .faculty-icon {
            text-align: center;
            margin-bottom: 15px;
        }

        .faculty-icon i {
            font-size: 50px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            -webkit-background-clip: text;
            color: transparent;
        }

        .input-group {
            margin-bottom: 22px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-weight: 600;
        }

        .input-group i {
            position: absolute;
            top: 38px;
            left: 15px;
            color: #6a11cb;
        }

        .input-group input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid #e1e5ee;
            border-radius: 10px;
            font-size: 15px;
        }

        .input-group input:focus {
            outline: none;
            border-color: #6a11cb;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        }

        .btn1 {
            display: block;
            margin-top: 15px;
            padding: 14px;
            border: 2px solid #2575fc;
            border-radius: 10px;
            text-align: center;
            color: #2575fc;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn1:hover {
            background-color: #2575fc;
            color: white;
        }

        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }

        .links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #2575fc;
        }

        .alert {
            background: #ffeaea;
            color: #d32f2f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #d32f2f;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 18px;
        }

        .note {
            margin-top: 15px;
            font-size: 14px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid #2575fc;
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <form class="login-box" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

        <div class="faculty-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>

        <div class="login-header">
            <h2>Faculty Login</h2>
            <p>Career Advancement & Development System</p>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <div class="input-group">
            <label for="email">Email Address</label>
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required placeholder="Enter your email address">
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit" name="login" class="btn">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>

        <a href="register.php" class="btn1">
            <i class="fas fa-user-plus"></i> Create New Account
        </a>

        <div class="links">
            <a href="forgot_password.php">
                <i class="fas fa-key"></i> Forgot Password?
            </a>
            <a href="#" onclick="toggleAdminNote()">
                <i class="fas fa-user-shield"></i> Admin Login Info
            </a>
        </div>

        <div class="note" id="admin-note">
            <i class="fas fa-info-circle"></i> Admin users can login using the same form with admin credentials.
        </div>

    </form>
</div>

<script>
function toggleAdminNote() {
    let note = document.getElementById("admin-note");
    if (note.style.display === "block") {
        note.style.display = "none";
    } else {
        note.style.display = "block";
    }
    return false;
}

// Add show password functionality
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const passwordGroup = document.querySelector('.input-group:nth-child(2)');
    
    // Add show password button
    const showPassBtn = document.createElement('span');
    showPassBtn.innerHTML = '<i class="fas fa-eye"></i>';
    showPassBtn.style.position = 'absolute';
    showPassBtn.style.right = '15px';
    showPassBtn.style.top = '38px';
    showPassBtn.style.cursor = 'pointer';
    showPassBtn.style.color = '#666';
    showPassBtn.title = 'Show Password';
    
    showPassBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
    
    passwordGroup.appendChild(showPassBtn);
});
</script>

</body>
</html>