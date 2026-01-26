<?php
// generate_certificate_enhanced.php
session_start();
include "config.php";
require __DIR__ . "/fpdf186/fpdf.php";

if(!isset($_SESSION['faculty_id'])){
    die("Access denied");
}

$faculty_id = $_SESSION['faculty_id'];
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($app_id <= 0) {
    die("Invalid certificate ID");
}

$sql = "
    SELECT pr.*, f.name, f.department, f.id AS faculty_owner
    FROM promotion_requests pr
    LEFT JOIN faculty f ON pr.faculty_id = f.id
    WHERE pr.id = '$app_id'
";

$res = mysqli_query($conn, $sql);
if(mysqli_num_rows($res) == 0) {
    die("Certificate not found!");
}

$data = mysqli_fetch_assoc($res);

if($faculty_id != $data['faculty_owner']){
    die("Access denied.");
}

if($data['status'] != "Approved"){
    die("Certificate only available for approved applications.");
}

// Enhanced PDF with better design
class EnhancedPDF extends FPDF {
    var $borderColor = array(212, 175, 55); // Gold
    var $primaryColor = array(106, 17, 203); // Purple
    var $secondaryColor = array(37, 117, 252); // Blue
    var $accentColor = array(139, 69, 19); // Brown
    
    function Header() {
        // Create gradient background
        $this->createGradientBackground();
        
        // Add decorative border
        $this->decorativeBorder();
        
        // Add certificate header
        $this->certificateHeader();
    }
    
    function createGradientBackground() {
        // Simulate gradient with multiple rectangles
        for($i = 0; $i < 297; $i += 5) {
            $color = $this->interpolateColor(
                $this->primaryColor, 
                $this->secondaryColor, 
                $i / 297
            );
            $this->SetFillColor($color[0], $color[1], $color[2]);
            $this->Rect(0, $i, 210, 5, 'F');
        }
    }
    
    function interpolateColor($color1, $color2, $ratio) {
        return array(
            $color1[0] + ($color2[0] - $color1[0]) * $ratio,
            $color1[1] + ($color2[1] - $color1[1]) * $ratio,
            $color1[2] + ($color2[2] - $color1[2]) * $ratio
        );
    }
    
    function decorativeBorder() {
        // Thick gold border
        $this->SetDrawColor($this->borderColor[0], $this->borderColor[1], $this->borderColor[2]);
        $this->SetLineWidth(3);
        $this->Rect(10, 10, 190, 277);
        
        // Inner thin border
        $this->SetLineWidth(1);
        $this->Rect(15, 15, 180, 267);
        
        // Corner decorations
        $this->SetLineWidth(2);
        $size = 15;
        
        // Top-left corner
        $this->Line(10, 10, 10+$size, 10);
        $this->Line(10, 10, 10, 10+$size);
        
        // Top-right corner
        $this->Line(200-$size, 10, 200, 10);
        $this->Line(200, 10, 200, 10+$size);
        
        // Bottom-left corner
        $this->Line(10, 287-$size, 10, 287);
        $this->Line(10, 287, 10+$size, 287);
        
        // Bottom-right corner
        $this->Line(200-$size, 287, 200, 287);
        $this->Line(200, 287-$size, 200, 287);
    }
    
    function certificateHeader() {
        // Logo icon
        $this->SetFont('Arial', 'B', 60);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(85, 25);
        $this->Cell(40, 30, "🏆", 0, 1, 'C');
        
        // Main title
        $this->SetFont('Times', 'B', 40);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 60);
        $this->Cell(190, 15, "CERTIFICATE OF PROMOTION", 0, 1, 'C');
        
