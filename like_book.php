<?php
// Set up MySQL connection
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $book_id = $_POST['bookId'];

    // Fetch stud_id from login_master based on the token
    $selectStudIdQuery = "SELECT stud_id FROM library.login_master WHERE token = ?";
    $stmtSelectStudId = $con->prepare($selectStudIdQuery);
    if (!$stmtSelectStudId) {
        die("Error preparing select query: " . $con->error);
    }

    $stmtSelectStudId->bind_param("s", $token);
    $stmtSelectStudId->execute();
    $stmtSelectStudId->bind_result($stud_id);
    $stmtSelectStudId->fetch();
    $stmtSelectStudId->close(); // Close the statement after fetching

    if ($stud_id) {
        // Insert the like into stud_book_map
        $insertLikeQuery = "INSERT INTO library.stud_book_map (stud_id, book_id) VALUES (?, ?)";
        $stmtInsertLike = $con->prepare($insertLikeQuery);
        if (!$stmtInsertLike) {
            die("Error preparing insert query: " . $con->error);
        }

        $stmtInsertLike->bind_param("ii", $stud_id, $book_id);
        if ($stmtInsertLike->execute()) {
            $response = array(
                "success" => true,
                "message" => "Book liked successfully!"
            );
        } else {
            $response = array(
                "success" => false,
                "message" => "Failed to like the book."
            );
        }
    } else {
        $response = array(
            "success" => false,
            "message" => "Invalid token."
        );
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the MySQL connection
$con->close();
?>
