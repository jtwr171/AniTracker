<?php
session_start();

// Start output buffering to prevent header issues
ob_start();

// Define AniList API credentials
$client_id = "23612"; // Your client ID
$client_secret = getenv('CLIENT_SECRET'); // Your client secret
$redirect_uri = "https://aniprotracker.onrender.com/callback.php"; // Redirect URI

// Check if the code and state parameters are set
if (isset($_GET['code']) && isset($_GET['state'])) {
    $code = $_GET['code'];
    $state = $_GET['state'];

    // Verify that the state matches to prevent CSRF attacks
    if ($_SESSION['state'] !== $state) {
        echo "Error: Invalid state.";
        exit;
    }

    // Prepare the data to exchange the authorization code for an access token
    $data = [
        'grant_type' => 'authorization_code',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
    ];

    // Initialize cURL for the token request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://anilist.co/api/v2/oauth/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Execute the request and get the response
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the response to extract the access token and user info
    $response_data = json_decode($response, true);

    if (isset($response_data['access_token'])) {
        // Store the access token and user ID in the session
        $_SESSION['access_token'] = $response_data['access_token'];

        // Get user info (optional but good for debugging)
        $user_info_url = 'https://graphql.anilist.co';
        $user_info_query = '{
            Viewer {
                id
                name
            }
        }';

        // Prepare the GraphQL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $response_data['access_token'],
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $user_info_query]));

        // Execute the GraphQL request to get user info
        $user_info_response = curl_exec($ch);
        curl_close($ch);

        // Decode the user info response
        $user_info = json_decode($user_info_response, true);

        if (isset($user_info['data']['Viewer']['id'])) {
            // Store the user ID in the session
            $_SESSION['user_id'] = $user_info['data']['Viewer']['id'];
            $_SESSION['username'] = $user_info['data']['Viewer']['name'];
            echo "Login successful. Redirecting to your profile...";

            // Redirect to profile.php
            header('Location: profile.php');
            exit;
        } else {
            echo "Error: Could not fetch user data.";
        }
    } else {
        echo "Error: Could not retrieve access token.";
    }
} else {
    echo "Error: Missing authorization code or state.";
}

// End output buffering and send the output
ob_end_flush();
?>
