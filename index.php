<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
</head>
<body>
    <h2>Add a New Book</h2>
    <form action="book_register.php" method="get">
        <label for="bname">Book Title:</label>
        <input type="text" id="bname" name="bname" required><br><br>

        <label for="aname">Author Name:</label>
        <input type="text" id="aname" name="aname" required><br><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="tags">Tags (comma-separated):</label>
        <input type="text" id="tags" name="tags"><br><br>

        <input type="submit" value="Add Book">
    </form>
</body>
</html>
