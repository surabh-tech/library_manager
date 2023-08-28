<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
</head>
<body>
    <h1>Student Login</h1>
    <form action="login.php" method="post">
        <label for="stud_id">Student ID:</label>
        <input type="text" id="stud_id" name="stud_id" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
