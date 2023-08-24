<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to add a new book
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bname = $_GET['bname'];
    $aname = $_GET['aname'];
    $price = $_GET['price'];
    $tags = $_GET['tags'];

    // Add book to book_master table
    $insertBookQuery = "INSERT INTO library.book_master1 (bname, aname, price) VALUES (?, ?, ?)";
    $stmt = $con->prepare($insertBookQuery);
    if (!$stmt) {
        echo "Prepare error: " . $con->error;
    } else {
        // Bind the parameters
        $stmt->bind_param("sss", $bname, $aname, $price);

        // Execute the statement
        if ($stmt->execute()) {
            $bookId = $stmt->insert_id;

            // Process tags
            $tagsArr = explode(",", $tags);
            foreach ($tagsArr as $tag) {
                $tag = trim($tag);

                if (!empty($tag)) {
                    // Check if tag already exists in tag_master
                    $tagId = getTagId($con, $tag);

                    // If tag doesn't exist, insert into tag_master
                    if (!$tagId) {
                        $tagId = insertTag($con, $tag);
                    }

                    // Insert tag association into tag_map
                    if ($tagId) {
                        insertTagMap($con, $tagId, $bookId);
                    }
                }
            }

            echo "Book added successfully!";
        } else {
            echo "Execute error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Close the MySQL connection
    $con->close();
}

function getTagId($con, $tag) {
    $selectTagIdQuery = "SELECT id FROM library.tag_master WHERE tname = ?";
    $stmt = $con->prepare($selectTagIdQuery);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $stmt->bind_result($tagId);
    $stmt->fetch();
    $stmt->close();
    return $tagId;
}

function insertTag($con, $tag) {
    $insertTagQuery = "INSERT INTO library.tag_master (tname) VALUES (?)";
    $stmt = $con->prepare($insertTagQuery);
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    $tagId = $stmt->insert_id;
    $stmt->close();
    return $tagId;
}

function insertTagMap($con, $tagId, $bookId) {
    $insertTagMapQuery = "INSERT INTO library.tag_map (tid, bid) VALUES (?, ?)";
    $stmt = $con->prepare($insertTagMapQuery);
    $stmt->bind_param("ii", $tagId, $bookId);
    $stmt->execute();
    $stmt->close();
}
?>
