<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//includes FPDF library
require('includes/fpdf.php');
// Database connection
include 'db_connection.php';


//================================================


// Student ID from URL (assuming the student ID is passed via GET)
$student_id = $_GET['student_id']; // Example student ID

// Fetch the student's photo (BLOB) from the database
$query = "SELECT photo FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $student_id);
$stmt->execute();
$stmt->bind_result($photo_blob);
$stmt->fetch();
$stmt->close();

// If photo exists, save it temporarily as a file
if ($photo_blob) {
  $photo_path = 'temp_student_photo.jpg'; // Temporary photo path
  file_put_contents($photo_path, $photo_blob); // Save BLOB to a file
} else {
  // Set a default photo if the student doesn't have a photo
  $photo_path = 'path/to/default/photo.jpg'; // Default photo path
}

// Assuming your database connection is already established
class MyPDF extends FPDF {
  protected $angle = 0; // Initialize the angle property

  public $student_photo; // Property to hold the photo path

  // Header
  function Header() {
    // Set font for the header
    $this->SetFont('Arial', 'B', 10);

    // Add school logo on the far left
    $this->Image('assets/img/logo.png', 10, 8, 20);  // Adjust the position and size (10, 8, 20)

    // School name (Centered)
    $this->SetFont('Arial', 'B', 15);
    $this->Cell(0, 5, 'Dinolabs Tech Services', 0, 1, 'C');

    $this->SetFont('Arial', 'B', 11);
    // School address (Centered)
    $this->Cell(0, 5, '  Suite 10, Wing B, 5th Floor, Tisco House, Alagbaka Akure Ondo State.', 0, 1, 'C');

    // School email (Centered)
    $this->Cell(0, 5, 'schoolemail@gmail.com', 0, 1, 'C');

    // School mobile (Centered)
    $this->Cell(0, 5, '+234 813 772 6887', 0, 1, 'C');

    // Add some space after the header
    $this->Ln(5);

    // Draw a horizontal line across the page width
    $x1 = 10; // Starting X position (left margin)
    $x2 = $this->GetPageWidth() - 10; // Ending X position (right margin)
    $y = $this->GetY(); // Vertical position for the line
    $this->Line($x1, $y, $x2, $y);

    // Add a little space after the line
    $this->Ln(5);

    // Add student's photo on the far right (if available)
    //if ($this->student_photo && file_exists($this->student_photo)) {
    //    $this->Image($this->student_photo, $this->GetPageWidth() - 30, 5, 20); // Adjust position and size
    //}
  }

  // Footer
  function Footer() {
    $this->SetY(-15); // Position the footer 15mm from the bottom
    $this->SetFont('Arial', 'I', 8);

    // Get current date and time
    $date = date('d/m/Y'); // Format: day-month-year
    $time = date('H:i:s'); // Format: hours:minutes:seconds

    // Add date on the left side
    $this->Cell(100, 10, '' . $date, 0, 0, 'L');

    // Add time on the right side
    $this->Cell(0, 10, '' . $time, 0, 0, 'R');
  }

