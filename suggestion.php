<?php
// Set up MySQL connection
include("config.php");

// Check connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];

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
    
    $book_ids = array(); // Array to store multiple book_id values
    
    if ($stud_id) {
        // Fetch book_ids from stud_book_map based on the stud_id
        $selectBookIdsQuery = "SELECT book_id FROM library.stud_book_map WHERE stud_id = ?";
        $stmtSelectBookIds = $con->prepare($selectBookIdsQuery);
        if (!$stmtSelectBookIds) {
            die("Error preparing select query: " . $con->error);
        }

        $stmtSelectBookIds->bind_param("i", $stud_id);
        $stmtSelectBookIds->execute();
        $resultBookIds = $stmtSelectBookIds->get_result();
        
        // Fetch and store multiple book_ids
        $book_ids = [];
        while ($rowBookId = $resultBookIds->fetch_assoc()) {
            $book_ids[] = $rowBookId['book_id'];
        }
        
        $stmtSelectBookIds->close(); // Close the statement after fetching
        $book_ids = array_unique($book_ids);
        if (!empty($book_ids)) {
            // Fetch related book suggestions based on the list of book_ids
            $relatedBooks = array();
            
            // Prepare the tags query outside the loop since it's the same for all books
            $tagsQuery = "SELECT tag_master.id, tname FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = ?";
            $stmtTags = $con->prepare($tagsQuery);
            if (!$stmtTags) {
                die("Error preparing tags query: " . $con->error);
            }
            $stmtTags->bind_param("i", $tag_bid);
        
            foreach ($book_ids as $book_id) {
                // Fetch book details for each book_id and store in suggestions
                $bookDetailsQuery = "SELECT * FROM library.book_master WHERE id = ?";
                $stmtBookDetails = $con->prepare($bookDetailsQuery);
                if (!$stmtBookDetails) {
                    die("Error preparing book details query: " . $con->error);
                }
        
                $stmtBookDetails->bind_param("i", $book_id);
                $stmtBookDetails->execute();
                $stmtBookDetails->bind_result($id, $bname, $aname, $price);
                $stmtBookDetails->fetch();
                $stmtBookDetails->close(); // Close the statement after fetching
        
                // Execute the tags query for the current book_id
                $tag_bid = $book_id;
                $stmtTags->execute();
                $tagsResult = $stmtTags->get_result();
        
                $tags = [];
                while ($tagRow = $tagsResult->fetch_assoc()) {
                    $tags[] = array(
                        "id" => $tagRow['id'],
                        "name" => $tagRow['tname']
                    );
                }
        
                // Store book details in the suggestions array along with tags
                $relatedBooks[] = array(
                    "id" => $id,
                    "bname" => $bname,
                    "aname" => $aname,
                    "price" => $price,
                    "tags" => $tags
                );
            }
            $stmtTags->close(); // Close the statement after fetching tags
        
            // Fetch remaining books that are not in the suggested book_ids
            $remainingBooksQuery = "SELECT * FROM library.book_master WHERE id NOT IN (" . implode(",", $book_ids) . ")";
            $resultRemainingBooks = mysqli_query($con, $remainingBooksQuery);
            
            $remainingBooks = array();
            while ($rowRemainingBook = mysqli_fetch_assoc($resultRemainingBooks)) {
                $remainingBookId = $rowRemainingBook['id'];
            
                // Fetch associated tags for each remaining book
                $remainingTagsQuery = "SELECT tag_master.id, tname FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = $remainingBookId";
                $resultRemainingTags = mysqli_query($con, $remainingTagsQuery);
            
                $remainingTags = [];
                while ($tagRow = mysqli_fetch_assoc($resultRemainingTags)) {
                    $remainingTags[] = array(
                        "id" => $tagRow['id'],
                        "name" => $tagRow['tname']
                    );
                }
            
                // Store remaining book details and associated tags in the remainingBooks array
                $remainingBooks[] = array(
                    "id" => $remainingBookId,
                    "bname" => $rowRemainingBook['bname'],
                    "aname" => $rowRemainingBook['aname'],
                    "price" => $rowRemainingBook['price'],
                    "tags" => $remainingTags
                );
            }
        
            $response = array(
                "success" => true,
                "message" => "Book suggestions fetched successfully!",
                "suggestions" => $relatedBooks,
                "remaining_books" => $remainingBooks
            );
        } else {
            $response = array(
                "success" => false,
                "message" => "No book suggestions available for the user."
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

    // Close the MySQL connection
    $con->close();
}
?>
