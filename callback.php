<?php
session_start();

// AniList API credentials
$client_id = "23612";
$client_secret = getenv('CLIENT_SECRET'); // Retrieved from environment variables
$redirect_uri = "https://aniprotracker.onrender.com/callback"; // Redirect URI

// Validate the received state parameter
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['state']) {
    echo "Error: Invalid state parameter.";
    exit;
}

// Get the authorization code from the callback URL
$code = isset($_GET['code']) ? $_GET['code'] : null;

if ($code) {
    // Exchange the authorization code for an access token
    $url = 'https://anilist.co/api/v2/oauth/token';
    $data = [
        'grant_type' => 'authorization_code',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'code' => $code,
    ];

    // Make the POST request to AniList
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    // Decode the JSON response
    $response_data = json_decode($response, true);

    if ($http_code === 200 && isset($response_data['access_token'])) {
        // Store the access token in the session
        $_SESSION['access_token'] = $response_data['access_token'];

        // Redirect the user to the profile page
        header('Location: profile.php');
        exit;
    } else {
        echo "Error: Failed to retrieve access token.<br>";
        echo "HTTP Code: $http_code<br>";
        echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
        exit;
    }
} else {
    echo "Error: Authorization code not received.";
    exit;
}
?>
