<?php
session_start();

// Check if the user is logged in by verifying the access token
if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) {
    $access_token = $_SESSION['access_token'];

    // Use the access token to fetch the user's profile from AniList
    $url = "https://graphql.anilist.co";
    $query = '
    {
        viewer {
            id
            name
            avatar {
                large
            }
        }
    }';

    $variables = [];
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    // Send the request to AniList's GraphQL API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query, 'variables' => $variables]));

    $response = curl_exec($ch);

    // Check if the request was successful
    if (!$response) {
        echo "Error: " . curl_error($ch);
        exit;
    }

    curl_close($ch);

    // Decode the response (which contains the user data)
    $user_data = json_decode($response, true);

    // Check if the user data was returned successfully
    if (isset($user_data['data']['viewer'])) {
        $viewer = $user_data['data']['viewer'];
        echo "<h1>Welcome, " . htmlspecialchars($viewer['name']) . "!</h1>";
        echo "<img src='" . htmlspecialchars($viewer['avatar']['large']) . "' alt='Avatar'>";
    } else {
        echo "Error: Could not retrieve user data.";
        exit;
    }
} else {
    echo "Error: User not logged in.";
    exit;
}
?>
