<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to get details of all books and their associated tags
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve all books
    $selectAllBooksQuery = "SELECT * FROM library.book_master1";
    $allBooksResult = $con->query($selectAllBooksQuery);

    $books = [];

    while ($book = $allBooksResult->fetch_assoc()) {
        $bookId = $book['id'];

        // Retrieve associated tags for each book
        $tagsQuery = "SELECT tname FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = ?";
        $stmt = $con->prepare($tagsQuery);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $tagsResult = $stmt->get_result();

        $tags = [];
        while ($tagRow = $tagsResult->fetch_assoc()) {
            $tags[] = $tagRow['tname'];
        }

        // Assign tags array to the book details
        $book['tags'] = $tags;

        // Add the book details to the array
        $books[] = $book;
    }

    // Close the statements
    $stmt->close();

    // Close the MySQL connection
    $con->close();

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($books);
}
?>
