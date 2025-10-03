<?php
/**
 * Show Roles from Database
 * This will show you exactly what's stored in the database
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

// Security check
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this script.');
}

global $wpdb;

// Get table prefix
$table_prefix = $wpdb->prefix;
$option_name = $table_prefix . 'user_roles';

echo "<h1>WordPress Roles Database Information</h1>";
echo "<hr>";

echo "<h2>Database Details:</h2>";
echo "<ul>";
echo "<li><strong>Database Name:</strong> " . DB_NAME . "</li>";
echo "<li><strong>Table Prefix:</strong> <code>" . $table_prefix . "</code></li>";
echo "<li><strong>Options Table:</strong> <code>" . $table_prefix . "options</code></li>";
echo "<li><strong>Roles Option Name:</strong> <code>" . $option_name . "</code></li>";
echo "</ul>";

echo "<hr>";

// Get roles from database
$roles_data = get_option($option_name);

echo "<h2>Roles Stored in Database:</h2>";
echo "<p>This is the raw data from: <code>SELECT option_value FROM " . $table_prefix . "options WHERE option_name = '" . $option_name . "'</code></p>";

if ($roles_data) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Role Slug</th>";
    echo "<th>Role Name</th>";
    echo "<th>Capabilities Count</th>";
    echo "</tr>";
    
    foreach ($roles_data as $role_slug => $role_info) {
        $cap_count = count($role_info['capabilities']);
        echo "<tr>";
        echo "<td><code>" . esc_html($role_slug) . "</code></td>";
        echo "<td>" . esc_html($role_info['name']) . "</td>";
        echo "<td>" . $cap_count . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>Full Raw Data (Serialized):</h2>";
    echo "<textarea style='width: 100%; height: 300px; font-family: monospace; font-size: 12px;'>";
    echo print_r($roles_data, true);
    echo "</textarea>";
    
} else {
    echo "<p style='color: red;'>ERROR: Could not retrieve roles from database!</p>";
}

echo "<hr>";
echo "<h2>SQL Query to View Roles:</h2>";
echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
echo "SELECT option_value FROM " . $table_prefix . "options WHERE option_name = '" . $option_name . "';";
echo "</pre>";

echo "<h2>SQL Query to Delete Specific Role:</h2>";
echo "<p>To manually delete a role from database, you would need to:</p>";
echo "<ol>";
echo "<li>Get the current serialized data</li>";
echo "<li>Unserialize it</li>";
echo "<li>Remove the role from the array</li>";
echo "<li>Serialize it back</li>";
echo "<li>Update the database</li>";
echo "</ol>";
echo "<p><strong>It's safer to use WordPress functions like <code>remove_role()</code></strong></p>";

echo "<hr>";
echo "<p><a href='force-cleanup-roles.php' style='display: inline-block; background: #d63638; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>🔥 Force Cleanup Roles</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
    h1 { color: #333; }
    h2 { color: #0073aa; margin-top: 30px; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    table { margin: 20px 0; }
    th { text-align: left; }
</style>

