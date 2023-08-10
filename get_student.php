<?php
// Set up MySQL connection
include("config.php");

// Check connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

// API endpoint to fetch all registered students
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to fetch all registered students
    $query = "SELECT * FROM library.student_master";
    $result = $con->query($query);
    
    // Prepare the response
    $students = array(); // Initialize the students array
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    } else {
        // If failed to fetch students, you can include an error message if needed
        $students = array();
        $students['error'] = "Failed to fetch students.";
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($students); // Output only the students array
}

// Close the MySQL connection
$con->close();
?>
