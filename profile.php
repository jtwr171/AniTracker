<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo "You must be logged in to view your profile.";
    exit;
}

// Access the user information from the session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Set the heading
$heading = "$username 's profile";

// Define the API URL
$api_url = 'https://graphql.anilist.co';

// Get the selected status from the dropdown or default to 'completed'
$status = isset($_GET['status']) ? $_GET['status'] : 'completed';

// Mapping the selected status to a list index
$status_map = [
   'completed' => 0, // Completed list
    'planning' => 1, // Planning list
    'paused' => 2, // Paused list
    'dropped' => 3, // Dropped list
    'watching' => 4, // Watching list

];

// Get the list index corresponding to the selected status
$list_index = isset($status_map[strtolower($status)]) ? $status_map[strtolower($status)] : 0;

// Get the search query, if any
$search_query = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// GraphQL query to fetch the media list with ratings and images
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
        score
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

// Extract the media list for the selected status
$media_list = isset($response_data['data']['MediaListCollection']['lists'][$list_index]['entries']) ?
    $response_data['data']['MediaListCollection']['lists'][$list_index]['entries'] : [];

// Filter the media list based on the search query, if provided
if ($search_query) {
    $media_list = array_filter($media_list, function ($entry) use ($search_query) {
        return strpos(strtolower($entry['media']['title']['romaji']), $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Anime List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .anime-cover {
            max-width: 80px;
            height: auto;
            border-radius: 5px;
        }
        .anime-title {
            font-weight: bold;
            color: #007bff;
        }
        .anime-title:hover {
            text-decoration: underline;
        }
        .anime-score {
            font-weight: bold;
            color: #28a745;
        }
        .no-media {
            font-size: 1.5rem;
            color: #dc3545;
            text-align: center;
        }
        .nav-link {
            color: #007bff !important;
        }
        .nav-link:hover {
            color: #0056b3 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Navigation Menu -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="#">AniProTracker</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="tier.php">Tier List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="findanime.php">Find an Anime</a>
                    </li>
                </ul>
            </div>
        </nav>

        <h1 class="mb-4">
            <?php echo $heading; ?>
        </h1>
        
        <!-- Display the count of completed anime -->
        <p class="text-muted">
            <?php echo count($media_list) . ' anime(s) in ' . ucfirst($status) . ' list'; ?>
        </p>

        <!-- Search Bar -->
        <form method="GET" class="mb-4">
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" name="search" placeholder="Search anime..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col">
                    <select class="form-control" name="status">
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="watching" <?php echo $status == 'watching' ? 'selected' : ''; ?>>Watching</option>
                        <option value="dropped" <?php echo $status == 'dropped' ? 'selected' : ''; ?>>Dropped</option>
                        <option value="paused" <?php echo $status == 'paused' ? 'selected' : ''; ?>>Paused</option>
                        <option value="planning" <?php echo $status == 'planning' ? 'selected' : ''; ?>>Planning</option>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <?php if (count($media_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Cover</th>
                            <th scope="col">Title</th>
                            <th scope="col">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($media_list as $index => $entry): ?>
                            <tr>
                                <th scope="row"><?php echo $index + 1; ?></th>
                                <td>
                                    <img src="<?php echo $entry['media']['coverImage']['large']; ?>" 
                                         alt="<?php echo htmlspecialchars($entry['media']['title']['romaji']); ?>" 
                                         class="anime-cover">
                                </td>
                                <td>
                                    <a href="https://anilist.co/anime/<?php echo $entry['media']['id']; ?>" 
                                       class="anime-title" 
                                       target="_blank">
                                        <?php echo htmlspecialchars($entry['media']['title']['romaji']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($entry['score'] !== null && $entry['score'] > 0): ?>
                                        <span class="anime-score"><?php echo $entry['score']; ?>/10</span>
                                    <?php else: ?>
                                        <span class="text-muted">- / 10</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-media">No anime found.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
