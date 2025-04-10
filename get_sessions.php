<?php
header('Content-Type: application/json');

// Database connection
include 'db_connection.php';


// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to get distinct session values from mastersheet table
$sql = "SELECT DISTINCT csession FROM mastersheet";
$result = $conn->query($sql);

$sessions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row['csession'];
    }
}

$conn->close();

echo json_encode(['sessions' => $sessions]);
?>
