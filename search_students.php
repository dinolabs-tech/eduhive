<?php
// Database connection
include 'db_connection.php';

// Check connection to the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search query and sanitize it
$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Build a query that searches both the students and login tables
$sql = "
    SELECT id, name FROM students WHERE name LIKE '%$query%'
    UNION
    SELECT id, username AS name FROM login WHERE username LIKE '%$query%'
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  // Output each matching entry as a clickable div with a data attribute for id
  while ($row = $result->fetch_assoc()) {
    echo "<div class='student' data-id='" . $row['id'] . "'>" . $row['name'] . "</div>";
  }
} else {
  echo "<div class='student'>No results found</div>";
}

$conn->close();
?>
