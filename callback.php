<?php
session_start();

// Define AniList API credentials
$client_id = "23612";
$client_secret = getenv('CLIENT_SECRET'); // Your client secret (from environment variable)
$redirect_uri = "https://aniprotracker.onrender.com/callback"; // Redirect URI

// Debugging: Output session to check if state exists
// Uncomment these lines if needed for debugging
 echo "State in session: " . $_SESSION['state'] . "<br>";
 echo "State in GET: " . $_GET['state'] . "<br>";

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

    // Check if there was an error during the cURL request
    if ($response === false) {
        echo "Error: cURL request failed.";
        exit;
    }

    // Decode the response to extract the access token and user info
    $response_data = json_decode($response, true);

    // Debugging: Print the API response for analysis
    // Uncomment these lines if needed
    // var_dump($response_data);

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

        // Check if there was an error during the cURL request
        if ($user_info_response === false) {
            echo "Error: cURL request failed while fetching user info.";
            exit;
        }

        // Decode the user info response
        $user_info = json_decode($user_info_response, true);

        // Debugging: Print the user info response for analysis
        // Uncomment these lines if needed
        // var_dump($user_info);

        if (isset($user_info['data']['Viewer']['id'])) {
            // Store the user ID in the session
            $_SESSION['user_id'] = $user_info['data']['Viewer']['id'];
            echo "Login successful. Redirecting to your profile...";
            header('Location: profile.php'); // Redirect to the profile page
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
?>
