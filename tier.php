<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo "You must be logged in to view your tier list.";
    exit;
}

// Access the user information from the session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Set the heading
$heading = "$username's Anime Tier List";

// Define the API URL
$api_url = 'https://graphql.anilist.co';

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

// Extract all the anime entries (combine all lists into one array)
$anime_list = [];
foreach ($response_data['data']['MediaListCollection']['lists'] as $list) {
    foreach ($list['entries'] as $entry) {
        $anime_list[] = $entry;
    }
}

// Organize anime into 21 tiers, including half-point ratings (1-10, 1.5-10.5, etc.)
$tiers = array_fill(0, 21, []); // Index 0 will be for unrated (0/10)

foreach ($anime_list as $entry) {
    $score = $entry['score'];

    // Handle unrated anime (null or 0 score)
    if ($score === null || $score == 0) {
        $score = -1; // Use -1 to denote unrated anime (this is the 0th index in $tiers)
    }

    // Check if the score is an integer or a float (i.e., has a .5)
    if (is_float($score)) {
        $tierIndex = (int) ($score * 2); // Convert to the appropriate index for .5 ratings
    } else {
        $tierIndex = $score * 2; // Convert to the index for integer scores
    }

    $tiers[$tierIndex][] = $entry; // Assign the anime to the correct tier
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $heading; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .tier-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .tier {
            width: 100%;
            min-width: 120px;
            border: 2px solid #ccc;
            padding: 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
            text-align: center;
            margin-bottom: 2px;
        }

        .tier h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .tier-images {
            display: flex;
            flex-wrap: wrap;
            gap: 3px; /* Ensure spacing between images */
        }

        .tier-images .anime-image {
            position: relative; /* For tooltip positioning */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .tier-images .anime-cover {
            width: 50px; /* Default image size */
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            z-index: 2; /* Image above the anchor tag */
        }

        .tier-images .anime-cover:hover {
            transform: scale(1.5); /* Enlarge image */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for emphasis */
            z-index: 10; /* Bring the image to the front */
        }

        .anime-tooltip {
            position: absolute;
            top: -30px; /* Position above the image */
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 14px;
            visibility: hidden;
            opacity: 0;
            white-space: nowrap;
            z-index: 15; /* Ensure tooltip appears above everything */
            transition: visibility 0.2s, opacity 0.2s;
        }

        .anime-image:hover .anime-tooltip {
            visibility: visible;
            opacity: 1;
        }

        .tier-empty {
            font-size: 16px;
            color: #888;
        }
        
        /* Anchor tag is transparent and ensures image is clickable */
        .anime-image a {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1; /* Anchor is below the image */
            background: transparent; /* Make the anchor tag fully transparent */
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">
            <?php echo $heading; ?>
        </h1>

        <!-- Save as Image button -->
        <button id="saveImageBtn" class="btn btn-primary mt-4">Save as Image</button>

        <div class="tier-container">
            <!-- Loop through tiers from 10/10 down to 1/10 and then unrated at the bottom -->
            <?php for ($i = 20; $i >= 0; $i--): ?>
                <div class="tier" id="tier-<?php echo $i; ?>" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <h3><?php 
                        if ($i == -1) {
                            echo "Unrated"; // For unrated entries (index -1)
                        } else {
                            $tier = $i / 2; // Convert index back to the score
                            if ($i % 2 == 1) {
                                echo $tier . "/10"; // For .5 scores (e.g., 7.5/10)
                            } else {
                                echo (int) $tier . "/10"; // For integer scores (e.g., 8/10)
                            }
                        }
                    ?></h3>
                    <div class="tier-images">
                        <?php if (!empty($tiers[$i])): ?>
                            <?php foreach ($tiers[$i] as $entry): ?>
                                <div class="anime-image" draggable="true" ondragstart="drag(event)" id="anime-<?php echo $entry['media']['id']; ?>">
                                    <a href="https://anilist.co/anime/<?php echo $entry['media']['id']; ?>" target="_blank"></a>
                                    <img src="<?php echo $entry['media']['coverImage']['large']; ?>" alt="Anime Image" class="anime-cover">
                                    <div class="anime-tooltip"><?php echo $entry['media']['title']['romaji']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="tier-empty">No anime in this tier.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- html2canvas script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>

    <script>
   document.getElementById("saveImageBtn").addEventListener("click", function() {
    const userConfirmed = confirm("Your image is ready to download. Do you want to proceed?");
    
    if (userConfirmed) {
        const tierListContainer = document.querySelector(".tier-container");

        html2canvas(tierListContainer, {
            backgroundColor: "#fff", // Set background color to white
            scale: 2, // Optional: scale the image for better quality
            logging: false, // Disable logging in the console
            useCORS: true, // Use CORS for cross-origin images
            allowTaint: true, // Allow tainted canvas (useful for cross-origin)
            foreignObjectRendering: true, // Allows better rendering of non-HTML elements
        }).then(function(canvas) {
            const imageUrl = canvas.toDataURL("image/png");
            console.log("Generating canvas...");
html2canvas(tierListContainer).then(function(canvas) {
    const imageUrl = canvas.toDataURL("image/png");
    console.log("Image URL generated:", imageUrl); // Log image URL
    // Proceed with the download code here...
});

            const link = document.createElement("a");
            link.href = imageUrl;
            link.download = "<?php echo $username; ?>_tier_list.png"; // Filename for download
            
            // Triggering the click programmatically
            document.body.appendChild(link);  // Ensure link is in the DOM
            link.click();  // Trigger the download

            // Remove the link after download
            document.body.removeChild(link);
        }).catch(function(error) {
            console.error("Error generating image:", error); // Log errors if any
        });
    }
});


    </script>
</body>
</html>
