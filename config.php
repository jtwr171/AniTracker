// config.php
<?php
// Load .env file function
function loadEnv($file) {
    if (!file_exists($file)) {
        throw new Exception('Env file does not exist');
    }

    // Read the file into an array
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split the line by '=' into key and value
        list($name, $value) = explode('=', $line, 2);

        // Remove surrounding whitespace
        $name = trim($name);
        $value = trim($value);

        // Set environment variable
        putenv("$name=$value");
    }
}

// Load the .env file
loadEnv(__DIR__ . '/.env');
?>