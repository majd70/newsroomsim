<?php
define('WP_USE_THEMES', false);
require('./wp-load.php');

echo "=== User Roles in the System ===\n\n";

if (!function_exists('wp_roles')) {
    function wp_roles() {
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        return $wp_roles;
    }
}

$all_roles = wp_roles()->roles;

foreach ($all_roles as $role_key => $role_info) {
    echo "Role: " . $role_key . "\n";
    echo "Display Name: " . $role_info['name'] . "\n";
    echo "Capabilities:\n";
    foreach ($role_info['capabilities'] as $cap => $enabled) {
        if ($enabled) {
            echo "  - " . $cap . "\n";
        }
    }
    echo "\n";
}

// Count users by role
echo "=== User Count by Role ===\n\n";
foreach ($all_roles as $role_key => $role_info) {
    $users = get_users(array('role' => $role_key));
    echo $role_info['name'] . " (" . $role_key . "): " . count($users) . " users\n";
}

