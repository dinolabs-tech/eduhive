<?php
// Database connection
include 'db_connection.php';


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['class'])) {
    $selected_class = $_POST['class'];
    $subject_sql = "SELECT subject FROM subject WHERE class = '$selected_class' group by subject"; // Adjust this query based on your subject table structure
    $subject_result = $conn->query($subject_sql);

    $subjects = [];
    if ($subject_result->num_rows > 0) {
        while ($row = $subject_result->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
    }

    echo '<select class="form-control form-select" id="subject" name="subject" required>';
    echo '<option value="">Select Subject</option>';
    foreach ($subjects as $subject) {
        echo '<option value="' . htmlspecialchars($subject) . '">' . htmlspecialchars($subject) . '</option>';
    }
    echo '</select>';
}

$conn->close();
?>
