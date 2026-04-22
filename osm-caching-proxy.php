<?php
/**
 * Configuration Settings
 */
// Directory for storing files (e.g. 'cache/')
$storage = 'cache/';
// Time in seconds for renewing the tiles
$ttl = 604800;
// Email address of the proxy operator
$operator = 'you@mail.com';

/**
 * Read Input Variables
 */
$z = $_GET['z'] ?? '';
$x = $_GET['x'] ?? '';
$y = $_GET['y'] ?? '';
// If any of the variables are empty or not numeric, terminate the script
if(!isset($z) || !isset($x) || !isset($y) || !is_numeric($z) || !is_numeric($x) || !is_numeric($y) || $z < 0 || $x < 0 || $y < 0) {
    die;
}

/**
 * Function to Download Tiles
 */
function download($storage, $z, $x, $y, $operator) {
    // Set maximum execution time to unlimited
    set_time_limit(0);
    // Define the source URL for the tile
    $source = 'https://tile.openstreetmap.org/' . $z . '/' . $x . '/' . $y . '.png';
    // Set timeout for the download
    $timeout = 30;
    // Open file handler for writing
    $fh = fopen($storage . $z . '/' . $x . '/' . $y . '.png', 'w');
    // Initialize cURL session
    $ch = curl_init();
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $source);
    curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'osm-caching-proxy, Operator: ' . $operator);
    // Execute cURL session
    curl_exec($ch);
    // Close cURL session
    curl_close($ch);
    // Close file handler
    fclose($fh);
}

// Check if directories exist and download tile
$tileDirectory = $storage . $z . '/' . $x;
if (!is_dir($tileDirectory)) {
    mkdir($tileDirectory, 0750, true);
}

$tilePath = $tileDirectory . '/' . $y . '.png';
if (file_exists($tilePath)) {
    // If the tile exists, check its age
    $age = filemtime($tilePath);
    // If the tile is older than the defined TTL, download a new one
    if (time() >= ($age + $ttl)) {
        download($storage, $z, $x, $y, $operator);
    }
} else {    
    // If the tile doesn't exist, download it
    download($storage, $z, $x, $y, $operator);
}

// Output the tile image
header('Content-Type: image/png');
readfile($storage . $z . '/' . $x . '/' . $y . '.png');