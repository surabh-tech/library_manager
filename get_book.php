<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to get book details and associated tags
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bookId = $_GET['bookId'];

    // Retrieve book details
    $selectBookQuery = "SELECT * FROM library.book_master1 WHERE id = ?";
    $stmt = $con->prepare($selectBookQuery);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();

        // Retrieve associated tags
        $tagsQuery = "SELECT tname FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = ?";
        $stmt = $con->prepare($tagsQuery);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $tagsResult = $stmt->get_result();

        $tags = [];
        while ($tagRow = $tagsResult->fetch_assoc()) {
            $tags[] = $tagRow['tname'];
        }

        // Assign tags array to book details
        $book['tags'] = $tags;

        // Close the statements
        $stmt->close();

        // Close the MySQL connection
        $con->close();

        // Return the data as JSON
        header('Content-Type: application/json');
        echo json_encode($book);
    } else {
        // Book not found
        http_response_code(404);
        echo json_encode(array("message" => "Book not found."));
    }
}
?>
