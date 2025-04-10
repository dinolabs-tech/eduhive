<?php
include 'db_connect.php';

header('Content-Type: application/json');

$response = array();

if(isset($_POST['action']) && $_POST['action'] == 'get_student_by_idno'){
    $id_no = $conn->real_escape_string($_POST['id_no']);
    
    $query = "SELECT * FROM students WHERE id = '$id_no'";
    $result = $conn->query($query);
    
    if($result->num_rows > 0){
        $student = $result->fetch_assoc();
        $response['status'] = 'success';
        $response['data'] = array(
            'name' => $student['name'],
            'contact' => $student['mobile'],
            //'email' => $student['email'],
            'address' => $student['address']
        );
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No student found with this ID number';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid action';
}

echo json_encode($response);
?>