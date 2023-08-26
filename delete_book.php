<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to delete a book by tag name
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tagName = $_POST['tagName'];

    // Get book IDs associated with the tag
    $getBookIdsQuery = "SELECT DISTINCT tag_map.bid FROM library.tag_map INNER JOIN library.tag_master ON tag_map.tid = tag_master.id WHERE tag_master.tname = ?";
    $stmt = $con->prepare($getBookIdsQuery);
    $stmt->bind_param("s", $tagName);
    $stmt->execute();
    $bookIdsResult = $stmt->get_result();

    while ($row = $bookIdsResult->fetch_assoc()) {
        $bookId = $row['bid'];

        // Delete tag associations from tag_map
        $deleteTagMapQuery = "DELETE FROM library.tag_map WHERE bid = ?";
        $stmt = $con->prepare($deleteTagMapQuery);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();

        // Delete book from book_master1
        $deleteBookQuery = "DELETE FROM library.book_master1 WHERE id = ?";
        $stmt = $con->prepare($deleteBookQuery);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
    }

    // Close the statements
    $stmt->close();

    // Close the MySQL connection
    $con->close();

    echo "Books with tag '$tagName' and their associated data deleted successfully!";
}
?>
