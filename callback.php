<?php
// Add these debug lines at the very top of your callback.php file to check if the page is triggered
echo "Callback page triggered";
exit;

// Debugging: Output incoming parameters
var_dump($_GET);
exit;

// Your existing callback code below...
session_start();

if (!isset($_GET['code'])) {
    echo "Error: 'code' parameter missing in URL.";
    exit;
}

$code = $_GET['code'];
$client_secret = getenv('ANILIST_CLIENT_SECRET'); // Fetch client secret from environment

$url = "https://anilist.co/api/v2/oauth/token";

// Prepare data for token exchange
$data = [
    'grant_type' => 'authorization_code',
    'client_id' => '23612',  // AniList client ID
    'client_secret' => $client_secret, // AniList client secret
    'redirect_uri' => 'https://aniprotracker.onrender.com/callback',
    'code' => $code
];

// Send POST request to AniList API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
    exit;
}
curl_close($ch);

// Log the response (to check if access token is returned)
file_put_contents('anilist_api_response.log', $response);

// Decode the response
$response_data = json_decode($response, true);

// Check if access token is returned
if (isset($response_data['access_token'])) {
    // Store access token in session
    $_SESSION['access_token'] = $response_data['access_token'];

    // Redirect to profile page
    header("Location: profile.php");
    exit;
} else {
    // If no access token, output the response for debugging
    echo "Error: Access token not found. Response data: ";
    var_dump($response_data);
    exit;
}
?>
