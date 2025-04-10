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

$pdf = new PDF('L', 'mm', array(85, 54));
$pdf->SetMargins(5, 5, 5);

//=================== Front Side ===================//
$pdf->AddPage();
$pdf->SetAutoPageBreak(false, 0);


// Colored header bar
$pdf->SetFillColor(65, 105, 225); // Royal blue
$pdf->RoundedRect(1, 1, 83, 13, 2, 'F');

// Border
$pdf->SetDrawColor(0, 191, 255); // Deep sky blue
$pdf->SetLineWidth(0.5);
$pdf->RoundedRect(1, 1, 83, 52, 3, 'D');

// Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(5, 4);
$pdf->Cell(80, 6, 'Your School Name', 0, 1, 'C');

// Logo
if (file_exists('assets/img/logo.png')) {
    $pdf->Image('assets/img/logo.png', 0, 3, 12, 8);
}

// Student Name
$pdf->SetTextColor(0, 0, 139); // Dark blue
$pdf->SetXY(5, 15);
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(75, 4, $student['name'], 0, 'C');


// Student Photo with circular frame from studentimg folder
$photo_filename = str_replace('/', '_', $student['id']); // e.g., wf_1000_24
$photo_path = "studentimg/" . $photo_filename . ".jpg";

if (!file_exists($photo_path)) {
    $photo_path = "studentimg/default.jpg"; // Fallback to default image
}

if (file_exists($photo_path)) {
    $pdf->Image($photo_path, 3, 23, 25, 25);
    $pdf->SetDrawColor(255, 215, 0); // Gold
    $pdf->SetLineWidth(0.3);
    $pdf->Ellipse(19.5, 27.5, 12.5, 12.5, 'D');
}

// Details background box
$pdf->SetFillColor(240, 248, 255); // Alice blue
$pdf->RoundedRect(32, 24, 47, 25, 1, 'F');

// Calculate vertical centering for text inside the details box
$boxTop = 20;
$boxHeight = 25;
$totalTextHeight = 9; // 4 for the first line and 5 for the second line (adjust if needed)
$detailsY = $boxTop + (($boxHeight - $totalTextHeight) / 2); // This will be 28

// Student Details
$pdf->SetTextColor(0, 0, 139); // Dark blue
$detailsX = 35;

$pdf->SetXY($detailsX, $detailsY);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(65, 105, 225);
$pdf->Cell(35, 5, "ID: " . $student['id'], 0, 1);

$pdf->SetX($detailsX);
$pdf->Cell(35, 5, "Class: " . $student['class'], 0, 1);

$pdf->SetX($detailsX);
$pdf->Cell(35, 5, "Arm: " . $student['arm'], 0, 1);

//=================== Back Side ===================//
$pdf->AddPage();

// Border
$pdf->SetDrawColor(0, 191, 255); // Deep sky blue
$pdf->SetLineWidth(0.5);
$pdf->RoundedRect(1, 1, 83, 52, 3, 'D');

// Header bar
$pdf->SetFillColor(65, 105, 225); // Royal blue
$pdf->RoundedRect(1, 1, 83, 10, 2, 'F');

// Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(5, 3);
$pdf->Cell(75, 6, 'Student ID Card', 0, 1, 'C');

// Contact info box
$pdf->SetFillColor(245, 245, 220); // Beige
$pdf->RoundedRect(3, 13, 79, 36, 2, 'F');

// Contact details
$pdf->SetTextColor(0, 100, 0); // Dark green
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(5, 17);
$pdf->Cell(75, 4, "School Address: 123 Main Street, City", 0, 1, 'C');
$pdf->SetX(5);
$pdf->Cell(75, 4, "Phone: +123456789", 0, 1, 'C');
$pdf->SetX(5);
$pdf->Cell(75, 4, "Email: info@yourschool.com", 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetX(5);
$pdf->MultiCell(75, 4, "If found please return to School Name at School Address....................more text here", 0, 'C');


$pdf->Output();
?>