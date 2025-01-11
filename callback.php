<?php
session_start();  // Start the session

// Enable error reporting for debugging purposes (remove this in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if 'code' and 'state' are set in the GET request
if (isset($_GET['code']) && isset($_GET['state'])) {
    // Debugging: Show the values of 'code' and 'state'
    echo "Code: " . $_GET['code'] . "<br>";
    echo "State: " . $_GET['state'] . "<br>";

    // Check if the state matches the one in the session
    if ($_GET['state'] === $_SESSION['state']) {
        // State matches, proceed with exchanging the code for an access token

        $code = $_GET['code'];
        $client_id = "your_client_id";  // Replace with your AniList Client ID
        $client_secret = "your_client_secret";  // Replace with your AniList Client Secret
        $redirect_uri = "https://your_redirect_uri";  // Replace with your Redirect URI

        // Prepare the API request to exchange the authorization code for an access token
        $url = "https://anilist.co/api/v2/oauth/token";
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'redirect_uri' => $redirect_uri
        ];

        // Use cURL to make the API request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);

        // Check for errors in the cURL request
        if (!$response) {
            echo "cURL Error: " . curl_error($ch);
            exit;
        }

        // Decode the JSON response
        $response_data = json_decode($response, true);

        // Debugging: Show the raw API response
        var_dump($response_data);

        // Check if the response contains an access token
        if (isset($response_data['access_token'])) {
            // Store the access token and other user data in the session
            $_SESSION['access_token'] = $response_data['access_token'];
            $_SESSION['user'] = $response_data['user'];

            // Debugging: Log the success message
            echo "Access token successfully received!<br>";
            echo "User data: <pre>";
            var_dump($response_data['user']);
            echo "</pre>";

            // Redirect the user to their profile page or a dashboard
            header("Location: profile.php");
            exit;
        } else {
            // If no access token is returned, display an error
            echo "Error: Could not get the access token.<br>";
            error_log("Error: Could not get the access token. Response: " . print_r($response_data, true));
        }
    } else {
        // If the state doesn't match, display an error and log it
        echo "State mismatch! Possible CSRF attack.<br>";
        error_log("State mismatch. Expected: " . $_SESSION['state'] . ", Got: " . $_GET['state']);
    }
} else {
    // If 'code' or 'state' are not set in the GET request, display an error
    echo "Error: Missing 'code' or 'state' parameters in the URL.<br>";
    error_log("Error: Missing 'code' or 'state' parameters.");
}
