<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniProTracker - Enter Your AniList Username</title>
</head>
<body>
    <h1>Welcome to AniProTracker</h1>
    <p>Enter your AniList username to track your anime progress.</p>
    <p>wtf is going on</p>

    <form action="authorize.php" method="GET">
        <label for="username">AniList Username:</label>
        <input type="text" id="username" name="username" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
<?php
echo 'Client Secret: ' . htmlspecialchars($client_secret);
?>
