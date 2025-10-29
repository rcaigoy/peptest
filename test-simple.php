<?php
/**
 * Simple WordPress Load Test
 * Tests if WordPress loads correctly
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting WordPress load test...<br>";

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "WordPress loaded successfully!<br>";
echo "Current theme: " . wp_get_theme()->get('Name') . "<br>";

// Test theme switching
echo "<br>Testing theme list:<br>";
$themes = wp_get_themes();
foreach ($themes as $slug => $theme) {
    echo "- " . $slug . ": " . $theme->get('Name') . "<br>";
}

echo "<br>Test completed successfully!";
?>

