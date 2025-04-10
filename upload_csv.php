<?php
session_start();
error_reporting(1);
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

// Initialize alert variables with default success values.
$alertType    = 'success';
$alertTitle   = 'Success!';
$alertMessage = 'CSV file imported successfully.';

// Check if the CSV file was uploaded without errors
if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    $alertType    = 'error';
    $alertTitle   = 'Upload Error';
    $alertMessage = 'CSV file upload failed.';
} else {
    $csvFile = $_FILES['csvFile']['tmp_name'];

    // Retrieve additional metadata from the form
    $class       = isset($_POST['class'])   ? mysqli_real_escape_string($conn, $_POST['class']) : '';
    $arm         = isset($_POST['arm'])     ? mysqli_real_escape_string($conn, $_POST['arm']) : '';
    $term        = isset($_POST['term'])    ? mysqli_real_escape_string($conn, $_POST['term']) : '';
    $session_val = isset($_POST['session']) ? mysqli_real_escape_string($conn, $_POST['session']) : '';
    $subject = isset($_POST['subject']) ? mysqli_real_escape_string($conn, $_POST['subject']) : '';

    // Open the CSV file for reading
    if (($handle = fopen($csvFile, "r")) !== false) {
        $lineNumber = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $lineNumber++;

            // Optionally skip the header row if your CSV file includes one.
            if ($lineNumber == 1 && strtolower($data[0]) === 'test_id') {
                continue;
            }

            // Check if the CSV row has at least 8 columns
            if (count($data) < 7) {
                // Optionally, log or collect these errors.
                continue;
            }

            // Escape each column from the CSV
            
            $que_desc  = mysqli_real_escape_string($conn, $data[1]);
            
            $ans1      = mysqli_real_escape_string($conn, $data[2]);
            $ans2      = mysqli_real_escape_string($conn, $data[3]);
            $ans3      = mysqli_real_escape_string($conn, $data[4]);
            $ans4      = mysqli_real_escape_string($conn, $data[5]);
            $true_ans  = mysqli_real_escape_string($conn, $data[6]);

            // Insert the row into the database (ensure your table has these extra columns)
            $query = "INSERT INTO question (
                            subject, que_desc, ans1, ans2, ans3, ans4, true_ans, class, arm, term, session
                      ) VALUES (
                            '$subject', '$que_desc', '$ans1', '$ans2', '$ans3', '$ans4', '$true_ans', '$class', '$arm', '$term', '$session_val'
                      )";

            if (!mysqli_query($conn, $query)) {
                $alertType    = 'error';
                $alertTitle   = 'Error Importing CSV';
                $alertMessage = "Error on line $lineNumber: " . mysqli_error($conn);
                // Optionally, break on error if you don't want to continue processing.
                break;
            }
        }
        fclose($handle);
    } else {
        $alertType    = 'error';
        $alertTitle   = 'File Error';
        $alertMessage = 'Unable to open CSV file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSV Upload Result</title>
    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Include SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Display the SweetAlert2 modal with our alert variables
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo $alertTitle; ?>',
            text: '<?php echo addslashes($alertMessage); ?>',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        }).then((result) => {
            // Redirect immediately to questionadd.php after clicking OK
            window.location.href = 'questionadd.php';
        });
    </script>
</body>
</html>
