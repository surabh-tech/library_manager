<?php
// Set up MySQL connection
include("config.php");

// Check connection


// Function to register a new student
function registerStudent($id, $name, $branch, $conn)
{
    $sql = "INSERT INTO library.students_master (sid, fname, lname, smob, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssi", $sid, $fname, $lname, $smob, $status);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// API endpoint for student registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the request
    $sid = $_POST['sid'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $smob = $_POST['smob'];
    $status = $_POST['status'];


    // Register the student
    $registrationStatus = registerStudent($sid, $fname, $lname, $smob, $status, $con);
    
    $response = array();
    if ($registrationStatus) {
        $response['success'] = true;
        $response['message'] = "Student registered successfully!";
    } else {
        $response['success'] = false;
        $response['message'] = "Failed to register student.";
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the MySQL connection
$con->close();
?>
