<?php
session_start();
include "config.php";
require __DIR__ . "/fpdf186/fpdf.php";

if(!isset($_SESSION['faculty_id'])){
    die("Access denied");
}

$faculty_id = $_SESSION['faculty_id'];

$f = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM faculty WHERE id='$faculty_id'"
));

$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM research_uploads WHERE faculty_id='$faculty_id'"
));

$t = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(teaching_hours) as hours FROM teaching_activities WHERE faculty_id='$faculty_id'"
));

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',20);
$pdf->Cell(0,10,$f['name'],0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,$f['email']." | ".$f['department'],0,1,'C');
$pdf->Ln(10);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Professional Summary",0,1);

$pdf->SetFont('Arial','',12);
$pdf->MultiCell(0,8,
    "Dedicated faculty member with ".$f['experience']." years of teaching experience, actively involved in research and academic development."
);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Teaching Experience",0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Total Teaching Hours: ".($t['hours'] ?? 0),0,1);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Research Contributions",0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Total Research Uploads: ".$r['total'],0,1);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Qualification",0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,$f['qualification'],0,1);

$pdf->Output("D","Faculty_Resume.pdf");
exit;
?>
