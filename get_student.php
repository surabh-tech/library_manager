<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to fetch all registered students
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to fetch all registered students
    $query = "SELECT * FROM library.students_master";
    $result = $conn->query($query);
    
    // Prepare the response
    $response = array();
    if ($result) {
        $students = array();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $response['success'] = true;
        $response['students'] = $students;
    } else {
        $response['success'] = false;
        $response['message'] = "Failed to fetch students.";
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the MySQL connection
$conn->close();
?>
