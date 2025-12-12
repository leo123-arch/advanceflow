<?php
session_start();
include "config.php";

if(!isset($_SESSION['faculty_id']) && !isset($_SESSION['role'])){
    die("Access denied.");
}

$app_id = intval($_GET['id']);

$sql = "
    SELECT pr.*, f.name, f.department, f.id AS faculty_owner
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    WHERE pr.id = '$app_id'
";

$res = mysqli_query($conn, $sql);
if(mysqli_num_rows($res) == 0) die("Application not found");

$data = mysqli_fetch_assoc($res);

// Faculty can only download their own certificate
if(isset($_SESSION['faculty_id']) && $_SESSION['faculty_id'] != $data['faculty_owner']){
    die("Access denied.");
}

if($data['status'] != "Approved"){
    die("Certificate only available after approval.");
}

require __DIR__ . "/fpdf186/fpdf.php";

// Create PDF (Landscape)
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// PAGE DIMENSIONS
$width = 297;
$height = 210;



// ------------------------------
// 2) COLLEGE LOGO (Centered)
// ------------------------------
$pdf->Image(__DIR__ . '/images/logo.jpeg', ($width/2) - 20, 15, 40);

$pdf->Ln(45);

// ------------------------------
// 3) TITLE
// ------------------------------
$pdf->SetFont('Arial', 'B', 26);
$pdf->SetTextColor(80, 50, 0);
$pdf->Cell(0, 12, 'PROMOTION CERTIFICATE', 0, 1, 'C');

// Underline
$pdf->SetLineWidth(0.8);
$pdf->Line(60, $pdf->GetY(), $width - 60, $pdf->GetY());
$pdf->Ln(12);

// ------------------------------
// 4) CONTENT TEXT
// ------------------------------
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);

$pdf->SetFont('Arial', '', 15);
$pdf->Cell(0, 10, "This is to certify that", 0, 1, 'C');
$pdf->Ln(3);

// Name
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetTextColor(0, 70, 130);
$pdf->Cell(0, 12, $data['name'], 0, 1, 'C');
$pdf->Ln(3);

// Department
$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, "Department of " . $data['department'], 0, 1, 'C');
$pdf->Ln(10);

// Paragraph (inside margins)
$pdf->SetFont('Arial', '', 15);
$pdf->MultiCell(240, 9,
    "has successfully been promoted from the position of " . 
    $data['current_position'] . " to " . $data['promotion_to'] .
    " based on outstanding academic achievements and professional contributions.",
    0, 'C'
);

$pdf->Ln(12);

// ------------------------------
// 5) APPROVAL DATE
// ------------------------------
$pdf->SetFont('Arial', 'I', 14);
$pdf->Cell(0, 10, "Date of Approval: " . date("d M Y", strtotime($data['created_at'])), 0, 1, 'C');

// ------------------------------
// 6) SIGNATURE (Inside Certificate)
// ------------------------------
$pdf->SetY(150); // Perfect spot inside your gold border

$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, "__________________________", 0, 1, 'R');
$pdf->Cell(0, 5, "Principal / Director", 0, 1, 'R');

// ------------------------------
// 7) OUTPUT PDF
// ------------------------------
$pdf->Output('D', "Promotion_Certificate_".$data['name'].".pdf");
exit;

?>
