<?php
session_start();

// Retrieve the access token from the session
if (!isset($_SESSION['access_token'])) {
    echo "Error: Access token not found. Please log in again.";
    exit;
}

$access_token = $_SESSION['access_token'];

// AniList API GraphQL endpoint
$graphql_url = 'https://graphql.anilist.co';

// GraphQL query to fetch the user's anime list
$query = '
query ($userId: Int!, $type: MediaType!) {
    MediaListCollection(userId: $userId, type: $type) {
        lists {
            name
            entries {
                media {
                    title {
                        romaji
                        english
                    }
                    coverImage {
                        large
                    }
                }
            }
        }
    }
}';

// Replace with the actual AniList user ID
$variables = [
    'userId' => 206495,  // Replace with dynamic user data in the future
    'type' => 'ANIME',   // Fetch anime data
];

// Prepare headers for the API request
$headers = [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
];

// Initialize cURL for the API request
$ch = curl_init($graphql_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query, 'variables' => $variables]));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

// Decode the JSON response
$data = json_decode($response, true);
curl_close($ch);

// Check if the anime list data is available
if (isset($data['data']['MediaListCollection']['lists'])) {
    $animeLists = $data['data']['MediaListCollection']['lists'];

    echo "<h1>Welcome to Your Anime List</h1>";

    foreach ($animeLists as $list) {
        echo "<h2>" . htmlspecialchars($list['name']) . "</h2>";

        if (!empty($list['entries'])) {
            echo "<ul>";
            foreach ($list['entries'] as $entry) {
                $title = htmlspecialchars($entry['media']['title']['romaji'] ?? $entry['media']['title']['english']);
                $coverImage = htmlspecialchars($entry['media']['coverImage']['large']);

                echo "<li>";
                echo "<img src='$coverImage' alt='Anime Cover' width='50' height='70'>";
                echo "<strong>" . $title . "</strong>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No entries found in this list.</p>";
        }
    }
} else {
    echo "<p>Could not fetch the anime list. Please try again later.</p>";
    if (isset($data['errors'])) {
        echo "<pre>" . print_r($data['errors'], true) . "</pre>"; // Display API errors for debugging
    }
}
?>
