<?php
session_start();  // Start the session

// Check if the access token exists in the session
if (!isset($_SESSION['access_token'])) {
    // If no access token, redirect the user to the login page
    header("Location: index.php");
    exit;
}

// Get the access token from the session
$access_token = $_SESSION['access_token'];

// AniList API endpoint to get the current user's information
$url = "https://graphql.anilist.co";

// The GraphQL query to fetch user details
$query = <<<QUERY
{
  Viewer {
    id
    name
    avatar {
      large
    }
    about
    statistics {
      chaptersRead
      booksRead
      animeWatched
    }
  }
}
QUERY;

// Set up the request headers, including the access token
$headers = [
    "Authorization: Bearer " . $access_token,
    "Content-Type: application/json"
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the request
$response = curl_exec($ch);

// Check for errors in the cURL request
if (!$response) {
    echo "cURL Error: " . curl_error($ch);
    exit;
}

curl_close($ch);

// Decode the response
$response_data = json_decode($response, true);

// Check if the response contains user data
if (isset($response_data['data']['Viewer'])) {
    $user = $response_data['data']['Viewer'];
} else {
    // If there is no user data, display an error
    echo "Error: Could not fetch user profile data.<br>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .profile-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: 50px;
        }
        .profile-container img {
            border-radius: 50%;
        }
        .profile-container h1 {
            margin-top: 20px;
        }
        .profile-container p {
            font-size: 1.2em;
            margin-top: 10px;
        }
        .statistics {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Display the user's profile picture -->
        <img src="<?php echo $user['avatar']['large']; ?>" alt="Profile Avatar" width="150" height="150">
        
        <!-- Display the user's name -->
        <h1><?php echo htmlspecialchars($user['name']); ?></h1>

        <!-- Display the user's "about" information -->
        <?php if (!empty($user['about'])): ?>
            <p><strong>About:</strong> <?php echo htmlspecialchars($user['about']); ?></p>
        <?php endif; ?>

        <!-- Display the user's statistics (optional) -->
        <div class="statistics">
            <p><strong>Anime Watched:</strong> <?php echo $user['statistics']['animeWatched']; ?></p>
            <p><strong>Chapters Read:</strong> <?php echo $user['statistics']['chaptersRead']; ?></p>
            <p><strong>Books Read:</strong> <?php echo $user['statistics']['booksRead']; ?></p>
        </div>
    </div>
</body>
</html>
