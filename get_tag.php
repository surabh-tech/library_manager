<?php
include("config.php");
$sql = "SELECT * FROM tag_master";
$result = mysqli_query($con, $sql);

// Fetch data and convert to an array
$tags = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tags[] = $row;
}

// Close the database connection
mysqli_close($con);

// Output data in JSON format directly without the "tags" key
echo json_encode($tags);
?>
