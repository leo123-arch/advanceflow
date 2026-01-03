<?php
session_start();
include "config.php";
require "fpdf186/fpdf.php";

$faculty_id = $_SESSION['faculty_id'];

$f = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM faculty WHERE id='$faculty_id'"
));

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,"Faculty Resume",0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Name: ".$f['name'],0,1);
$pdf->Cell(0,8,"Department: ".$f['department'],0,1);
$pdf->Cell(0,8,"Email: ".$f['email'],0,1);
$pdf->Ln(5);

$pdf->MultiCell(0,8,"This resume was auto-generated using AI-based academic profiling.");

$pdf->Output("D","Faculty_Resume.pdf");
?>