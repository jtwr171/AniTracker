<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id'])) {
    echo "You must be logged in to view your profile.";
    exit;
}

// API URL to fetch the user's media list
$api_url = 'https://graphql.anilist.co';

// GraphQL query to fetch the media list with images
$query = '{
  MediaListCollection(type: ANIME, userId: ' . $_SESSION['user_id'] . ') {
    lists {
      name
      entries {
        id
        media {
          id
          title {
            romaji
          }
          coverImage {
            large
          }
        }
      }
    }
  }
}';

// Make the API request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['access_token'],
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));

// Get the response
$response = curl_exec($ch);
curl_close($ch);

// Decode the response
$response_data = json_decode($response, true);

// Check for errors
if (isset($response_data['errors'])) {
    echo "Error: Could not fetch your media list.";
    exit;
}

// Extract the completed list entries
$completed_list = $response_data['data']['MediaListCollection']['lists'][0]['entries']; // Assuming it's the first list
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Anime List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .count {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .anime-entry {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            background-color: #fff;
        }

        .anime-entry img {
            width: 50px; /* Set image to be half the previous size */
            height: auto;
            margin-right: 15px;
            border-radius: 5px;
        }

        .anime-entry .title {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .anime-entry:hover {
            background-color: #e7f1f9;
        }

        .no-media {
            text-align: center;
            font-size: 1.2rem;
            color: #e74c3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

    </style>
</head>
<body>

    <h1>Your Completed Anime List</h1>
    
    <!-- Display the count of completed anime -->
    <div class="count">
        <?php echo count($completed_list) . ' anime(s) completed'; ?>
    </div>

    <?php if (count($completed_list) > 0): ?>
        <div class="anime-list">
            <?php foreach ($completed_list as $entry): ?>
                <div class="anime-entry">
                    <img src="<?php echo $entry['media']['coverImage']['large']; ?>" alt="<?php echo htmlspecialchars($entry['media']['title']['romaji']); ?>">
                    <div class="title"><?php echo htmlspecialchars($entry['media']['title']['romaji']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-media">No completed anime found.</p>
    <?php endif; ?>
</body>
</html>
