<?php
define('WP_USE_THEMES', false);
require('./wp-load.php');

echo "Current Site URL: " . get_option('siteurl') . "\n";
echo "Current Home URL: " . get_option('home') . "\n";
echo "\n";

// Check if plugin is active
$active_plugins = get_option('active_plugins');
echo "Active Plugins:\n";
foreach ($active_plugins as $plugin) {
    echo "  - " . $plugin . "\n";
}
echo "\n";

// Check rewrite rules
global $wp_rewrite;
$rules = get_option('rewrite_rules');
echo "Rewrite rules for 'login' and 'register':\n";
if (is_array($rules)) {
    foreach ($rules as $pattern => $rewrite) {
        if (strpos($pattern, 'login') !== false || strpos($pattern, 'register') !== false) {
            echo "  Pattern: " . $pattern . " => " . $rewrite . "\n";
        }
    }
}

// Update site URLs if needed
$correct_url = 'http://localhost/Upwork/David/newsroomsim';
$current_siteurl = get_option('siteurl');
$current_home = get_option('home');

if ($current_siteurl !== $correct_url || $current_home !== $correct_url) {
    echo "\nUpdating URLs to: " . $correct_url . "\n";
    update_option('siteurl', $correct_url);
    update_option('home', $correct_url);
    echo "URLs updated!\n";
} else {
    echo "\nURLs are already correct.\n";
}

// Flush rewrite rules
flush_rewrite_rules();
echo "\nRewrite rules flushed!\n";

