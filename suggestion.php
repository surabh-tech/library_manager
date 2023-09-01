<?php
// Set up MySQL connection
include("config.php");

// Check connection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = 1;
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
        // Fetch book_ids and islike from stud_book_map based on the stud_id
        $selectBookIdsQuery = "SELECT book_id, islike FROM library.stud_book_map WHERE stud_id = ?";
        $stmtSelectBookIds = $con->prepare($selectBookIdsQuery);
        if (!$stmtSelectBookIds) {
            die("Error preparing select query: " . $con->error);
        }

        $stmtSelectBookIds->bind_param("i", $stud_id);
        $stmtSelectBookIds->execute();
        $resultBookIds = $stmtSelectBookIds->get_result();

        // Fetch and store multiple book_ids and their islike status
        $book_likes = array();
        while ($rowBookId = $resultBookIds->fetch_assoc()) {
            $book_likes[] = array(
                "book_id" => $rowBookId['book_id'],
                "islike" => $rowBookId['islike']
            );
        }

        $stmtSelectBookIds->close(); // Close the statement after fetching
        $book_ids = array_unique(array_column($book_likes, 'book_id'));
        if (!empty($book_ids)) {
            // Fetch related book suggestions based on the list of book_ids
            $relatedBooks = array();

            // Prepare the tags query outside the loop since it's the same for all books
            $tagsQuery = "SELECT tag_master.id, tag_name FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = ?";
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
                        "tag_name" => $tagRow['tag_name']
                    );
                }

                // Get islike status for the current book_id
                $islike = getIsLikeStatus($book_likes, $book_id);

                // Store book details in the suggestions array along with tags and islike status
                $relatedBooks[] = array(
                    "id" => $id,
                    "bname" => $bname,
                    "aname" => $aname,
                    "price" => $price,
                    "tag" => $tags,
                    "islike" => $islike
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
                $remainingTagsQuery = "SELECT tag_master.id, tag_name FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = $remainingBookId";
                $resultRemainingTags = mysqli_query($con, $remainingTagsQuery);

                $remainingTags = [];
                while ($tagRow = mysqli_fetch_assoc($resultRemainingTags)) {
                    $remainingTags[] = array(
                        "id" => $tagRow['id'],
                        "tag_name" => $tagRow['tag_name']
                    );
                }

                // Get islike status for the current remaining book_id
                $islike = getIsLikeStatus($book_likes, $remainingBookId);

                // Store remaining book details and associated tags in the remainingBooks array
                $remainingBooks[] = array(
                    "id" => $remainingBookId,
                    "bname" => $rowRemainingBook['bname'],
                    "aname" => $rowRemainingBook['aname'],
                    "price" => $rowRemainingBook['price'],
                    "tags" => $remainingTags,
                    "islike" => $islike
                );
            }

            shuffle($relatedBooks);
            shuffle($remainingBooks);

            $response = array(
                "success" => true,
                "message" => "Book suggestions fetched successfully!",
                "suggestions" => $relatedBooks,
                "remaining_books" => $remainingBooks
            );

        } else {
            $selectAllBooksQuery = "SELECT * FROM library.book_master";
            $allBooksResult = $con->query($selectAllBooksQuery);

            $books = [];

            while ($book = $allBooksResult->fetch_assoc()) {
                $bookId = $book['id'];

                // Retrieve associated tags for each book
                $tagsQuery = "SELECT tag_master.id, tag_name FROM library.tag_master INNER JOIN library.tag_map ON tag_master.id = tag_map.tid WHERE tag_map.bid = ?";
                $stmt = $con->prepare($tagsQuery);
                $stmt->bind_param("i", $bookId);
                $stmt->execute();
                $tagsResult = $stmt->get_result();

                $tags = [];
                while ($tagRow = $tagsResult->fetch_assoc()) {
                    $tags[] = array(
                        "id" => $tagRow['id'],
                        "tag_name" => $tagRow['tag_name']
                    );
                }

                // Get islike status for the current book_id
                $islike = getIsLikeStatus($book_likes, $bookId);

                // Assign tags array to the book details
                $book['tags'] = $tags;

                // Add the book details to the array
                $book['islike'] = $islike;
                $books[] = $book;
            }

            // Close the statements
            $stmt->close();

            // Close the MySQL connection
            //$con->close();

            // Return the data as JSON
            $count = 2;
            shuffle($books);

            header('Content-Type: application/json');
            echo json_encode($books);
        }
    } else {
        $response = array(
            "success" => false,
            "message" => "Invalid token."
        );
    }

    // Send the JSON response
    if ($count == 1) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    // Close the MySQL connection
    $con->close();
}

// Function to get the islike status for a specific book_id
function getIsLikeStatus($book_likes, $book_id)
{
    foreach ($book_likes as $book_like) {
        if ($book_like["book_id"] == $book_id) {
            return $book_like["islike"];
        }
    }
    return 0; // Return 0 if the book_id is not found in the array
}
?>
