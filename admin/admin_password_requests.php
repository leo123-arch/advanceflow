<?php
include "config.php";
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

if(isset($_GET['msg'])){
    echo "<script>alert('Email sent successfully!');</script>";
}

$requests = mysqli_query($conn, 
    "SELECT pr.*, f.name, f.email, f.department
     FROM password_requests pr 
     LEFT JOIN faculty f ON pr.faculty_id = f.id 
     ORDER BY pr.id DESC");

$total_requests = mysqli_num_rows($requests);
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM password_requests WHERE status='Pending'"));
$approved_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM password_requests WHERE status='Approved'"));
$rejected_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM password_requests WHERE status='Rejected'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change Requests | Admin Panel</title>
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

        .admin-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .admin-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-role {
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

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card.total {
            border-top: 4px solid var(--primary-color);
        }

        .stat-card.pending {
            border-top: 4px solid var(--warning-color);
        }

        .stat-card.approved {
            border-top: 4px solid var(--success-color);
        }

        .stat-card.rejected {
            border-top: 4px solid var(--error-color);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-card.total .stat-number { color: var(--primary-color); }
        .stat-card.pending .stat-number { color: var(--warning-color); }
        .stat-card.approved .stat-number { color: var(--success-color); }
        .stat-card.rejected .stat-number { color: var(--error-color); }

        .stat-label {
            font-size: 1rem;
            color: var(--gray-text);
            font-weight: 600;
        }

        /* Requests Table */
        .requests-table-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 30px;
        }

        .table-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .table-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: var(--light-bg);
        }

        th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 700;
            color: var(--dark-text);
            border-bottom: 2px solid #e9ecef;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 20px 15px;
            border-bottom: 1px solid #e9ecef;
            color: var(--gray-text);
            font-size: 0.95rem;
        }

        tbody tr {
            transition: all 0.3s;
        }

        tbody tr:hover {
            background-color: rgba(106, 17, 203, 0.03);
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }

        .status-pending {
            background-color: rgba(255, 165, 2, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(255, 165, 2, 0.3);
        }

        .status-approved {
            background-color: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .status-rejected {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-approve {
            background-color: rgba(46, 213, 115, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .btn-approve:hover {
            background-color: rgba(46, 213, 115, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(46, 213, 115, 0.2);
        }

        .btn-reject {
            background-color: rgba(255, 71, 87, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        .btn-reject:hover {
            background-color: rgba(255, 71, 87, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 71, 87, 0.2);
        }

        /* Password Display */
        .password-display {
            font-family: 'Courier New', monospace;
            background: var(--light-bg);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px dashed #ddd;
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--light-bg);
            margin: 20px;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--gray-text);
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: var(--gray-text);
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }

        .quick-action-btn {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-decoration: none;
            color: var(--dark-text);
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-top: 4px solid var(--primary-color);
        }

        .quick-action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .quick-action-btn i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .quick-action-btn h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .quick-action-btn p {
            color: var(--gray-text);
            font-size: 0.9rem;
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .requests-table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .main {
                padding: 15px;
            }
        }

        /* Animation for table rows */
        @keyframes slideInRow {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        tbody tr {
            animation: slideInRow 0.5s ease;
            animation-fill-mode: both;
        }

        tbody tr:nth-child(1) { animation-delay: 0.1s; }
        tbody tr:nth-child(2) { animation-delay: 0.2s; }
        tbody tr:nth-child(3) { animation-delay: 0.3s; }
        tbody tr:nth-child(4) { animation-delay: 0.4s; }
        tbody tr:nth-child(5) { animation-delay: 0.5s; }
        tbody tr:nth-child(6) { animation-delay: 0.6s; }
        tbody tr:nth-child(7) { animation-delay: 0.7s; }
        tbody tr:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
            <div class="admin-info">
                <div class="admin-name"><?php echo htmlspecialchars($admin_name); ?></div>
                <div class="admin-role">System Administrator</div>
            </div>
        </div>

        <div class="sidebar-nav">
            <a href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_promotion_request.php">
                <i class="fas fa-arrow-up"></i> Promotion Applications
            </a>
            <a href="admin_password_requests.php" class="active">
                <i class="fas fa-key"></i> Password Requests
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="sidebar-footer">
            <p>© <?php echo date('Y'); ?> Career System</p>
            <p>Admin Portal v2.0</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-key"></i> Password Change Requests</h1>
            <p>Review and manage password reset requests from faculty members</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_requests; ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?php echo $approved_count; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-number"><?php echo $rejected_count; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="requests-table-container">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Password Reset Requests</h2>
                <p>All pending and processed password change requests from faculty</p>
            </div>

            <?php if(mysqli_num_rows($requests) > 0): ?>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Faculty</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>New Password</th>
                        <th>Status</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = mysqli_fetch_assoc($requests)): ?>
                    <tr>
                        <td><strong>#<?php echo $r['id']; ?></strong></td>
                        <td style="font-weight: 500; color: var(--dark-text);">
                            <?php echo htmlspecialchars($r['name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['department']); ?></td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($r['email']); ?>" style="color: var(--secondary-color);">
                                <?php echo htmlspecialchars($r['email']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="password-display">
                                <?php echo htmlspecialchars($r['new_password']); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $status_class = '';
                            switch($r['status']) {
                                case 'Pending': $status_class = 'status-pending'; break;
                                case 'Approved': $status_class = 'status-approved'; break;
                                case 'Rejected': $status_class = 'status-rejected'; break;
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($r['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $request_date = date('M j, Y', strtotime($r['request_date']));
                            echo $request_date;
                            ?>
                        </td>
                        <td>
                            <?php if($r['status'] == "Pending"): ?>
                                <div class="action-buttons">
                                    <a href="process_password.php?id=<?php echo $r['id']; ?>&action=approve" class="action-btn btn-approve">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="process_password.php?id=<?php echo $r['id']; ?>&action=reject" class="action-btn btn-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--gray-text); font-style: italic;">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php else: ?>

            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-key"></i>
                <h3>No Password Requests Found</h3>
                <p>There are no pending or processed password change requests at the moment.</p>
            </div>

            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="admin_dashboard.php" class="quick-action-btn">
                <i class="fas fa-tachometer-alt"></i>
                <h3>Admin Dashboard</h3>
                <p>Return to the main admin dashboard overview</p>
            </a>
            <a href="admin_promotion_request.php" class="quick-action-btn">
                <i class="fas fa-arrow-up"></i>
                <h3>Promotion Requests</h3>
                <p>Review faculty promotion applications</p>
            </a>
            <a href="logout.php" class="quick-action-btn">
                <i class="fas fa-sign-out-alt"></i>
                <h3>Logout</h3>
                <p>Securely logout from the admin panel</p>
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Admin Password Management</p>
            <p>Last updated: <?php echo date('F j, Y, h:i A'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = 'rgba(106, 17, 203, 0.05)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });

            // Add animation to stats cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add confirmation for approve/reject actions
            const approveButtons = document.querySelectorAll('.btn-approve');
            const rejectButtons = document.querySelectorAll('.btn-reject');
            
            approveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if(!confirm('Are you sure you want to approve this password change request?')) {
                        e.preventDefault();
                    }
                });
            });
            
            rejectButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if(!confirm('Are you sure you want to reject this password change request?')) {
                        e.preventDefault();
                    }
                });
            });

            // Add click effect to quick action buttons
            const quickActions = document.querySelectorAll('.quick-action-btn');
            quickActions.forEach(button => {
                button.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                });
            });

            // Auto-scroll to top
            window.scrollTo(0, 0);
        });
    </script>
</body>
</html>