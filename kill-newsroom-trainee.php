<?php
/**
 * Kill newsroom_trainee Role - Nuclear Option
 * This will hunt down and destroy the newsroom_trainee role from everywhere
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

// Security check
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this script.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Kill newsroom_trainee Role</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #d63638; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 4px; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; font-family: monospace; }
        .button { display: inline-block; background: #d63638; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 10px 5px 10px 0; font-weight: bold; border: none; cursor: pointer; font-size: 16px; }
        .button:hover { background: #a02020; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔥 Kill newsroom_trainee Role</h1>

<?php
if (isset($_GET['kill']) && $_GET['kill'] === 'yes') {
    echo '<div class="danger"><h2>🔥 EXECUTING NUCLEAR OPTION...</h2></div>';

    global $wpdb;
    $table_prefix = $wpdb->prefix;
    $option_name = $table_prefix . 'user_roles';

    // Step 1: Show current state
    echo '<h2>Step 1: Current State</h2>';
    $roles_before = get_option($option_name);
    echo '<p>Roles in database:</p><ul>';
    foreach ($roles_before as $slug => $data) {
        $highlight = ($slug === 'newsroom_trainee') ? ' style="color:red; font-weight:bold;"' : '';
        echo "<li$highlight><code>$slug</code> - {$data['name']}</li>";
    }
    echo '</ul>';

    // Step 2: Remove using WordPress function
    echo '<h2>Step 2: Remove using remove_role()</h2>';
    for ($i = 1; $i <= 5; $i++) {
        remove_role('newsroom_trainee');
        echo "<p>✓ Called remove_role('newsroom_trainee') - Attempt $i</p>";
    }

    // Step 3: Remove directly from database
    echo '<h2>Step 3: Remove directly from database</h2>';
    $roles_data = get_option($option_name);
    
    if (isset($roles_data['newsroom_trainee'])) {
        echo '<p style="color:red;"><strong>⚠️ newsroom_trainee FOUND in database!</strong></p>';
        unset($roles_data['newsroom_trainee']);
        update_option($option_name, $roles_data);
        echo '<p style="color:green;"><strong>✓ DELETED newsroom_trainee from database!</strong></p>';
    } else {
        echo '<p style="color:green;"><strong>✓ newsroom_trainee NOT found in database</strong></p>';
    }

    // Step 4: Verify removal
    echo '<h2>Step 4: Verify Removal</h2>';
    $roles_after = get_option($option_name);
    echo '<p>Roles remaining in database:</p><ul>';
    $found = false;
    foreach ($roles_after as $slug => $data) {
        if ($slug === 'newsroom_trainee') {
            $found = true;
            echo "<li style='color:red; font-weight:bold;'><code>$slug</code> - {$data['name']} ❌ STILL HERE!</li>";
        } else {
            echo "<li><code>$slug</code> - {$data['name']}</li>";
        }
    }
    echo '</ul>';

    if (!$found) {
        echo '<div class="success"><h3>✅ SUCCESS!</h3><p>newsroom_trainee has been completely removed from the database!</p></div>';
    } else {
        echo '<div class="danger"><h3>❌ FAILED!</h3><p>newsroom_trainee is STILL in the database. This is very unusual.</p></div>';
    }

    // Step 5: Clear ALL caches
    echo '<h2>Step 5: Clear All Caches</h2>';
    wp_cache_flush();
    delete_transient('wp_user_roles');
    wp_cache_delete('alloptions', 'options');
    wp_cache_delete($option_name, 'options');
    
    // Force WordPress to reload roles
    global $wp_roles;
    $wp_roles = null;
    $wp_roles = new WP_Roles();
    
    echo '<p>✓ wp_cache_flush()</p>';
    echo '<p>✓ delete_transient()</p>';
    echo '<p>✓ wp_cache_delete()</p>';
    echo '<p>✓ WP_Roles reloaded</p>';

    // Step 6: Check WP_Roles object
    echo '<h2>Step 6: Check WP_Roles Object</h2>';
    echo '<p>Roles in WP_Roles object:</p><ul>';
    foreach ($wp_roles->roles as $slug => $data) {
        if ($slug === 'newsroom_trainee') {
            echo "<li style='color:red; font-weight:bold;'><code>$slug</code> ❌ STILL IN WP_ROLES!</li>";
        } else {
            echo "<li><code>$slug</code></li>";
        }
    }
    echo '</ul>';

    // Step 7: Final instructions
    echo '<div class="warning">';
    echo '<h2>⚠️ CRITICAL: You MUST do this now:</h2>';
    echo '<ol style="font-size: 18px; line-height: 2;">';
    echo '<li><strong>CLOSE this browser tab</strong></li>';
    echo '<li><strong>CLOSE your entire browser</strong> (all windows)</li>';
    echo '<li><strong>Wait 5 seconds</strong></li>';
    echo '<li><strong>Open browser again</strong></li>';
    echo '<li><strong>Log back in to WordPress</strong></li>';
    echo '<li><strong>Go to Users → Add New</strong></li>';
    echo '<li><strong>Check if newsroom_trainee is gone</strong></li>';
    echo '</ol>';
    echo '</div>';

    echo '<p><a href="' . admin_url('user-new.php') . '" class="button">Go to Add New User</a></p>';

} else {
    // Show current state and confirmation
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    $option_name = $table_prefix . 'user_roles';
    
    echo '<div class="info">';
    echo '<h2>Current Situation</h2>';
    echo '<p>This script will use the <strong>NUCLEAR OPTION</strong> to remove the <code>newsroom_trainee</code> role.</p>';
    echo '</div>';

    echo '<h2>Current Roles in Database:</h2>';
    $roles = get_option($option_name);
    echo '<ul>';
    $found = false;
    foreach ($roles as $slug => $data) {
        if ($slug === 'newsroom_trainee') {
            $found = true;
            echo "<li style='color:red; font-weight:bold; font-size:18px;'><code>$slug</code> - {$data['name']} ⚠️ TARGET FOR DELETION</li>";
        } else {
            echo "<li><code>$slug</code> - {$data['name']}</li>";
        }
    }
    echo '</ul>';

    if ($found) {
        echo '<div class="danger">';
        echo '<h3>⚠️ newsroom_trainee DETECTED!</h3>';
        echo '<p>Click the button below to remove it permanently.</p>';
        echo '</div>';
    } else {
        echo '<div class="success">';
        echo '<h3>✅ newsroom_trainee NOT found in database</h3>';
        echo '<p>If you still see it in the Add User page, it might be cached in your browser.</p>';
        echo '<p><strong>Try closing your browser completely and logging back in.</strong></p>';
        echo '</div>';
    }

    echo '<h2>Database Information:</h2>';
    echo '<ul>';
    echo '<li><strong>Table:</strong> <code>' . $table_prefix . 'options</code></li>';
    echo '<li><strong>Option Name:</strong> <code>' . $option_name . '</code></li>';
    echo '</ul>';

    echo '<p>';
    echo '<a href="?kill=yes" class="button" onclick="return confirm(\'Are you sure you want to KILL newsroom_trainee role?\')">🔥 YES, KILL IT NOW!</a>';
    echo '</p>';
}
?>

</body>
</html>

