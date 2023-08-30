<?php
// Set up MySQL connection
include("config.php");

// Check connection

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
            // Fetch tag IDs associated with the liked book
            $tagQuery = "SELECT tid FROM library.tag_map WHERE bid = ?";
            $stmtTagQuery = $con->prepare($tagQuery);
            if (!$stmtTagQuery) {
                die("Error preparing tag query: " . $con->error);
            }

            $stmtTagQuery->bind_param("i", $book_id);
            $stmtTagQuery->execute();
            $stmtTagQuery->bind_result($tag_id);

            // Fetch related books
            $relatedBooks = array();
            while ($stmtTagQuery->fetch()) {
                // Fetch book IDs that share the same tag ID
                $relatedBooksQuery = "SELECT bid FROM library.tag_map WHERE tid = ?";
                $stmtRelatedBooks = $con->prepare($relatedBooksQuery);
                if (!$stmtRelatedBooks) {
                    die("Error preparing related books query: " . $con->error);
                }

                $stmtRelatedBooks->bind_param("i", $tag_id);
                $stmtRelatedBooks->execute();
                $stmtRelatedBooks->bind_result($related_book_id);

                // Fetch book details for each related book and store in suggestions
                while ($stmtRelatedBooks->fetch()) {
                    if ($related_book_id != $book_id) {
                        $bookDetailsQuery = "SELECT * FROM library.book_master1 WHERE id = ?";
                        $stmtBookDetails = $con->prepare($bookDetailsQuery);
                        if (!$stmtBookDetails) {
                            die("Error preparing book details query: " . $con->error);
                        }

                        $stmtBookDetails->bind_param("i", $related_book_id);
                        $stmtBookDetails->execute();
                        $stmtBookDetails->bind_result($id, $bname, $aname, $price);
                        $stmtBookDetails->fetch();

                        // Store book details in the suggestions array
                        $relatedBooks[] = array(
                            "id" => $id,
                            "bname" => $bname,
                            "aname" => $aname,
                            "price" => $price
                        );

                        $stmtBookDetails->close(); // Close the statement after fetching
                    }
                }

                $stmtRelatedBooks->close(); // Close the statement after fetching
            }

            $stmtTagQuery->close(); // Close the statement after fetching

            $response = array(
                "success" => true,
                "message" => "Book liked successfully!",
                "suggestions" => $relatedBooks
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
