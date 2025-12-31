<?php
session_start();
include "config.php";
require 'vendor/autoload.php';  // For PDF parser
require 'ai_classifier.php';    // AI model

use Smalot\PdfParser\Parser;

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit;
}

$faculty_id = $_SESSION['faculty_id'];
$title      = mysqli_real_escape_string($conn, $_POST['title']);

$uploadDir = "uploads/research/";
if(!is_dir($uploadDir)){ 
    mkdir($uploadDir, 0777, true); 
}

$fileName = time() . "_" . basename($_FILES["file"]["name"]);
$targetFile = $uploadDir . $fileName;

$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['pdf','jpg','jpeg','png'];

if(!in_array($ext, $allowed)){
    echo "<script>alert('Invalid file type. Allowed: PDF, JPG, JPEG, PNG'); history.back();</script>";
    exit;
}

if(!move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)){
    echo "<script>alert('File upload failed!'); history.back();</script>";
    exit;
}

/* -------------------------
     AI: Extract Text
---------------------------*/
$text = "";

if($ext == "pdf"){
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($targetFile);
        $text = $pdf->getText();
    } catch (Exception $e) {
        $text = $title . " " . $fileName;  // fallback if PDF invalid
    }
} else {
    // Image fallback (OCR not installed)
    $text = $title . " " . $fileName;
}

/* -------------------------
     AI: Predict Category
---------------------------*/
$predictedCategory = classifyDocumentText($text, $fileName);

/* -------------------------
     SAVE TO DATABASE
---------------------------*/
$query = "INSERT INTO research_uploads (faculty_id, title, category, filename)
          VALUES ('$faculty_id', '$title', '$predictedCategory', '$fileName')";

mysqli_query($conn, $query);

echo "<script>
    alert('Document Uploaded Successfully! AI detected category: $predictedCategory');
    window.location='view_research_uploads.php';
</script>";
?>
