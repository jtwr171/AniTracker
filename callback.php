<?php

$client_id = "23612";  // Your AniList Client ID
$client_secret = getenv('client_secret');  // Retrieve the AniList Client Secret from environment variables
$redirect_uri = "https://aniprotracker.onrender.com/callback";  // Local Redirect URI

// Get the authorization code from the callback URL
$code = isset($_GET['code']) ? $_GET['code'] : '';  // The 'code' parameter in the URL

// Validate the presence of the authorization code
if ($code) {
    // Prepare the data to exchange the authorization code for an access token
    $url = 'https://anilist.co/api/v2/oauth/token';
    $data = [
        'grant_type' => 'authorization_code',  // Authorization Code Grant
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'code' => $code,
    ];

    // Initialize cURL to send a POST request to AniList
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Execute the cURL request and capture the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    } else {
        // Log the response to see what AniList is sending back
        echo "<h2>Response from AniList:</h2>";
        echo "<pre>" . print_r(json_decode($response, true), true) . "</pre>";

        // Decode the JSON response from AniList
        $response_data = json_decode($response, true);

        // Check if the access token is present in the response
        if (isset($response_data['access_token'])) {
            // Store the access token securely (e.g., in session or database)
            $access_token = $response_data['access_token'];

            echo "<h1>Access Token Retrieved Successfully!</h1>";
            echo "<p>Your access token is: <pre>$access_token</pre></p>";

            // Optionally, make an API request using the access token
            // Example: Fetch user's data using GraphQL (like in your callback.php example)
        } else {
            // Display an error message if no access token is returned
            echo "<h2>Error: Access token not found in the response</h2>";
            echo "<pre>" . print_r($response_data, true) . "</pre>";
        }
    }

    // Close cURL session
    curl_close($ch);
} else {
    echo "<h2>Error: No authorization code received</h2>";
}
?>
