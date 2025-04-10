<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session check
if (!isset($_SESSION['user_id']) && !isset($_GET['student_id'])) {
    header("Location: login.php");
    exit();
}

require('includes/fpdf.php');
include 'db_connection.php';

//=======================================================================
// Get student ID
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : $_SESSION['user_id'];

// Fetch student data
$query = "SELECT id, name, class, arm FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->bind_result($id, $name, $class, $arm);

if ($stmt->fetch()) {
    $student = [
        'id'    => $id,
        'name'  => $name,
        'class' => $class,
        'arm'   => $arm,
    ];
} else {
    die("Student not found.");
}

// Extend FPDF class with custom methods
class PDF extends FPDF {
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-$yc)*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r ;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }
    
    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    // Add Ellipse method
    function Ellipse($x, $y, $rx, $ry, $style = 'D') {
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $lx = 4/3 * (sqrt(2) - 1) * $rx;
        $ly = 4/3 * (sqrt(2) - 1) * $ry;
        $k = $this->k;
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F b', 
            ($x+$rx)*$k, ($h-$y)*$k,
            ($x+$rx)*$k, ($h-($y-$ly))*$k,
            ($x+$lx)*$k, ($h-($y-$ry))*$k,
            $x*$k, ($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F b', 
            ($x-$lx)*$k, ($h-($y-$ry))*$k,
            ($x-$rx)*$k, ($h-($y-$ly))*$k,
            ($x-$rx)*$k, ($h-$y)*$k,
            ($x-$rx)*$k, ($h-$y)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F b', 
            ($x-$rx)*$k, ($h-($y+$ly))*$k,
            ($x-$lx)*$k, ($h-($y+$ry))*$k,
            $x*$k, ($h-($y+$ry))*$k,
            $x*$k, ($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F b', 
            ($x+$lx)*$k, ($h-($y+$ry))*$k,
            ($x+$rx)*$k, ($h-($y+$ly))*$k,
            ($x+$rx)*$k, ($h-$y)*$k,
            ($x+$rx)*$k, ($h-$y)*$k));
        $this->_out($op);
    }
}

// Instantiate PDF in portrait mode with custom dimensions: 54mm x 85mm
$pdf = new PDF('P', 'mm', array(54, 85));
$pdf->SetMargins(1, 1, 1); // Minimal margins

//=================== Front Side ===================//
$pdf->AddPage();
$pdf->SetAutoPageBreak(false, 0);

// Border for the front side (covering nearly entire card)
$pdf->SetDrawColor(0, 191, 255); // Deep sky blue
$pdf->SetLineWidth(0.5);
$pdf->RoundedRect(1, 1, 52, 83, 3, 'D');

// Top header bar
$pdf->SetFillColor(65, 105, 225); // Royal blue
$pdf->RoundedRect(1, 1, 52, 13, 2, 'F');

// Header text
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(1, 4);
$pdf->Cell(52, 6, 'Your School Name', 0, 1, 'C');

// Optional Logo (adjust size if needed)
if (file_exists('assets/img/logo.png')) {
    $pdf->Image('assets/img/logo.png', 2, 3, 10, 8);
}

// Student Photo (centered)
$photo_filename = str_replace('/', '_', $student['id']); // e.g., wf_1000_24
$photo_path = "studentimg/" . $photo_filename . ".jpg";
if (!file_exists($photo_path)) {
    $photo_path = "studentimg/default.jpg"; // Fallback to default image
}
if (file_exists($photo_path)) {
    // Center the photo horizontally; photo width = 20 mm
    $photoWidth = 20;
    $photoX = (54 - $photoWidth) / 2;
    $pdf->Image($photo_path, $photoX, 15, $photoWidth, 20);
    // Draw circular frame using the Ellipse method (adjust center & radius as needed)
    $centerX = 54 / 2;
    $centerY = 15 + (20 / 2);
    $pdf->SetDrawColor(255, 215, 0); // Gold
    $pdf->SetLineWidth(0.3);
    $pdf->Ellipse($centerX, $centerY, 10, 10, 'D');
}

// Student Name (below photo, centered)
$pdf->SetXY(1, 38);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(0, 0, 139); // Dark blue
$pdf->MultiCell(52, 5, $student['name'], 0,  'C');

// Details background box (for ID, Class, Arm)
$detailsBoxWidth = 44;
$detailsBoxHeight = 20;
$detailsBoxX = (54 - $detailsBoxWidth) / 2;
$detailsBoxY = 50;
$pdf->SetFillColor(240, 248, 255); // Alice blue
$pdf->RoundedRect($detailsBoxX, $detailsBoxY, $detailsBoxWidth, $detailsBoxHeight, 2, 'F');

// Calculate vertical centering for text inside details box
$totalTextHeight = 15; // Adjust if you have more or fewer lines
$textStartY = $detailsBoxY + ($detailsBoxHeight - $totalTextHeight) / 2;

// Student Details text inside the details box
$pdf->SetXY($detailsBoxX, $textStartY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(65, 105, 225);
$pdf->Cell($detailsBoxWidth, 5, "ID: " . $student['id'], 0, 1, 'C');
$pdf->SetX($detailsBoxX);
$pdf->Cell($detailsBoxWidth, 5, "Class: " . $student['class'], 0, 1, 'C');
$pdf->SetX($detailsBoxX);
$pdf->Cell($detailsBoxWidth, 5, "Arm: " . $student['arm'], 0, 1, 'C');

//=================== Back Side ===================//
$pdf->AddPage();

// Border for back side
$pdf->SetDrawColor(0, 191, 255); // Deep sky blue
$pdf->SetLineWidth(0.5);
$pdf->RoundedRect(1, 1, 52, 83, 3, 'D');

// Top header bar on back side
$pdf->SetFillColor(65, 105, 225); // Royal blue
$pdf->RoundedRect(1, 1, 52, 10, 2, 'F');

// Header text on back
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(1, 3);
$pdf->Cell(52, 6, 'Student ID Card', 0, 1, 'C');

// Contact info box
$contactBoxX = 3;
$contactBoxY = 12;
$contactBoxWidth = 46;
$contactBoxHeight = 60;
$pdf->SetFillColor(245, 245, 220); // Beige
$pdf->RoundedRect($contactBoxX, $contactBoxY, $contactBoxWidth, $contactBoxHeight, 2, 'F');

// Contact details inside the box
$pdf->SetTextColor(0, 100, 0); // Dark green
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY($contactBoxX, $contactBoxY + 3);
$pdf->MultiCell($contactBoxWidth, 4, "School Address: 123 Main Street, City", 0,  'C');
$pdf->SetX($contactBoxX);
$pdf->MultiCell($contactBoxWidth, 4, "Phone: +123456789", 0,  'C');
$pdf->SetX($contactBoxX);
$pdf->MultiCell($contactBoxWidth, 4, "Email: info@yourschool.com", 0,  'C');
$pdf->Ln(2);
// Return message as a multi‑line, centered text
$pdf->SetX($contactBoxX);
$pdf->MultiCell($contactBoxWidth, 4, "If found please return to School Name at School Address....................more text here", 0, 'C');

$pdf->Output();
?>
