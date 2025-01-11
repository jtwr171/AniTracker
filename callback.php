<?php
session_start();

// Get the authorization code from the URL
$code = isset($_GET['code']) ? $_GET['code'] : '';  // The 'code' parameter in the URL

if ($code) {
    // AniList token endpoint
    $token_url = 'https://anilist.co/api/v2/oauth/token';
    
    // Your AniList Client credentials
    $client_id = '23612';  // Your AniList Client ID
    $client_secret = 'your_client_secret';  // Your AniList Client Secret (replace with your actual client secret)
    $redirect_uri = 'https://aniprotracker.onrender.com/callback.php';  // Your Redirect URI

    // Data for the POST request to exchange the code for an access token
    $data = [
        'grant_type' => 'authorization_code',  // Grant type for Authorization Code
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'code' => $code,  // The authorization code
    ];

    // Use cURL to make a POST request to AniList's token endpoint
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);

    // Capture any cURL errors
    $curl_error = curl_error($ch);  // Get any cURL errors
    curl_close($ch);

    // Check if there was a cURL error
    if ($curl_error) {
        echo "cURL Error: " . $curl_error;
        exit;
    }

    // Decode the JSON response to get the access token
    $response_data = json_decode($response, true);

    if (isset($response_data['access_token'])) {
        // Store the access token in the session for later use
        $_SESSION['access_token'] = $response_data['access_token'];

        // Optionally, you can make an API request to fetch the user's information
        $access_token = $response_data['access_token'];

        // Corrected GraphQL query to fetch user's information
        $graphql_url = 'https://graphql.anilist.co';
        $graphql_query = json_encode([
            'query' => `
            {
                Viewer {
                    id
                    name
                    avatar {
                        large
                    }
                }
            }
            `
        ]);

        // cURL to fetch user data
        $ch = curl_init($graphql_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $graphql_query);
        $user_data = curl_exec($ch);

        // Capture any errors from this cURL request
        $graphql_error = curl_error($ch);
        curl_close($ch);

        // Check if there was a GraphQL cURL error
        if ($graphql_error) {
            echo "Error fetching user data: " . $graphql_error;
            exit;
        }

        // Decode the response and display user data
        $user_data = json_decode($user_data, true);

        // Debugging: Show the full response from GraphQL
        echo "<pre>";
        var_dump($user_data);
        echo "</pre>";

        if (isset($user_data['data']['Viewer'])) {
            $viewer = $user_data['data']['Viewer'];
            echo "<h1>Welcome, " . htmlspecialchars($viewer['name']) . "!</h1>";
            if (isset($viewer['avatar']['large'])) {
                echo "<img src='" . $viewer['avatar']['large'] . "' alt='Avatar'>";
            }
        } else {
            echo "Error fetching user data. Response: " . json_encode($user_data);
        }
    } else {
        // If access token was not returned, display the error response
        echo "Error: Could not retrieve access token. Response: " . json_encode($response_data);
    }
} else {
    echo "No authorization code received.";
}
?>
