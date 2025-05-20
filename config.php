<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petpal";

// Create connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If we're in development mode, show the error
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        echo "Connection failed: " . $e->getMessage();
    } else {
        // In production, show a generic error
        echo "We're experiencing technical difficulties. Please try again later.";
    }
    die();
}

// Site configuration
$siteConfig = [
    'site_name' => 'PetPal Adoption Center',
    'site_url' => 'https://petpal.example',
    'admin_email' => 'admin@petpal.example',
    'items_per_page' => 12,
    'social_media' => [
        'facebook' => 'https://facebook.com/petpal',
        'twitter' => 'https://twitter.com/petpal',
        'instagram' => 'https://instagram.com/petpal'
    ]
];

// Define default image paths if pet images don't exist
$defaultImages = [
    'Dog' => 'https://images.pexels.com/photos/1805164/pexels-photo-1805164.jpeg',
    'Cat' => 'https://images.pexels.com/photos/1170986/pexels-photo-1170986.jpeg',
    'Bird' => 'https://images.pexels.com/photos/1661179/pexels-photo-1661179.jpeg',
    'Small Pet' => 'https://images.pexels.com/photos/4383761/pexels-photo-4383761.jpeg',
    'Fish' => 'https://images.pexels.com/photos/128756/pexels-photo-128756.jpeg',
    'Reptile' => 'https://images.pexels.com/photos/2062316/pexels-photo-2062316.jpeg',
    'default' => 'https://images.pexels.com/photos/1108099/pexels-photo-1108099.jpeg'
];

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('UTC');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);