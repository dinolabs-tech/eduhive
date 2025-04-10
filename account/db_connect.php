<?php
//$servername = "localhost";
//$username = "wexfordc_root";
//$password = "foxtrot2november";
//$dbname = "wexfordc_portal2";
//$conn = new mysqli($servername, $username, $password, $dbname);

//if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portal";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
