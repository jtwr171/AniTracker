<?php
session_start();

// Specify the absolute path for the debug log file
$log_file = 'C:/Users/Admin/Downloads/AniTracker-main/AniTracker-main/debug.log';

// Debugging: Log the GET parameters
file_put_contents($log_file, "GET Parameters:\n" . print_r($_GET, true), FILE_APPEND);

// AniList API credentials
$client_id = "23612";
$client_secret = getenv('CLIENT_SECRET'); // Retrieve from environment variables (replace with actual key if needed)
$redirect_uri = "https://aniprotracker.onrender.com/callback"; // Make sure this matches your actual redirect URI

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
    file_put_contents($log_file, "AniList API Response:\n" . print_r($response, true), FILE_APPEND);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    // Decode the JSON response
    $response_data = json_decode($response, true);

    // Debugging: Log the decoded response
    file_put_contents($log_file, "Decoded Response Data:\n" . print_r($response_data, true), FILE_APPEND);

    if (isset($response_data['access_token'])) {
        $_SESSION['access_token'] = $response_data['access_token'];

        // Debugging: Log the session after setting access_token
        file_put_contents($log_file, "Session Variables:\n" . print_r($_SESSION, true), FILE_APPEND);

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
?>
