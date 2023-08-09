<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
</head>
<body>
    <h1>Add a New Book</h1>
    <form action="book_register.php" method="POST">
        <label for="bname">Book Name:</label>
        <input type="text" name="bname" required><br>
        
        <label for="aname">Author Name:</label>
        <input type="text" name="aname" required><br>
        
        <label for="price">Price:</label>
        <input type="text" name="price"  required><br>
        
        <label for="tags">Tags (comma-separated):</label>
        <input type="text" name="tags" required><br>
        
        <button type="submit">Add Book</button>
    </form>
</body>
</html>
