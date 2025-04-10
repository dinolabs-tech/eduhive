<?php
session_start();
require('includes/fpdf.php');

// Check if the student is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db_connection.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['user_id'];
$sql    = "SELECT * FROM mastersheet WHERE id = '$student_id' ORDER BY csession, term, subject";
$result = $conn->query($sql);

$transcriptData = [];
$studentName    = "";
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (empty($studentName)) {
            $studentid = $row['id'];
            $studentName = $row['name'];
        }
        $csession = $row['csession'];
        $term     = $row['term'];
        $transcriptData[$csession][$term][] = $row;
    }
} else {
    echo "No transcript data available.";
    exit();
}
$conn->close();

// Determine the student image path BEFORE adding it to the PDF.
// Adjust this as needed depending on how you retrieve the student photo.
// Here, I'm using the student ID to generate the filename.
$photo_filename = str_replace('/', '_', $student_id);  // e.g., wf_1000_24
$photo_path = "studentimg/" . $photo_filename . ".jpg";
if (!file_exists($photo_path)) {
    $photo_path = "studentimg/default.jpg"; // Fallback to default image
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Add school logo on the far left
$pdf->Image('assets/img/logo.png', 10, 8, 20);  

// Add student image on the top right
$x = $pdf->GetPageWidth() - 10 - 20; // Right margin (10) + image width (20)
$pdf->Image($photo_path, $x, 8, 20);

// Title and header info
$pdf->Cell(0,10,'School Name',0,1,'C');
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'School Address',0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->Cell(0,10, $studentid . " | " . $studentName,0,1,'C');

$pdf->Ln(5);

// Loop through transcript data and add it to the PDF.
foreach ($transcriptData as $csession => $terms) {
    $firstTerm   = reset($terms);
    $firstRecord = reset($firstTerm);
    $studentClass = $firstRecord['class'];
    $studentArm   = $firstRecord['arm'];

    // Academic session header.
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10, "Academic Session: " . $csession . " | Class: " . $studentClass . " | Arm: " . $studentArm,0,1);
    
    foreach ($terms as $term => $records) {
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,8, "Term: " . $term,0,1);
        
        // Table header.
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFillColor(52, 58, 64); // Dark background.
        $pdf->SetTextColor(255,255,255);
        $widths  = array(80, 10, 10, 10, 15, 15, 15, 15, 25);
        $headers = array('Subject','CA1','CA2','Exam','Cum.','Total','Average','Grade','Remark');
        foreach ($headers as $i => $header) {
            $pdf->Cell($widths[$i], 8, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Reset colors for table rows.
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
        
        // Table rows.
        foreach ($records as $record) {
            $pdf->Cell($widths[0], 8, $record['subject'], 1);
            $pdf->Cell($widths[1], 8, $record['ca1'], 1, 0, 'C');
            $pdf->Cell($widths[2], 8, $record['ca2'], 1, 0, 'C');
            $pdf->Cell($widths[3], 8, $record['exam'], 1, 0, 'C');
            $pdf->Cell($widths[4], 8, $record['lastcum'], 1, 0, 'C');
            $pdf->Cell($widths[5], 8, $record['total'], 1, 0, 'C');
            $pdf->Cell($widths[6], 8, $record['average'], 1, 0, 'C');
            $pdf->Cell($widths[7], 8, $record['grade'], 1, 0, 'C');
            $pdf->Cell($widths[8], 8, $record['remark'], 1, 0, 'C');
            $pdf->Ln();
        }
        $pdf->Ln(5);
    }
    $pdf->Ln(5);
}

$pdf->Output('D', 'transcript.pdf');
?>
