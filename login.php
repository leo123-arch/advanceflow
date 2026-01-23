<<<<<<< HEAD
<?php include "config.php"; session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Career System</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<div class="container">

    <form class="login-box" method="POST">
        <h2>Faculty Login</h2>

        <div class="input-group">
            <label>Email</label>
            <input type="text" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" name="login" class="btn">Login</button>

        <a href="register.php" class="btn1">Register</a>

    <p class="note">
    <a href="forgot_password.php">Forgot Password?</a>
    </p>

        <p class="note">Admin can also login here</p>
    </form>

</div>

<?php
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
=======
<?php 
include "config.php"; 
session_start();

// Handle login before any HTML output
if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
>>>>>>> 90e527b (Initial commit)

    $query = "SELECT * FROM faculty WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1){
        $row = mysqli_fetch_assoc($result);

        $_SESSION['faculty_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];

        if($row['role'] == "admin"){
            header("Location: ./admin/admin_dashboard.php");
<<<<<<< HEAD
        } else {
            header("Location: faculty_dashboard.php");
        }
    } else {
        echo "<script>alert('Invalid Email or Password!');</script>";
=======
            exit();
        } else {
            header("Location: faculty_dashboard.php");
            exit();
        }
    } else {
        $login_error = "Invalid email or password. Please try again.";
>>>>>>> 90e527b (Initial commit)
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
    <title>Login | Career Advancement System</title>
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

        .login-box {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
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
            margin-bottom: 35px;
        }

        .login-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #2575fc, #6a11cb);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .login-header p {
            color: #666;
            font-size: 15px;
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
            transition: color 0.3s;
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

        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 14px;
        }

        .links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }

        .links a:hover {
            color: #6a11cb;
        }

        .links a i {
            margin-right: 6px;
            font-size: 12px;
        }

        .note {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2575fc;
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
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            <?php if(isset($login_error)) echo 'display: block;'; else echo 'display: none;'; ?>
        }

        .alert-error {
            background-color: #ffeaea;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
            }
            
            .links {
                flex-direction: column;
                gap: 10px;
            }
            
            .links a {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <form class="login-box" method="POST" action="">
        <div class="faculty-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        
        <div class="login-header">
            <h2>Faculty Login</h2>
            <p>Career Advancement & Development System</p>
        </div>

        <?php if(isset($login_error)): ?>
            <div id="error-alert" class="alert alert-error">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <div class="input-group">
            <label for="email">Email Address</label>
            <i class="fas fa-envelope"></i>
            <input type="text" id="email" name="email" placeholder="Enter your email address" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
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
            <a href="#" onclick="showAdminNote(); return false;">
                <i class="fas fa-info-circle"></i> Admin Login
            </a>
        </div>

        <p class="note" id="admin-note" style="display: none;">
            <i class="fas fa-user-shield"></i> Admin users can login with their credentials above.
        </p>
    </form>
</div>

<script>
    function showAdminNote() {
        const note = document.getElementById('admin-note');
        note.style.display = note.style.display === 'none' ? 'block' : 'none';
    }
    
    // Clear error on input focus
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input');
        const errorAlert = document.getElementById('error-alert');
        
        if(errorAlert) {
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    errorAlert.style.display = 'none';
                });
            });
        }
    });
</script>

</body>
</html>
>>>>>>> 90e527b (Initial commit)
