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

// Get research uploads
$result = mysqli_query($conn, 
    "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id' ORDER BY id DESC");

// Get statistics
$total_uploads = mysqli_num_rows($result);
$paper_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id' AND category='Paper'"));
$book_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id' AND category='Book'"));
$conf_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id' AND category='Conference'"));
$patent_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM research_uploads WHERE faculty_id='$faculty_id' AND category='Patent'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Portfolio | Career Advancement System</title>
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

        .stat-card.papers {
            border-top: 4px solid var(--paper-color);
        }

        .stat-card.books {
            border-top: 4px solid var(--book-color);
        }

        .stat-card.conferences {
            border-top: 4px solid var(--conf-color);
        }

        .stat-card.patents {
            border-top: 4px solid var(--patent-color);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-card.total .stat-number { color: var(--primary-color); }
        .stat-card.papers .stat-number { color: var(--paper-color); }
        .stat-card.books .stat-number { color: var(--book-color); }
        .stat-card.conferences .stat-number { color: var(--conf-color); }
        .stat-card.patents .stat-number { color: var(--patent-color); }

        .stat-label {
            font-size: 1rem;
            color: var(--gray-text);
            font-weight: 600;
        }

        /* Research Table */
        .research-table-container {
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

        /* Category Badges */
        .category-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }

        .badge-paper {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--paper-color);
            border: 1px solid rgba(52, 152, 219, 0.3);
        }

        .badge-book {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--book-color);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .badge-conference {
            background-color: rgba(155, 89, 182, 0.1);
            color: var(--conf-color);
            border: 1px solid rgba(155, 89, 182, 0.3);
        }

        .badge-patent {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--patent-color);
            border: 1px solid rgba(231, 76, 60, 0.3);
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

        .upload-btn {
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

        .upload-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(106, 17, 203, 0.4);
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
            
            .research-table-container {
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
            <h2><i class="fas fa-graduation-cap"></i> Career System</h2>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($faculty['name']); ?></div>
                <div class="user-dept"><?php echo htmlspecialchars($faculty['department']); ?></div>
            </div>
        </div>

        <div class="sidebar-nav">
            <a href="faculty_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="faculty_research_upload.php">
                <i class="fas fa-upload"></i> Upload Research
            </a>
            <a href="view_research_uploads.php" class="active">
                <i class="fas fa-eye"></i> View Research
            </a>
            <a href="faculty_research_upload.php">
                <i class="fas fa-plus-circle"></i> New Upload
            </a>
            <a href="faculty_dashboard.php">
                <i class="fas fa-chart-line"></i> API Score
            </a>
            <a href="logout.php">
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
            <h1><i class="fas fa-flask"></i> Research Portfolio</h1>
            <p>Manage and view all your research publications, papers, and academic documents in one place</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_uploads; ?></div>
                <div class="stat-label">Total Uploads</div>
            </div>
            <div class="stat-card papers">
                <div class="stat-number"><?php echo $paper_count; ?></div>
                <div class="stat-label">Research Papers</div>
            </div>
            <div class="stat-card books">
                <div class="stat-number"><?php echo $book_count; ?></div>
                <div class="stat-label">Books/Chapters</div>
            </div>
            <div class="stat-card conferences">
                <div class="stat-number"><?php echo $conf_count; ?></div>
                <div class="stat-label">Conferences</div>
            </div>
            <div class="stat-card patents">
                <div class="stat-number"><?php echo $patent_count; ?></div>
                <div class="stat-label">Patents</div>
            </div>
        </div>

        <!-- Research Table -->
        <div class="research-table-container">
            <div class="table-header">
                <h2><i class="fas fa-file-alt"></i> Research Documents</h2>
                <p>All your uploaded research documents with AI-powered categorization</p>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>File</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td style="font-weight: 500; color: var(--dark-text);">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </td>
                        <td>
                            <?php 
                            $badge_class = '';
                            switch($row['category']) {
                                case 'Paper': $badge_class = 'badge-paper'; break;
                                case 'Book': $badge_class = 'badge-book'; break;
                                case 'Conference': $badge_class = 'badge-conference'; break;
                                case 'Patent': $badge_class = 'badge-patent'; break;
                                default: $badge_class = 'badge-paper';
                            }
                            ?>
                            <span class="category-badge <?php echo $badge_class; ?>">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['category']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="uploads/research/<?php echo htmlspecialchars($row['filename']); ?>" target="_blank" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> View Document
                            </a>
                        </td>
                        <td>
                            <?php 
                            $upload_date = date('M j, Y', strtotime($row['uploaded_at'] ?? $row['created_at']));
                            echo $upload_date;
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php else: ?>

            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>No Research Documents Found</h3>
                <p>You haven't uploaded any research documents yet. Start building your research portfolio by uploading your first document.</p>
                <a href="faculty_research_upload.php" class="upload-btn">
                    <i class="fas fa-plus-circle"></i> Upload Your First Research
                </a>
            </div>

            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="faculty_research_upload.php" class="quick-action-btn">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>Upload New Research</h3>
                <p>Add new research papers, publications, or documents to your portfolio</p>
            </a>
            <a href="faculty_dashboard.php" class="quick-action-btn">
                <i class="fas fa-chart-line"></i>
                <h3>View API Score</h3>
                <p>See how your research contributes to your academic performance score</p>
            </a>
            <a href="faculty_research_upload.php" class="quick-action-btn">
                <i class="fas fa-robot"></i>
                <h3>AI Analysis</h3>
                <p>Get AI-powered insights on your research portfolio</p>
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Career Advancement System | Research Management Portal</p>
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