<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to delete a book by ID
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = $_POST['bookId'];

    // Delete tag associations from tag_map
    $deleteTagMapQuery = "DELETE FROM library.tag_map WHERE bid = ?";
    $stmt = $con->prepare($deleteTagMapQuery);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $stmt->close();

    // Delete book from book_master1
    $deleteBookQuery = "DELETE FROM library.book_master1 WHERE id = ?";
    $stmt = $con->prepare($deleteBookQuery);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $stmt->close();

    // Close the MySQL connection
    $con->close();

    echo "Book with ID '$bookId' and its associated data deleted successfully!";
}
?>