  // Rotate function
  function Rotate($angle, $x = -1, $y = -1) {
      if ($x == -1) $x = $this->x;
      if ($y == -1) $y = $this->y;
      if ($this->angle != 0) $this->_out('Q');
      $this->angle = $angle;
      if ($angle != 0) {
          $angle *= M_PI / 180;
          $c = cos($angle);
          $s = sin($angle);
          $cx = $x * $this->k;
          $cy = ($this->h - $y) * $this->k;
          $this->_out(sprintf('q %.2F %.2F %.2F %.2F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
      }
  }

  function RotatedText($x, $y, $txt, $angle) {
      $this->Rotate($angle, $x, $y);
      $this->Text($x, $y, $txt);
      $this->Rotate(0);
  }
}

// Create a new PDF instance
$pdf = new MyPDF();
$pdf->student_photo = $photo_path; // Pass the photo path dynamically
$pdf->AddPage();

// Query to get the student details
$student_details_result = $conn->query("SELECT * FROM students WHERE id = '$student_id'");
$student_details = $student_details_result->fetch_assoc();

// Query to get the current term
$current_term_result = $conn->query("SELECT * FROM currentterm WHERE id = 1");
$current_term = $current_term_result->fetch_assoc();

// Query to get the current term
$current_session_result = $conn->query("SELECT * FROM currentsession WHERE id = 1");
$current_session = $current_session_result->fetch_assoc();

$term = $current_term['cterm'];
$curr_session = $current_session['csession'];

// Query to get the class comments
$class_comments_result = $conn->query("SELECT * FROM classcomments WHERE id = '$student_id'");
$class_comments = $class_comments_result->fetch_assoc();

// Query to get the principal comment
$principal_comments_result = $conn->query("SELECT comment FROM principalcomments WHERE id = '$student_id'");
$principal_comment = $principal_comments_result->fetch_assoc();

// Query to get the next term
$next_term_result = $conn->query("SELECT Next FROM nextterm WHERE id = 1");
$next_term = $next_term_result->fetch_assoc()['Next'];

// Add content to the PDF
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 7, "Name:      " . $student_details['name'], 'B', 0); // Bottom border only
$pdf->Cell(95, 7, "SchlOpen:      " . $class_comments['schlopen'], 'B', 1); // Bottom border only


$pdf->Cell(95, 7, "Class:      " . $student_details['class'] . " " . $student_details['arm'], 'B', 0);
$pdf->Cell(95, 7, "Days Absent:  " . $class_comments['daysabsent'], 'B', 1); // Bottom border only

$pdf->Cell(95, 7, "Term:      " . $term, 'B', 0); // Bottom border only
$pdf->Cell(95, 7, "Days Present: " . $class_comments['dayspresent'], 'B', 1); // Bottom border only

$pdf->Cell(95, 7, "Session:  " . $curr_session, 'B', 0); // Bottom border only
$pdf->Cell(95, 7, "Next Term:      " . $next_term, 'B', 1); // Bottom border only

$pdf->Ln(5);

// Set background color to gray
$pdf->SetFillColor(255, 211, 244); // RGB values for gray

// Add results table heading
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(80, 25, 'SUBJECT', 1, 0, 'C', true);
$pdf->Cell(10, 25, 'CA1', 1, 0, 'C', true);
//$pdf->Cell(10, 25, 'CA2', 1, 0, 'C', true);

// Rotate the remaining headers
$x_start = $pdf->GetX(); // Starting X position for rotated headers
$y_start = $pdf->GetY(); // Y position for rotated headers
$rotated_headers = ['ASSIGNMENT','EXAM', 'LAST CUM', 'TOTAL', 'AVERAGE', 'GRADE','CLASS AVG.'];
$header_width = 8; // Width of each rotated header cell

foreach ($rotated_headers as $index => $header) {
    $x_pos = $x_start + ($index * $header_width);
    $pdf->Cell($header_width, 25, '', 1, 0, 'C', true); // Add cell for alignment
    $pdf->RotatedText($x_pos + 6, $y_start + 23, $header, 90); // Adjust the +6 for alignment
}

$pdf->Cell(44, 25, 'REMARK', 1, 0, 'C', true); // Remark header remains upright
$pdf->Ln();

// Add student results data
$pdf->SetFont('Arial', '', 8);
$results_result = $conn->query("SELECT * FROM mastersheet WHERE id = '$student_id' AND term = '$term' AND csession = '$curr_session'");

// Fetch class average scores for all subjects
$subject_averages_result = $conn->query("
    SELECT subject, AVG(total) AS avg_score 
    FROM mastersheet 
    WHERE class = '{$student_details['class']}' AND term = '$term' AND csession = '$curr_session'
    GROUP BY subject
");

// Store averages in an associative array for easy lookup
$subject_averages = [];
while ($avg_row = $subject_averages_result->fetch_assoc()) {
    $subject_averages[$avg_row['subject']] = ceil($avg_row['avg_score']); // Store as a rounded-up whole number
}

// Initialize variables for calculating the overall average
$total_average = 0;
$num_subjects = 0;

// Loop through student results
while ($row = $results_result->fetch_assoc()) {
    $subject = $row['subject'];
    $avg_score = isset($subject_averages[$subject]) ? $subject_averages[$subject] : '-'; // Fetch the class average for the subject

    $pdf->Cell(80, 5, $subject, 1, 0); // Subject
    $pdf->Cell(10, 5, $row['ca1'], 1, 0, 'C'); // CA1
    $pdf->Cell(8, 5, $row['ca2'], 1, 0, 'C'); // CA2
    $pdf->Cell(8, 5, $row['exam'], 1, 0, 'C'); // Exam
    $pdf->Cell(8, 5, $row['lastcum'], 1, 0, 'C'); // Last Cumulative
    $pdf->Cell(8, 5, $row['total'], 1, 0, 'C'); // Total
    $pdf->Cell(8, 5, $row['average'], 1, 0, 'C'); // Average
    $pdf->Cell(8, 5, $row['grade'], 1, 0, 'C'); // Grade
    $pdf->Cell(8, 5, $avg_score, 1, 0, 'C'); // Class Average (Rounded Up)
    $pdf->Cell(44, 5, $row['remark'], 1, 1, 'C'); // Remark

    // Sum up the average scores for overall calculation
    $total_average += $row['average'];
    $num_subjects++;
}

// Calculate overall average
if ($num_subjects > 0) {
    $overall_average = number_format($total_average / $num_subjects, 2);
} else {
    $overall_average = '0.00';
}

// Output overall average (optional)
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190, 7, "Overall Average: $overall_average", 1, 1, 'C');


$pdf->Ln(2); // Add some space before displaying the overall average

// Add class and principal comments at the bottom of the page// Set font for the table
$pdf->SetFont('Arial', 'I', 10);
// Row 2: Actual comment with only the bottom border
$pdf->Cell(0, 5, $class_comments['comment'], 'B', 1, 'C'); // Bottom border only
// Row 1: Title "Class Teacher's Comment"
$pdf->Cell(0, 5, "Class Teacher's Comment", 0, 1, 'C'); // No borders

$pdf->Ln(2); // Add some space before displaying the overall average

// Add class and principal comments at the bottom of the page// Set font for the table
$pdf->SetFont('Arial', 'I', 10);
// Row 2: Actual comment with only the bottom border
$pdf->Cell(0, 5, $principal_comment['comment'], 'B', 1, 'C'); // Bottom border only
// Row 1: Title "Class Teacher's Comment"
$pdf->Cell(0, 5, "Principal's Comment: ", 0, 1, 'C'); // No borders


$pdf->Ln(3); // Add some space before displaying the overall average



// Add space before the signature (right side)
$pdf->Cell(10, 2, '', 0, 0); // Empty cell for spacing

// Set the X position to align the image on the far right
$pdf->SetX(-40); // Adjust -50 to place the image correctly on the far right (you can adjust this value based on the margin)

// Add the principal's signature on the right side
$pdf->Image('assets/img/signature.jpg', $pdf->GetX(), $pdf->GetY(), 30); // Adjust 40 to fit the image size
$pdf->Ln(1);
$pdf->SetX(-30);
$pdf->Cell(10, -5, 'Principal`s Signature', 0, 1, 'C');

// Add some space before the grading table
$pdf->Ln(7);

// Set font for the grading system table
$pdf->SetFont('Arial', '', 11);

// Add Grading Table header
$pdf->Cell(60, 7, 'Grading Table', 1, 1, 'C', true);

// Set font for the table content
$pdf->SetFont('Arial', '', 10);

  // Create the table rows
  $grading_data = [
    ['A', '75 - 100', 'Excellent'],
      ['B', '65 - 74', 'Very Good'],
      ['C', '50 - 64', 'Good'],
      ['D', '45 - 49', 'Fair'],
      ['E', '40 - 44', 'Poor'],
      ['F', '0 - 39', 'Very Poor']
    ];

// Loop through each row of grading data
foreach ($grading_data as $row) {
    $pdf->Cell(10, 6, $row[0], 1, 0, 'C'); // First column: grade
    $pdf->Cell(20, 6, $row[1], 1, 0, 'C'); // Second column: grade range
    $pdf->Cell(30, 6, $row[2], 1, 1, 'C'); // Third column: description
}


// Output the PDF
$pdf->Output();

// End output buffering and flush
ob_end_flush();


//==============================================================

?>
