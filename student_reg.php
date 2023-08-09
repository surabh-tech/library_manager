<?php
// Set up MySQL connection
include("config.php");

// Check connection


// Function to register a new student
function registerStudent($id, $name, $branch, $conn)
{
    $sql = "INSERT INTO library.students_master (id, name, branch) VALUES (?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $id, $name, $branch);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// API endpoint for student registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the request
    $id = $_POST['id'];
    $name = $_POST['name'];
    $branch = $_POST['branch'];

    // Validate the data (You can add more validation if required)

    // Register the student
    $registrationStatus = registerStudent($id, $name, $branch, $conn);
    
    // Prepare the response
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
