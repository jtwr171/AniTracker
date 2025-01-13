<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniProTracker - Enter Your AniList Username</title>
</head>
<body>
    <h1>AniProTracker V1</h1>
    <p>Enter your AniList username to enter the app.</p>

    <!-- Form to input AniList username -->
    <form action="authorize.php" method="GET">
        <label for="username">AniList Username:</label>
        <input type="text" id="username" name="username" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
