<?php
session_start();

// Check if the 'code' parameter is in the URL
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Get the client secret from the environment variable
    $client_secret = getenv('CLIENT_SECRET'); // Ensure you have this environment variable set in Render

    // Define AniList's token exchange URL
    $url = "https://anilist.co/api/v2/oauth/token";
    
    // Prepare the POST data to exchange the code for an access token
    $data = [
        'grant_type' => 'authorization_code',
        'client_id' => '23612',  // Your AniList client ID
        'client_secret' => $client_secret,  // Use client secret from the environment variable
        'redirect_uri' => 'https://aniprotracker.onrender.com/callback',  // Your callback URL
        'code' => $_GET['code']
    ];

    // Use cURL to send the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);

    // Check if the request was successful
    if (!$response) {
        echo "Error: " . curl_error($ch);
        exit;
    }

    curl_close($ch);

    // Decode the response (which contains the access token)
    $response_data = json_decode($response, true);

    // Check if the access token was returned
    if (isset($response_data['access_token'])) {
        // Store the access token in the session
        $_SESSION['access_token'] = $response_data['access_token'];

        // Redirect the user to the profile page (or wherever you want)
        header("Location: profile.php");
        exit;
    } else {
        // Handle any error, for example, missing access token
        echo "Error: Access token not found.";
        exit;
    }
} else {
    echo "Error: Code parameter missing in the URL.";
    exit;
}
?>
