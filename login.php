<?php
// Set up MySQL connection
include("config.php");

// Include JWT library
require 'vendor/autoload.php'; // Make sure to include the correct path to the JWT library

use Firebase\JWT\JWT;

// Function to check student login and generate JWT token
function studentLogin($stud_id, $password, $con)
{
    // Check student login
    $checkLoginQuery = "SELECT stud_id, password FROM login_master WHERE stud_id = ?";
    $stmtCheckLogin = $con->prepare($checkLoginQuery);
    $stmtCheckLogin->bind_param("s", $stud_id);
    $stmtCheckLogin->execute();
    $result = $stmtCheckLogin->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            // Student login is valid, generate JWT token
            $secret_key = "your_secret_key"; // Change this to your own secret key
            $payload = array(
                "stud_id" => $stud_id
            );
            $token = JWT::encode($payload, $secret_key, 'HS256'); // Use appropriate algorithm

            // Update login_master table with the generated token
            $updateTokenQuery = "UPDATE login_master SET token = ? WHERE stud_id = ?";
            $stmtUpdateToken = $con->prepare($updateTokenQuery);
            $stmtUpdateToken->bind_param("ss", $token, $stud_id);
            $stmtUpdateToken->execute();

            return $token;
        }
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $stud_id = $_POST['stud_id'];
    $password = $_POST['password'];
    
    $token = studentLogin($stud_id, $password, $con);
    
    $response = array();
    if ($token) {
        // Retrieve student details
        $retrieveStudentQuery = "SELECT sid,fname, lname, smob FROM library.student_master WHERE sid = ?";
        $stmtRetrieveStudent = $con->prepare($retrieveStudentQuery);
        $stmtRetrieveStudent->bind_param("s", $stud_id);
        $stmtRetrieveStudent->execute();
        $stmtRetrieveStudent->bind_result($sid,$aname, $lname, $mob);
        $stmtRetrieveStudent->fetch();
        $stmtRetrieveStudent->close();
        
        $response['success'] = true;
        $response['message'] = "Login successful!";
        $response['token'] = $token;
        $response['student_details'] = array(
            "sid"=> $sid,
            "aname" => $aname,
            "lname" => $lname,
            "mob" => $mob
        );
    } else {
        $response['success'] = false;
        $response['message'] = "Login failed.";
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the MySQL connection
$con->close();
?>
