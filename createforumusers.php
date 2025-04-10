<?php

// Database connection
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Copy data from students table to users table
$sql = "INSERT INTO users (id, name, username, password)
        SELECT id, name, id, password FROM students
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            password = VALUES(password)";



if ($conn->query($sql) === TRUE) {
    //echo "Data copied successfully.";
} else {
    echo "Error: " . $conn->error;
}

// Copy data from students table to users table
$sql = "INSERT INTO users (id, name, username, password)
        SELECT id, staffname, username, password FROM login
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            password = VALUES(password)";



if ($conn->query($sql) === TRUE) {
    //echo "Data copied successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>
