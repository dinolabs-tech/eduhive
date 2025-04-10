<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if session and term are set
if (isset($_GET['session']) && isset($_GET['term'])) {
    $csession = $_GET['session'];
    $currentterm = $_GET['term'];
    $term = $currentterm; // Set $term to the value of $currentterm
} else {
    header('Location: login.php');
    exit();
}

// Includes FPDF library and database connection
require('includes/fpdf.php');
include 'db_connection.php';

$user_id = $_SESSION['user_id'];

// Extend FPDF with custom header, footer, and a property for the student image
class PDF extends FPDF {
    public $studentImage; // Path to the student's image
    protected $angle = 0; // Initialize the angle property

    // Header
    function Header() {
        // Set font for the header
        $this->SetFont('Arial', 'B', 10);

        // Add school logo on the far left
        $this->Image('assets/img/logo.png', 10, 8, 20);  // Adjust position and size as needed

        // Add student image on the top right if available
        if (isset($this->studentImage)) {
            $x = $this->GetPageWidth() - 10 - 20; // right margin (10) + image width (20)
            $this->Image($this->studentImage, $x, 8, 20);
        }

        // School name (Centered)
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 5, 'Dinolabs Tech Services', 0, 1, 'C');

        $this->SetFont('Arial', 'B', 11);
        // School address (Centered)
        $this->Cell(0, 5, 'Suite 10, Wing B, 5th Floor, Tisco House, Alagbaka, Akure Ondo state.', 0, 1, 'C');

        // School email (Centered)
        $this->Cell(0, 5, 'schoolemail@gmail.com', 0, 1, 'C');

        // School mobile (Centered)
        $this->Cell(0, 5, '+234 813 772 6887', 0, 1, 'C');

        $this->Ln(5); // Space after header

        // Draw a horizontal line across the page
        $x1 = 10;
        $x2 = $this->GetPageWidth() - 10;
        $y = $this->GetY();
        $this->Line($x1, $y, $x2, $y);

        $this->Ln(5);
    }

    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);

        // Current date and time
        $date = date('d/m/Y');
        $time = date('H:i:s');

        // Date on the left
        $this->Cell(100, 10, $date, 0, 0, 'L');
        // Time on the right
        $this->Cell(0, 10, $time, 0, 0, 'R');
    }

    // Rotate function and RotatedText for any rotated headers
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

// Start output buffering
ob_start();

// Query to get the student details from mastersheet
$result = $conn->query("SELECT * FROM mastersheet WHERE id = '$user_id' and term = '$term' and csession = '$csession'");
$student_details = $result->fetch_assoc();

// Query to get student info from the students table
$result = $conn->query("SELECT * FROM students WHERE id = '$user_id'");
$student_photo = $result->fetch_assoc();

// Query for class comments and principal comments
$class_comments_result = $conn->query("SELECT * FROM classcomments WHERE id = '$user_id'");
$class_comments = $class_comments_result->fetch_assoc();

$principal_comments_result = $conn->query("SELECT comment FROM principalcomments WHERE id = '$user_id'");
$principal_comment = $principal_comments_result->fetch_assoc();

$next_term_result = $conn->query("SELECT Next FROM nextterm WHERE id = 1");
$next_term = $next_term_result->fetch_assoc()['Next'];

// Create the PDF
$pdf = new PDF();

// Get the student image using your filename method
$photo_filename = str_replace('/', '_', $student_photo['id']);  // e.g., wf_1000_24
$photo_path = "studentimg/" . $photo_filename . ".jpg";
if (!file_exists($photo_path)) {
    $photo_path = "studentimg/default.jpg"; // Fallback to default image
}
$pdf->studentImage = $photo_path;

$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// First row (Name / SchlOpen)
$pdf->Cell(95, 7, "Name:      " . $student_photo['name'], 'B', 0);
$pdf->Cell(10, 7, "", 0, 0);  // Add an empty cell to create extra space
$pdf->Cell(85, 7, "SchlOpen:      " . $class_comments['schlopen'], 'B', 1);

// Second row (Class / Days Absent)
$pdf->Cell(95, 7, "Class:      " . $student_photo['class'] . " " . $student_photo['arm'], 'B', 0);
$pdf->Cell(10, 7, "", 0, 0);
$pdf->Cell(85, 7, "Days Absent:  " . $class_comments['daysabsent'], 'B', 1);

// Third row (Term / Days Present)
$pdf->Cell(95, 7, "Term:      " . $term, 'B', 0);
$pdf->Cell(10, 7, "", 0, 0);
$pdf->Cell(85, 7, "Days Present: " . $class_comments['dayspresent'], 'B', 1);

