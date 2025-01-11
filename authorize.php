<?php
// profile.php

require_once 'config.php';  // Include the config file that loads the .env

// Now you can use environment variables
$client_id = getenv('CLIENT_ID');
$client_secret = getenv('CLIENT_SECRET');

// Your code that needs access to the environment variables
 // Retrieve the AniList Client Secret from environment variables
$redirect_uri = "https://animeprotracker.onrender.com/callback";  // Local Redirect URI

$username = isset($_GET['username']) ? $_GET['username'] : '';  // Get the username

if ($username) {
    // Prepare the query parameters for the Authorization Code Grant
    $query = [
        'client_id' => $client_id,
        'response_type' => 'code',  // The response type should be 'code' for Authorization Code Grant
        'redirect_uri' => $redirect_uri,
        'state' => bin2hex(random_bytes(16)),  // Generate a random state for security
    ];

    // Build the authorization URL
    $url = 'https://anilist.co/api/v2/oauth/authorize?' . http_build_query($query);

    // Redirect the user to AniList for authentication
    header("Location: $url");
    exit;
} else {
    echo "Please provide a username.";
}
?>
//test