        // Subtitle
        $this->SetFont('Times', 'I', 16);
        $this->SetXY(10, 80);
        $this->Cell(190, 10, "Awarded for Outstanding Academic Achievement", 0, 1, 'C');
    }
    
    function Footer() {
        $this->SetY(-30);
        
        // Footer decorative line
        $this->SetDrawColor($this->borderColor[0], $this->borderColor[1], $this->borderColor[2]);
        $this->SetLineWidth(0.5);
        $this->Line(20, $this->GetY(), 190, $this->GetY());
        
        // Footer text
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        
        $this->Cell(0, 6, 'CareerFlow Advancement System | Official Promotion Certificate', 0, 1, 'C');
        $this->Cell(0, 6, 'Certificate ID: CF-' . date('Y') . '-' . str_pad($_GET['id'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');
        $this->Cell(0, 6, 'Generated on: ' . date('F d, Y'), 0, 0, 'C');
    }
    
    function createContentBox() {
        // White content box with shadow effect
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        
        // Main content area
        $this->Rect(20, 110, 170, 150, 'DF');
        
        // Add subtle shadow
        $this->SetDrawColor(150, 150, 150);
        $this->Rect(21, 111, 170, 150);
    }
    
    function addSeal() {
        // Add circular seal
        // $this->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        // $this->SetLineWidth(2);
        // $this->Circle(105, 250, 20);
        
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->SetXY(95, 245);
        $this->Cell(20, 10, "✓", 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(95, 255);
        $this->Cell(20, 5, "APPROVED", 0, 1, 'C');
    }
}

// Create enhanced PDF
$pdf = new EnhancedPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Create content box
$pdf->createContentBox();

// "This is to certify that" text
$pdf->SetFont('Times', 'I', 16);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(20, 120);
$pdf->Cell(170, 10, "This is to certify that", 0, 1, 'C');

// Recipient name with decorative underline
$pdf->SetFont('Times', 'B', 32);
$pdf->SetTextColor($pdf->primaryColor[0], $pdf->primaryColor[1], $pdf->primaryColor[2]);
$pdf->SetXY(20, 140);
$pdf->Cell(170, 15, strtoupper($data['name']), 0, 1, 'C');

// Decorative underline
$pdf->SetDrawColor($pdf->borderColor[0], $pdf->borderColor[1], $pdf->borderColor[2]);
$pdf->SetLineWidth(1);
$pdf->Line(60, 155, 150, 155);

// Department
$pdf->SetFont('Times', 'I', 18);
$pdf->SetTextColor($pdf->accentColor[0], $pdf->accentColor[1], $pdf->accentColor[2]);
$pdf->SetXY(20, 165);
$pdf->Cell(170, 10, "Department of " . $data['department'], 0, 1, 'C');

// Promotion text
$pdf->SetFont('Times', '', 14);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(20, 185);
$text = "has been promoted from\n\"" . $data['current_position'] . "\" to \"" . $data['promotion_to'] . "\"\nin recognition of exceptional performance,\ndedication, and contribution to academic excellence.";
$pdf->MultiCell(170, 8, $text, 0, 'C');

// Add approval seal
$pdf->addSeal();

// Signature area
$pdf->SetY(220);
$pdf->SetFont('Times', 'I', 24);
$pdf->SetTextColor($pdf->accentColor[0], $pdf->accentColor[1], $pdf->accentColor[2]);
$pdf->Cell(170, 10, "Dr. Alexander Morgan", 0, 1, 'R');

$pdf->SetFont('Times', '', 14);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(170, 8, "Dean of Academic Affairs", 0, 1, 'R');

$pdf->SetFont('Times', 'I', 12);
$pdf->SetTextColor($pdf->primaryColor[0], $pdf->primaryColor[1], $pdf->primaryColor[2]);
$pdf->Cell(170, 8, "CareerFlow University", 0, 1, 'R');

// Certificate details in a box
$pdf->SetY(240);
$pdf->SetFillColor(245, 247, 251);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.5);
$pdf->Rect(30, 240, 150, 40, 'DF');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor($pdf->primaryColor[0], $pdf->primaryColor[1], $pdf->primaryColor[2]);
$pdf->SetXY(30, 245);
$pdf->Cell(150, 6, 'CERTIFICATE DETAILS', 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(80, 80, 80);

$pdf->SetXY(30, 255);
$pdf->Cell(75, 6, 'Approval Date:', 0, 0, 'L');
$pdf->Cell(75, 6, date('F d, Y', strtotime($data['created_at'])), 0, 1, 'L');

$pdf->SetXY(30, 262);
$pdf->Cell(75, 6, 'Certificate ID:', 0, 0, 'L');
$pdf->Cell(75, 6, 'CF-' . date('Y') . '-' . str_pad($data['id'], 6, '0', STR_PAD_LEFT), 0, 1, 'L');

$pdf->SetXY(30, 269);
$pdf->Cell(75, 6, 'Valid From:', 0, 0, 'L');
$pdf->Cell(75, 6, date('F d, Y', strtotime($data['created_at'])), 0, 1, 'L');

// Output the PDF
$filename = str_replace(' ', '_', $data['name']) . '_Promotion_Certificate.pdf';
$pdf->Output('D', $filename);
exit;
?>