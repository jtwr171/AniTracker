<?php
session_start();
$access_token = $_SESSION['access_token'];  // Retrieve access token from session

// AniList API URL
$graphql_url = 'https://graphql.anilist.co';

// GraphQL query to fetch user's profile and anime list (with `MediaListCollection`)
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

// Variables to send in the request
$variables = [
    'userId' => 206495,  // Replace with actual user ID
    'type' => 'ANIME'    // You can also use 'MANGA' if you need manga data
];

$headers = [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
];

// Set up cURL to send the request
$ch = curl_init($graphql_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query, 'variables' => $variables]));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    $data = json_decode($response, true);

    // Check if data exists before accessing it
    if (isset($data['data']['MediaListCollection']['lists'])) {
        $animeLists = $data['data']['MediaListCollection']['lists'];

        echo "<h1>Hello, Welcome to your Anime List!</h1>";

        // Loop through the anime lists and display entries
        foreach ($animeLists as $list) {
            echo "<h2>" . $list['name'] . "</h2>";

            if (isset($list['entries']) && count($list['entries']) > 0) {
                echo "<ul>";
                foreach ($list['entries'] as $entry) {
                    $title = $entry['media']['title']['romaji'] ?? $entry['media']['title']['english'];
                    $coverImage = $entry['media']['coverImage']['large'];

                    echo "<li>";
                    echo "<img src='" . $coverImage . "' alt='Anime Cover' width='50' height='70'>";
                    echo "<strong>" . $title . "</strong>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No entries found in this list.</p>";
            }
        }
    } else {
        echo "<p>Could not fetch the anime list.</p>";
    }
}

curl_close($ch);
?>
