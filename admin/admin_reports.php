    <?php
    session_start();
    include "config.php";

    if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
        die("Access denied");
    }

    require __DIR__ . "/../fpdf186/fpdf.php";

    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial','B',18);
    $pdf->Cell(0,10,'Career Advancement System - Admin Report',0,1,'C');
    $pdf->Ln(10);

    // =================== SUMMARY ===================
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'System Summary',0,1);

    $pdf->SetFont('Arial','',12);

    $totalFaculty = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM faculty"))['c'];
    $totalApps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM promotion_requests"))['c'];
    $approved = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM promotion_requests WHERE status='Approved'"))['c'];
    $pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM promotion_requests WHERE status='Pending'"))['c'];
    $rejected = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM promotion_requests WHERE status='Rejected'"))['c'];

    $pdf->Cell(0,8,"Total Faculty: $totalFaculty",0,1);
    $pdf->Cell(0,8,"Total Promotion Applications: $totalApps",0,1);
    $pdf->Cell(0,8,"Approved: $approved",0,1);
    $pdf->Cell(0,8,"Pending: $pending",0,1);
    $pdf->Cell(0,8,"Rejected: $rejected",0,1);

    $pdf->Ln(10);

    // =================== FACULTY API TABLE ===================
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Faculty API Scores',0,1);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(50,8,'Name',1);
    $pdf->Cell(40,8,'Department',1);
    $pdf->Cell(30,8,'API Score',1);
    $pdf->Cell(30,8,'Status',1);
    $pdf->Ln();

    $query = mysqli_query($conn,"
        SELECT f.name, f.department, pa.api_score, pa.status
        FROM promotion_applications pa
        JOIN faculty f ON pa.faculty_id = f.id
    ");

    $pdf->SetFont('Arial','',10);

    while($row = mysqli_fetch_assoc($query)){
        $pdf->Cell(50,8,$row['name'],1);
        $pdf->Cell(40,8,$row['department'],1);
        $pdf->Cell(30,8,$row['api_score'],1);
        $pdf->Cell(30,8,$row['status'],1);
        $pdf->Ln();
    }

    $pdf->Ln(10);

    // =================== RESEARCH UPLOADS ===================
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Research Upload Summary',0,1);

    $research = mysqli_query($conn,"
        SELECT f.name, COUNT(r.id) as total
        FROM research_uploads r
        JOIN faculty f ON r.faculty_id = f.id
        GROUP BY r.faculty_id
    ");

    $pdf->SetFont('Arial','',10);

    while($row = mysqli_fetch_assoc($research)){
        $pdf->Cell(0,8,$row['name']." uploaded ".$row['total']." research documents",0,1);
    }

    // Output
    $pdf->Output("D","System_Report.pdf");
    exit;
    ?>
