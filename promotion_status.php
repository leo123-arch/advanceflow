<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Get faculty info
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id='$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

// Get promotion applications
$result = mysqli_query($conn, "SELECT * FROM promotion_requests WHERE faculty_id='$faculty_id' ORDER BY created_at DESC");
$total_applications = mysqli_num_rows($result);
$approved_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM promotion_requests WHERE faculty_id='$faculty_id' AND status='Approved'"));
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM promotion_requests WHERE faculty_id='$faculty_id' AND status='Pending'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Applications Status | Career Advancement System</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
            color: var(--dark-text);
        }

        .container {
            max-width: 1200px;
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
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* User Info */
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

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-top: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-card.total .stat-number { color: var(--primary-color); }
        .stat-card.approved .stat-number { color: var(--success-color); }
        .stat-card.pending .stat-number { color: var(--warning-color); }
        .stat-card.rejected .stat-number { color: var(--error-color); }

        .stat-label {
            font-size: 1rem;
            color: var(--gray-text);
            font-weight: 600;
        }

        /* Applications Table */
        .applications-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
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
            transform: translateX(5px);
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
        }

        .btn-view {
            background-color: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(37, 117, 252, 0.3);
        }

        .btn-view:hover {
            background-color: rgba(37, 117, 252, 0.2);
            transform: translateY(-2px);
        }

        .btn-download {
            background: linear-gradient(135deg, var(--success-color), #25b764);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 213, 115, 0.3);
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 213, 115, 0.4);
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

        .apply-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .apply-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        /* Action Buttons Footer */
        .action-footer {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .footer-btn {
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .footer-btn.primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.3);
        }

        .footer-btn.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
        }

        .footer-btn.secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .footer-btn.secondary:hover {
            background: var(--light-bg);
            transform: translateY(-3px);
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
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .applications-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            .action-footer {
                flex-direction: column;
            }
            
            .footer-btn {
                width: 100%;
                justify-content: center;
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
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Promotion Applications Status</h1>
            <p>Track the status of your academic promotion applications and download certificates</p>
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Faculty: <?php echo htmlspecialchars($faculty['name']); ?>
                | Department: <?php echo htmlspecialchars($faculty['department']); ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_applications; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?php echo $approved_count; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-number"><?php echo $total_applications - $approved_count - $pending_count; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="applications-table">
            <div class="table-header">
                <h2><i class="fas fa-file-alt"></i> Your Promotion Applications</h2>
                <p>All your submitted applications with current status and actions</p>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Current Position</th>
                        <th>Applied For</th>
                        <th>Document</th>
                        <th>Remarks</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($row['current_position']); ?></td>
                        <td><?php echo htmlspecialchars($row['promotion_to']); ?></td>
                        <td>
                            <?php if(!empty($row['document'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($row['document']); ?>" target="_blank" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php else: ?>
                            <span style="color: var(--gray-text);">No document</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($row['remarks'])): ?>
                            <span title="<?php echo htmlspecialchars($row['remarks']); ?>">
                                <i class="fas fa-comment"></i> View
                            </span>
                            <?php else: ?>
                            <span style="color: var(--gray-text);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $status_class = '';
                            switch($row['status']) {
                                case 'Pending': $status_class = 'status-pending'; break;
                                case 'Approved': $status_class = 'status-approved'; break;
                                case 'Rejected': $status_class = 'status-rejected'; break;
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <?php if($row['status'] == "Approved"): ?>
                                <a href="promotion_certificate.php?id=<?php echo $row['id']; ?>" class="action-btn btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php else: ?>
                                <span style="color: var(--gray-text);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php else: ?>

            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>No Applications Found</h3>
                <p>You haven't submitted any promotion applications yet. Start your academic advancement journey by submitting your first application.</p>
                <a href="apply_promotion.php" class="apply-btn">
                    <i class="fas fa-plus-circle"></i> Apply for Promotion
                </a>
            </div>

            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-footer">
            <a href="apply_promotion.php" class="footer-btn primary">
                <i class="fas fa-plus-circle"></i> New Application
            </a>
            <a href="faculty_dashboard.php" class="footer-btn secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Promotion Status Portal</p>
            <p>Last updated: <?php echo date('F j, Y, h:i A'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            // Add tooltip for remarks
            const remarkCells = document.querySelectorAll('td span[title]');
            remarkCells.forEach(cell => {
                cell.addEventListener('click', function() {
                    const remarks = this.getAttribute('title');
                    if(remarks) {
                        alert('Remarks:\n\n' + remarks);
                    }
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

            // Auto-scroll to top
            window.scrollTo(0, 0);
        });
    </script>
</body>
</html>