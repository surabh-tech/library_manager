<?php
// Set up MySQL connection
include("config.php");

// Check connection

// API endpoint to add a new book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bname = $_POST['bname'];
    $aname = $_POST['aname'];
    $price = $_POST['price'];
    $tags = $_POST['tags'];

    // Add book to book_master table
    $insertBookQuery = "INSERT INTO library.book_master1 (bname, aname, price,tag) VALUES (?, ?, ?,?)";
    $stmt = $con->prepare($insertBookQuery);
    // Prepare the statement
$stmt = $con->prepare($insertBookQuery);
if (!$stmt) {
    echo "Prepare error: " . $con->error;
} else {
    // Bind the parameters
    $stmt->bind_param("ssss", $bname, $aname, $price, $tags);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Book added successfully!";
    } else {
        echo "Execute error: " . $stmt->error;
    }

    // Close the statement
   
}

    $stmt->bind_param("ssss", $bname, $aname, $price,$tags);

    if ($stmt->execute()) {
        $bookId = $stmt->insert_id;

        // Insert tags into tag_master if not already present
        $tagsArr = explode(",", $tags);
        foreach ($tagsArr as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $insertTagQuery = "INSERT IGNORE INTO library.tag_master (tname) VALUES (?)";
                $stmtTag = $con->prepare($insertTagQuery);
                $stmtTag->bind_param("s", $tag);
                $stmtTag->execute();
            }
        }

        // Associate tags with the book in tag_map table
        foreach ($tagsArr as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $selectTagIdQuery = "SELECT id FROM library.tag_master WHERE tname = ?";
                $stmtSelectTagId = $con->prepare($selectTagIdQuery);
                $stmtSelectTagId->bind_param("s", $tag);
                $stmtSelectTagId->execute();
                $stmtSelectTagId->bind_result($tagId);
                $stmtSelectTagId->fetch();
                $stmtSelectTagId->close();

                $insertTagMapQuery = "INSERT INTO tag_map (tid, tname, bid, bname) VALUES (?, ?, ?, ?)";
                $stmtTagMap = $con->prepare($insertTagMapQuery);
                $stmtTagMap->bind_param("isss", $tagId, $tag, $bookId, $bname);
                $stmtTagMap->execute();
            }
        }

        // Prepare the response
        $response = array();
        $response['success'] = true;
        $response['message'] = "Book added successfully!";
    } else {
        $response['success'] = false;
        $response['message'] = "Failed to add book.";
    }

    // Send the JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the MySQL connection
$con->close();
?>
