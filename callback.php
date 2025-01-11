<?php
session_start();

// Define client details and redirect URI
$client_id = "23612";
$client_secret = getenv('CLIENT_SECRET'); // Retrieve from environment variables
$redirect_uri = "https://aniprotracker.onrender.com/callback"; // Your Redirect URI

// Check if the authorization code is present
if (!isset($_GET['code']) || empty($_GET['code'])) {
    echo "Error: No authorization code received.";
    exit;
}

// Get the authorization code from the callback URL
$code = $_GET['code'];

// Exchange the authorization code for an access token
$url = 'https://anilist.co/api/v2/oauth/token';
$data = [
    'grant_type' => 'authorization_code', // Specify the authorization code grant type
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'code' => $code,
];

// Initialize cURL to send a POST request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Decode the JSON response
$response_data = json_decode($response, true);

// Check if the access token is present in the response
if (isset($response_data['access_token'])) {
    // Store the access token in the session
    $_SESSION['access_token'] = $response_data['access_token'];

    // Optionally store other data from the response (e.g., refresh token, user info)
    if (isset($response_data['refresh_token'])) {
        $_SESSION['refresh_token'] = $response_data['refresh_token'];
    }

    // Redirect the user to the profile page
    header('Location: profile.php');
    exit;
} else {
    // Handle API errors
    echo "Error retrieving access token.";
    echo "<pre>";
    print_r($response_data); // Debugging info
    echo "</pre>";
    exit;
}
?>
