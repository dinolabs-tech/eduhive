<?php
// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require 'db_connection.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Include the rotation library (ensure the path is correct)
require('includes/rotation.php');

// Function to fetch distinct dropdown data safely
function fetchDropdownData($conn, $table, $column)
{
    // Escape table and column names
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    
    $result = $conn->query("SELECT DISTINCT `$column` FROM `$table` ORDER BY `$column` ASC");
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row[$column];
        }
    }
    return $data;
}

// Get dropdown options
$classOptions   = fetchDropdownData($conn, 'class', 'class');
$armOptions     = fetchDropdownData($conn, 'arm', 'arm');
$termOptions    = ['1st Term', '2nd Term', '3rd Term'];
$sessionOptions = fetchDropdownData($conn, 'mastersheet', 'csession');

// Get form values (if set)
$class   = isset($_POST['class'])   ? htmlspecialchars($_POST['class'])   : '';
$arm     = isset($_POST['arm'])     ? htmlspecialchars($_POST['arm'])     : '';
$term    = isset($_POST['term'])    ? htmlspecialchars($_POST['term'])    : '';
$session = isset($_POST['session']) ? htmlspecialchars($_POST['session']) : '';

// Initialize arrays to store data
$scores   = [];
$students = [];
$subjects = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare statement to safely query the mastersheet table
    $stmt = $conn->prepare("
        SELECT name, subject, average FROM mastersheet 
        WHERE class = ? AND arm = ? AND term = ? AND csession = ?
    ");
    $stmt->bind_param("ssss", $class, $arm, $term, $session);
    $stmt->execute();
    
    // Bind result variables
    $stmt->store_result();
    $stmt->bind_result($name, $subject, $average);
    
    // Fetch the results using bind_result
    while ($stmt->fetch()) {
        $scores[$name][$subject] = $average;
        $students[$name] = true;
        $subjects[$subject] = true;
    }
    
    // Convert keys to arrays
    $students = array_keys($students);
    $subjects = array_keys($subjects);
    
    generatePDF($class, $arm, $term, $session, $students, $subjects, $scores);
    // generatePDF() will exit after sending the PDF
}

// -----------------------------------------------------------------
// PDF Generation Function
// -----------------------------------------------------------------
function generatePDF($class, $arm, $term, $session, $students, $subjects, $scores)
{
    // Create a new PDF_Rotate object (landscape A4)
    $pdf = new PDF_Rotate('L', 'mm', 'A4');
    $pdf->AddPage();
    
    // Title settings
    $pdf->SetFont('Arial', 'B', 14);
    $title = "Master Sheet for $class $arm - $term ($session)";
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Table column settings
    $cellWidthName    = 80;  // width for the "Student Name" column
    $cellWidthSubject = 10;  // width for each subject header cell
    $headerHeight     = 90;  // height for the rotated header row
    $rowHeight        = 8;   // height for each data row
    
    // ---------------------------
    // Table Header (Rotated Subject Headers)
    // ---------------------------
    $pdf->SetFont('Arial', 'B', 8);
    
    // Print the "Student Name" header cell with the header height
    $pdf->Cell($cellWidthName, $headerHeight, 'STUDENT NAME', 1, 0, 'C');
    
    // For each subject, print a cell with rotated text
    foreach ($subjects as $subject) {
        // Save current x, y position for the subject header cell
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        // Draw the cell border (will be replaced by rotated text)
        $pdf->Rect($x, $y, $cellWidthSubject, $headerHeight);
        
        // Rotate text 90° clockwise; set the rotation center to the center of the cell
        $pdf->Rotate(90, $x + $cellWidthSubject / 2, $y + $headerHeight / 2);
        
        // Use a slightly smaller font for rotated text if needed
        $pdf->SetFont('Arial', 'B', 8);
        // Adjust text position to center in the rotated cell. 
        // You may need to tweak the offsets (-3, +3) depending on your text length.
        $pdf->Text($x + $cellWidthSubject / 2 - 40, $y + $headerHeight / 2 + 0, $subject);
        
        // Reset rotation and font back to normal for headers
        $pdf->Rotate(0);
        $pdf->SetFont('Arial', 'B', 8);
        
        // Move the cursor to the next cell
        $pdf->SetXY($x + $cellWidthSubject, $y);
    }
    $pdf->Ln();
    
    // ---------------------------
    // Table Content
    // ---------------------------
    $pdf->SetFont('Arial', '', 8);
    foreach ($students as $student) {
        // Print student name cell using the data row height
        $pdf->Cell($cellWidthName, $rowHeight, $student, 1, 0, 'L');
        
        // For each subject, print the corresponding score (or a dash if missing)
        foreach ($subjects as $subject) {
            $score = isset($scores[$student][$subject]) ? $scores[$student][$subject] : '-';
            $pdf->Cell($cellWidthSubject, $rowHeight, $score, 1, 0, 'C');
        }
        $pdf->Ln();
    }
    
    // Force download of the PDF file (the 'D' option)
    try {
        $pdf->Output('D', "Master_Sheet_{$class}_{$arm}_{$term}_{$session}.pdf");
    } catch (Exception $e) {
        echo "Error generating PDF: " . $e->getMessage();
    }
    exit; // Stop any further output
}



?>