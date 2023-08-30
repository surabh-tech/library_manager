<!DOCTYPE html>
<html>
<head>
    <title>Book Like and Suggestions</title>
</head>
<body>
    <h1>Book Like and Suggestions</h1>
    
    <form id="likeForm" method="post" action="suggestion.php">
        <label for="token">Token:</label>
        <input type="text" id="token" name="token"><br><br>
        
        <label for="bookId">Book ID:</label>
        <input type="text" id="bookId" name="bookId"><br><br>
        
        <input type="submit" value="Like Book">
    </form>
    
    <h2>Suggested Books:</h2>
    <ul id="suggestionsList">
        <!-- Suggestions will be displayed here -->
    </ul>
</body>
</html>
