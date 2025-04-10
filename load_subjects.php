<?php
header('Content-Type: application/json');
if (isset($_GET['class']) && isset($_GET['arm'])) {
    $class = $_GET['class'];
    $arm = $_GET['arm'];

    // Database connection and query
    include 'db_connection.php';
    $stmt = $conn->prepare("SELECT subject FROM subjects WHERE class = ? AND arm = ?");
    $stmt->bind_param("ss", $class, $arm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
    echo json_encode($subjects);
} else {
    echo json_encode([]);
}
?>
