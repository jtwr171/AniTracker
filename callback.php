<?php
session_start();

// profile.php

//require_once 'config.php';  // Include the config file that loads the .env

// Now you can use environment variables
$client_id = getenv('CLIENT_ID');
$client_secret = getenv('CLIENT_SECRET');

// Your code that needs access to the environment variables

$redirect_uri = "https://aniprotracker.onrender.com/callback";  // Your Redirect URI

// Get the authorization code from the callback URL
$code = isset($_GET['code']) ? $_GET['code'] : '';  // The 'code' parameter in the URL

if ($code) {
    // Exchange the authorization code for an access token
    $url = 'https://anilist.co/api/v2/oauth/token';
    $data = [
        'grant_type' => 'authorization_code',  // Specify the authorization code grant type
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
    curl_close($ch);

    // Decode the JSON response
    $response_data = json_decode($response, true);

    if (isset($response_data['access_token'])) {
        // Store the access token in the session
        $_SESSION['access_token'] = $response_data['access_token'];

        // Redirect the user to the profile page (or wherever you want to display their data)
        header('Location: profile.php');
        exit;
    } else {
        echo "Error retrieving access token.";
    }
} else {
    echo "No authorization code received.";
}
?>
