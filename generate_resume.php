<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Faculty basic info
$f = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM faculty WHERE id='$faculty_id'"
));

// Research count
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM research_uploads WHERE faculty_id='$faculty_id'"
));

// Teaching score
$t = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(teaching_hours) as hours FROM teaching_activities WHERE faculty_id='$faculty_id'"
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Generated Resume | Career Advancement System</title>
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
            --accent-color: #2ed573;
            --dark-color: #2d3436;
            --light-color: #f8f9fa;
            --gray-color: #636e72;
            --border-color: #e9ecef;
            --card-bg: #ffffff;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .resume-wrapper {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Sidebar */
        .sidebar {
            background: var(--card-bg);
            border-radius: 25px;
            padding: 40px 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: sticky;
            top: 30px;
            height: fit-content;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 25px;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(106, 17, 203, 0.3);
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .profile-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .contact-info {
            background: var(--light-color);
            border-radius: 15px;
            padding: 20px;
            width: 100%;
            margin: 25px 0;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            color: var(--gray-color);
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-item i {
            color: var(--primary-color);
            font-size: 18px;
            width: 25px;
        }

        .stats-sidebar {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            width: 100%;
        }

        .stat-item-sidebar {
            background: var(--light-color);
            border-radius: 12px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }

        .stat-item-sidebar:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.1));
        }

        .stat-icon-sidebar {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .stat-content-sidebar h3 {
            font-size: 14px;
            color: var(--gray-color);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .stat-value-sidebar {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark-color);
        }

        /* Main Content */
        .main-content {
            background: var(--card-bg);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .resume-header {
            margin-bottom: 40px;
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 3px solid var(--light-color);
        }

        .resume-header h1 {
            font-size: 36px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .resume-header p {
            color: var(--gray-color);
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Sections Grid */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 1100px) {
            .sections-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: var(--light-color);
            border-radius: 20px;
            padding: 35px;
            transition: all 0.4s;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .section-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-color);
            box-shadow: 0 15px 30px rgba(106, 17, 203, 0.15);
        }

        .section-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
        }

        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-color);
        }

        .card-content {
            line-height: 1.8;
            color: var(--dark-color);
            font-size: 16px;
        }

        .highlight {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            padding: 2px 5px;
        }

        /* Qualification Card - Full Width */
        .qualification-card {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.05), rgba(37, 117, 252, 0.05));
            border: 2px solid;
            border-image: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) 1;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 2px solid var(--light-color);
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 35px;
            border-radius: 15px;
            font-size: 17px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            min-width: 200px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(106, 17, 203, 0.3);
        }

        .btn-secondary {
            background: var(--light-color);
            color: var(--dark-color);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .resume-wrapper {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .sidebar {
                position: static;
                order: 2;
            }
            
            .main-content {
                order: 1;
                padding: 30px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .main-content {
                padding: 25px;
            }
            
            .sidebar {
                padding: 30px 20px;
            }
            
            .resume-header h1 {
                font-size: 28px;
            }
            
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 36px;
            }
            
            .profile-name {
                font-size: 24px;
            }
            
            .card-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .card-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .sections-grid {
                gap: 20px;
            }
            
            .section-card {
                padding: 25px;
            }
            
            .card-title {
                font-size: 20px;
            }
            
            .action-btn {
                padding: 15px 25px;
                font-size: 16px;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .resume-wrapper {
                box-shadow: none !important;
                max-width: 100% !important;
            }
            
            .section-card:hover {
                transform: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="resume-wrapper">
        <!-- Sidebar with Profile Info -->
        <div class="sidebar">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($f['name'], 0, 1)); ?>
            </div>
            
            <h2 class="profile-name"><?php echo htmlspecialchars($f['name']); ?></h2>
            <div class="profile-title">Faculty Member</div>
            
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo htmlspecialchars($f['email']); ?></span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-building"></i>
                    <span><?php echo htmlspecialchars($f['department']); ?> Department</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo $f['experience']; ?> Years Experience</span>
                </div>
            </div>
            
            <div class="stats-sidebar">
                <div class="stat-item-sidebar">
                    <div class="stat-icon-sidebar">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content-sidebar">
                        <h3>Experience</h3>
                        <div class="stat-value-sidebar"><?php echo $f['experience']; ?>+ years</div>
                    </div>
                </div>
                
                <div class="stat-item-sidebar">
                    <div class="stat-icon-sidebar">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content-sidebar">
                        <h3>Teaching Hours</h3>
                        <div class="stat-value-sidebar"><?php echo $t['hours'] ?? 0; ?></div>
                    </div>
                </div>
                
                <div class="stat-item-sidebar">
                    <div class="stat-icon-sidebar">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="stat-content-sidebar">
                        <h3>Research Works</h3>
                        <div class="stat-value-sidebar"><?php echo $r['total']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="resume-header">
                <h1>Professional Resume</h1>
                <p>AI-Generated Comprehensive Professional Profile</p>
            </div>
            
            <div class="sections-grid">
                <!-- Professional Summary -->
                <div class="section-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="card-title">Professional Summary</h3>
                    </div>
                    <div class="card-content">
                        Dedicated and passionate faculty member with <span class="highlight"><?php echo $f['experience']; ?> years</span> of teaching experience in the <span class="highlight"><?php echo htmlspecialchars($f['department']); ?></span> department. Committed to excellence in education, actively engaged in research activities, student mentorship, and continuous professional development within the academic community.
                    </div>
                </div>
                
                <!-- Teaching Experience -->
                <div class="section-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3 class="card-title">Teaching Experience</h3>
                    </div>
                    <div class="card-content">
                        Extensive teaching experience with a total of <span class="highlight"><?php echo $t['hours'] ?? 0; ?> teaching hours</span> recorded. Demonstrated commitment to delivering high-quality education, fostering engaging learning environments, and incorporating innovative teaching methodologies. Continuously updating pedagogical approaches to enhance student learning outcomes and academic success.
                    </div>
                </div>
                
                <!-- Research Contributions -->
                <div class="section-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <h3 class="card-title">Research Contributions</h3>
                    </div>
                    <div class="card-content">
                        Active researcher with <span class="highlight"><?php echo $r['total']; ?> research documents</span> contributed to the academic community. Engaged in scholarly activities and knowledge creation, with focus on advancing the field of <span class="highlight"><?php echo htmlspecialchars($f['department']); ?></span>. Committed to producing high-impact research that contributes significantly to the institution's academic reputation and standing.
                    </div>
                </div>
                
                <!-- Qualification -->
                <div class="section-card qualification-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3 class="card-title">Academic Qualification</h3>
                    </div>
                    <div class="card-content">
                        <span class="highlight"><?php echo htmlspecialchars($f['qualification']); ?></span>
                        <br><br>
                        This qualification forms the foundation of expertise in the field, complemented by extensive practical experience and continuous professional development throughout the academic career.
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="resume_pdf.php" class="action-btn btn-primary">
                    <i class="fas fa-download"></i>
                    Download Resume PDF
                </a>
                <a href="faculty_dashboard.php" class="action-btn btn-secondary">
                    <i class="fas fa-tachometer-alt"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, observerOptions);

            // Observe all section cards
            document.querySelectorAll('.section-card').forEach((card) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });

            // Add hover sound effect (optional)
            const cards = document.querySelectorAll('.section-card, .stat-item-sidebar');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.cursor = 'pointer';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.cursor = 'default';
                });
            });

            // Button hover effects
            const buttons = document.querySelectorAll('.action-btn');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>