<?php
session_start();

// Define AniList API credentials
$client_id = "23612";
$client_secret = getenv('CLIENT_SECRET'); // Hardcoded client_secret for now
$redirect_uri = "https://aniprotracker.onrender.com/callback.php"; // The redirect URI registered in AniList OAuth settings

// Get the username from the query parameters
$username = isset($_GET['username']) ? $_GET['username'] : '';

if ($username) {
    // Generate a random state for CSRF protection
    $state = bin2hex(random_bytes(16));
    $_SESSION['state'] = $state; // Store the state in the session for later verification

    // Build the query parameters for the authorization URL
    $query = [
        'client_id' => $client_id,
        'response_type' => 'code', // We are requesting an authorization code
        'redirect_uri' => $redirect_uri, // Callback URL for the AniList redirect
        'state' => $state, // Including the CSRF state for security
    ];

    // Build the AniList authorization URL
    $url = 'https://anilist.co/api/v2/oauth/authorize?' . http_build_query($query);

    // Redirect the user to AniList for authorization
    header("Location: $url");
    exit;
} else {
    echo "Error: Please provide a username.";
}
?>