// Fourth row (Session / Next Term)
$pdf->Cell(95, 7, "Session:  " . $csession, 'B', 0);
$pdf->Cell(10, 7, "", 0, 0);
$pdf->Cell(85, 7, "Next Term:      " . $next_term, 'B', 1);

$pdf->Ln(5);

// Set background color to gray for the results table header
$pdf->SetFillColor(255, 211, 244);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(80, 25, 'SUBJECT', 1, 0, 'C', true);
$pdf->Cell(10, 25, 'CA1', 1, 0, 'C', true);

// Rotated headers for remaining columns
$x_start = $pdf->GetX();
$y_start = $pdf->GetY();
$rotated_headers = ['ASSIGNMENT','EXAM', 'LAST CUM', 'TOTAL', 'AVERAGE', 'GRADE','CLASS AVG.'];
$header_width = 8;

foreach ($rotated_headers as $index => $header) {
    $x_pos = $x_start + ($index * $header_width);
    $pdf->Cell($header_width, 25, '', 1, 0, 'C', true);
    $pdf->RotatedText($x_pos + 6, $y_start + 23, $header, 90);
}

$pdf->Cell(44, 25, 'REMARK', 1, 0, 'C', true);
$pdf->Ln();

// Add student results data
$pdf->SetFont('Arial', '', 8);
$results_result = $conn->query("SELECT * FROM mastersheet WHERE id = '$user_id' AND term = '$term' AND csession = '$csession'");

// Fetch class average scores for each subject
$subject_averages_result = $conn->query("
    SELECT subject, AVG(total) AS avg_score 
    FROM mastersheet 
    WHERE class = '{$student_details['class']}' AND term = '$term' AND csession = '$csession'
    GROUP BY subject
");

$subject_averages = [];
while ($avg_row = $subject_averages_result->fetch_assoc()) {
    $subject_averages[$avg_row['subject']] = ceil($avg_row['avg_score']);
}

$total_average = 0;
$num_subjects = 0;

while ($row = $results_result->fetch_assoc()) {
    $subject = $row['subject'];
    $avg_score = isset($subject_averages[$subject]) ? $subject_averages[$subject] : '-';
  
    $pdf->Cell(80, 5, $subject, 1, 0);
    $pdf->Cell(10, 5, $row['ca1'], 1, 0, 'C');
    $pdf->Cell(8, 5, $row['ca2'], 1, 0, 'C');
    $pdf->Cell(8, 5, $row['exam'], 1, 0, 'C');
    $pdf->Cell(8, 5, $row['lastcum'], 1, 0, 'C');
    $pdf->Cell(8, 5, $row['total'], 1, 0, 'C');
    $pdf->Cell(8, 5, $row['average'], 1, 0, 'C');
    $pdf->Cell(8, 5, $row['grade'], 1, 0, 'C');
    $pdf->Cell(8, 5, $avg_score, 1, 0, 'C');
    $pdf->Cell(44, 5, $row['remark'], 1, 1, 'C');
  
    $total_average += $row['average'];
    $num_subjects++;
}

if ($num_subjects > 0) {
    $overall_average = number_format($total_average / $num_subjects, 2);
} else {
    $overall_average = '0.00';
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190, 7, "Overall Average: $overall_average", 1, 1, 'C');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 5, $class_comments['comment'], 'B', 1, 'C');
$pdf->Cell(0, 5, "Class Teacher's Comment", 0, 1, 'C');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 5, $principal_comment['comment'], 'B', 1, 'C');
$pdf->Cell(0, 5, "Principal's Comment: ", 0, 1, 'C');

$pdf->Ln(3);
$pdf->Cell(10, 2, '', 0, 0);
$pdf->SetX(-40);
$pdf->Image('assets/img/signature.jpg', $pdf->GetX(), $pdf->GetY(), 30);
$pdf->Ln(1);
$pdf->SetX(-30);
$pdf->Cell(10, -5, 'Principal`s Signature', 0, 1, 'C');

$pdf->Ln(7);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 7, 'Grading Table', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$grading_data = [
    ['A', '75 - 100', 'Excellent'],
    ['B', '65 - 74', 'Very Good'],
    ['C', '50 - 64', 'Good'],
    ['D', '45 - 49', 'Fair'],
    ['E', '40 - 44', 'Poor'],
    ['F', '0 - 39', 'Very Poor']
];

foreach ($grading_data as $row) {
    $pdf->Cell(10, 6, $row[0], 1, 0, 'C');
    $pdf->Cell(20, 6, $row[1], 1, 0, 'C');
    $pdf->Cell(30, 6, $row[2], 1, 1, 'C');
}

$pdf->Output();
ob_end_flush();
?>
