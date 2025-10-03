<?php
define('WP_USE_THEMES', false);
require('./wp-load.php');

echo "=== WordPress Debug Info ===\n\n";

// Check permalink structure
echo "Permalink Structure: " . get_option('permalink_structure') . "\n";
echo "Rewrite Rules Enabled: " . (get_option('permalink_structure') ? 'Yes' : 'No') . "\n\n";

// Check if mod_rewrite is working
echo "Testing URL Rewriting:\n";
global $wp_rewrite;
echo "Using Permalinks: " . ($wp_rewrite->using_permalinks() ? 'Yes' : 'No') . "\n";
echo "Using Index Permalinks: " . ($wp_rewrite->using_index_permalinks() ? 'Yes' : 'No') . "\n\n";

// Check query vars
echo "Registered Query Vars:\n";
global $wp;
foreach ($wp->public_query_vars as $var) {
    if (strpos($var, 'newsroom') !== false) {
        echo "  - " . $var . "\n";
    }
}

// Test the specific rewrite rule
echo "\nTesting 'register' route:\n";
$rules = get_option('rewrite_rules');
if (isset($rules['^register/?$'])) {
    echo "  Rule exists: ^register/?$ => " . $rules['^register/?$'] . "\n";
} else {
    echo "  Rule NOT found!\n";
}

// Check .htaccess
echo "\n.htaccess file exists: " . (file_exists('.htaccess') ? 'Yes' : 'No') . "\n";
if (file_exists('.htaccess')) {
    echo ".htaccess is writable: " . (is_writable('.htaccess') ? 'Yes' : 'No') . "\n";
}

// Force update permalink structure
echo "\nForcing permalink structure update...\n";
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules(true);
echo "Done!\n";

