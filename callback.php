<?php
session_start();

// Debugging: Log the GET parameters
file_put_contents('debug.log', "GET Parameters:\n" . print_r($_GET, true), FILE_APPEND);
if ($result === false) {
    echo "Failed to write to log file.";
}

// AniList API credentials
$client_id = "23612";
$client_secret = getenv('CLIENT_SECRET');
$redirect_uri = "https://aniprotracker.onrender.com/callback";

// Validate the state parameter
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['state']) {
    echo "Error: Invalid state parameter.";
    exit;
}

// Get the authorization code
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

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);

    // Debugging: Log the raw API response
    file_put_contents('debug.log', "AniList API Response:\n" . print_r($response, true), FILE_APPEND);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $response_data = json_decode($response, true);

    if (isset($response_data['access_token'])) {
        $_SESSION['access_token'] = $response_data['access_token'];

        // Debugging: Log the session after setting access_token
        file_put_contents('debug.log', "Session Variables:\n" . print_r($_SESSION, true), FILE_APPEND);

        header('Location: profile.php');
        exit;
    } else {
        echo "Error: Failed to retrieve access token.";
        exit;
    }
} else {
    echo "Error: Authorization code not received.";
    exit;
    
}
