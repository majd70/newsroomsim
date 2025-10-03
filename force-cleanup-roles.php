<?php
/**
 * Force Cleanup All Roles - Aggressive Database Cleanup
 * This will completely remove all unwanted roles from the database
 * Access: http://localhost/Upwork/David/newsroomsim/force-cleanup-roles.php
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
    <title>Force Cleanup Roles</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #d63638; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 4px; }
        ul { line-height: 2; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; font-family: monospace; }
        .button { display: inline-block; background: #d63638; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 10px 5px 10px 0; font-weight: bold; border: none; cursor: pointer; font-size: 16px; }
        .button:hover { background: #a02020; }
        .button-secondary { background: #666; }
        .button-secondary:hover { background: #444; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .role-keep { color: #0a7e07; font-weight: bold; }
        .role-delete { color: #d63638; font-weight: bold; }
    </style>
</head>
<body>
    <h1>⚠️ Force Cleanup All Roles</h1>

<?php
if (isset($_GET['run']) && $_GET['run'] === 'yes') {
    echo '<div class="info"><strong>Processing aggressive cleanup...</strong></div>';

    // Get current roles directly from database
    global $wpdb;
    $roles_option = get_option($wpdb->get_blog_prefix() . 'user_roles');
    
    echo '<h2>Step 1: Current Roles in Database</h2>';
    echo '<table><tr><th>Role Slug</th><th>Role Name</th><th>Action</th></tr>';
    
    // Roles to KEEP - Only the 4 main roles + administrator
    $roles_to_keep = [
        'administrator',
        'newsroom_operator',
        'trainee',
        'viewer'
    ];
    
    foreach ($roles_option as $role_slug => $role_data) {
        $action = in_array($role_slug, $roles_to_keep) ? 
            '<span class="role-keep">✓ KEEP</span>' : 
            '<span class="role-delete">✗ DELETE</span>';
        echo "<tr><td><code>$role_slug</code></td><td>{$role_data['name']}</td><td>$action</td></tr>";
    }
    echo '</table>';

    // Step 2: Remove unwanted roles
    echo '<h2>Step 2: Removing Unwanted Roles</h2>';
    echo '<ul>';
    
    $removed_roles = [];
    foreach ($roles_option as $role_slug => $role_data) {
        if (!in_array($role_slug, $roles_to_keep)) {
            remove_role($role_slug);
            $removed_roles[] = $role_slug;
            echo "<li>✓ Removed: <code>$role_slug</code> ({$role_data['name']})</li>";
        }
    }
    
    if (empty($removed_roles)) {
        echo '<li>No roles to remove.</li>';
    }
    echo '</ul>';

    // Step 3: Force update the database option
    echo '<h2>Step 3: Force Database Update</h2>';
    
    // Get fresh roles after removal
    global $wp_roles;
    $wp_roles = new WP_Roles();
    
    // Build clean roles array
    $clean_roles = [];
    foreach ($roles_to_keep as $role_slug) {
        $role = get_role($role_slug);
        if ($role) {
            $clean_roles[$role_slug] = [
                'name' => $wp_roles->role_names[$role_slug] ?? ucfirst(str_replace('_', ' ', $role_slug)),
                'capabilities' => $role->capabilities
            ];
        }
    }
    
    // Force update database
    update_option($wpdb->get_blog_prefix() . 'user_roles', $clean_roles);
    echo '<p>✓ Database option updated with clean roles only</p>';

    // Step 4: Setup the 4 main roles with correct capabilities
    echo '<h2>Step 4: Setting Up Main Roles</h2>';

    // Newsroom Operator
    echo '<h3>Newsroom Operator</h3>';
    $operator_role = get_role('newsroom_operator');
    if (!$operator_role) {
        add_role('newsroom_operator', 'Newsroom Operator', [
            'read' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'edit_published_posts' => true,
            'delete_posts' => true,
            'delete_others_posts' => true,
            'delete_published_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
            'manage_training_content' => true,
            'read_news_article' => true,
            'edit_news_article' => true,
            'edit_news_articles' => true,
            'edit_others_news_articles' => true,
            'publish_news_articles' => true,
            'delete_news_articles' => true,
            'read_social_post' => true,
            'edit_social_post' => true,
            'edit_social_posts' => true,
            'edit_others_social_posts' => true,
            'publish_social_posts' => true,
            'delete_social_posts' => true,
        ]);
        echo '<p>✓ Created</p>';
    } else {
        echo '<p>✓ Already exists</p>';
    }

    // Trainee
    echo '<h3>Trainee</h3>';
    $trainee_role = get_role('trainee');
    if (!$trainee_role) {
        add_role('trainee', 'Trainee', [
            'read' => true,
            'read_news_article' => true,
            'read_social_post' => true,
            'edit_comment' => true,
            'add_reply' => true,
            'edit_own_reply' => true,
            'delete_own_reply' => true,
            'like_post' => true,
            'unlike_post' => true,
        ]);
        echo '<p>✓ Created with interact capabilities</p>';
    } else {
        // Update capabilities
        $trainee_role->add_cap('read');
        $trainee_role->add_cap('read_news_article');
        $trainee_role->add_cap('read_social_post');
        $trainee_role->add_cap('edit_comment');
        $trainee_role->add_cap('add_reply');
        $trainee_role->add_cap('edit_own_reply');
        $trainee_role->add_cap('delete_own_reply');
        $trainee_role->add_cap('like_post');
        $trainee_role->add_cap('unlike_post');
        echo '<p>✓ Updated with interact capabilities</p>';
    }

    // Viewer
    echo '<h3>Viewer</h3>';
    $viewer_role = get_role('viewer');
    if (!$viewer_role) {
        add_role('viewer', 'Viewer', [
            'read' => true,
            'read_news_article' => true,
            'read_social_post' => true,
        ]);
        echo '<p>✓ Created (read-only)</p>';
    } else {
        echo '<p>✓ Already exists</p>';
    }

    // Step 5: Clear all caches
    echo '<h2>Step 5: Clearing All Caches</h2>';
    wp_cache_flush();
    delete_transient('wp_user_roles');
    wp_cache_delete('alloptions', 'options');
    
    // Force WordPress to reload roles
    $wp_roles = new WP_Roles();
    
    echo '<p>✓ All caches cleared</p>';
    echo '<p>✓ WordPress roles reloaded</p>';

    // Final summary
    echo '<div class="success">';
    echo '<h2>✅ Cleanup Complete!</h2>';
    echo '<p><strong>Final roles in your system:</strong></p>';
    echo '<table><tr><th>Role</th><th>Description</th><th>Users</th></tr>';
    
    $final_roles = [
        'administrator' => 'Administrator - Full system access',
        'newsroom_operator' => 'Newsroom Operator - Can create/edit/delete content',
        'trainee' => 'Trainee - Can read + reply/like posts',
        'viewer' => 'Viewer - Read-only (no interaction)',
    ];

    foreach ($final_roles as $slug => $desc) {
        $role = get_role($slug);
        if ($role) {
            $user_count = count(get_users(['role' => $slug]));
            echo "<tr><td><code>$slug</code></td><td>$desc</td><td>$user_count</td></tr>";
        }
    }
    
    echo '</table></div>';
    
    echo '<div class="warning">';
    echo '<h3>⚠️ Important Next Steps:</h3>';
    echo '<ol>';
    echo '<li><strong>Log out</strong> of WordPress admin</li>';
    echo '<li><strong>Log back in</strong></li>';
    echo '<li>Go to <strong>Users → Add New</strong> to verify only correct roles appear</li>';
    echo '</ol>';
    echo '</div>';
    
    echo '<p><a href="' . admin_url('users.php') . '" class="button">View All Users</a> ';
    echo '<a href="' . admin_url('user-new.php') . '" class="button">Add New User</a></p>';

} else {
    // Show current roles and confirmation
    global $wp_roles;
    $all_roles = $wp_roles->roles;
    
    $roles_to_keep = ['administrator', 'newsroom_operator', 'trainee', 'viewer'];
    
    echo '<div class="danger">';
    echo '<h2>⚠️ DANGER: Aggressive Cleanup</h2>';
    echo '<p>This script will <strong>forcefully remove ALL unwanted roles</strong> directly from the database.</p>';
    echo '<p><strong>This will delete WordPress default roles: Editor, Author, Contributor, Subscriber</strong></p>';
    echo '</div>';

    echo '<h2>Current Roles in Your System:</h2>';
    echo '<table><tr><th>Role Slug</th><th>Role Name</th><th>Action</th></tr>';
    
    foreach ($all_roles as $role_slug => $role_info) {
        $action = in_array($role_slug, $roles_to_keep) ? 
            '<span class="role-keep">✓ KEEP</span>' : 
            '<span class="role-delete">✗ DELETE</span>';
        echo "<tr><td><code>$role_slug</code></td><td>{$role_info['name']}</td><td>$action</td></tr>";
    }
    echo '</table>';

    echo '<div class="warning">';
    echo '<h3>This will:</h3>';
    echo '<ul>';
    echo '<li>Remove ALL roles marked as DELETE above (including Editor, Author, Contributor, Subscriber)</li>';
    echo '<li>Keep only: <strong>Administrator, Newsroom Operator, Trainee, Viewer</strong></li>';
    echo '<li>Update the database directly</li>';
    echo '<li>Clear all WordPress caches</li>';
    echo '</ul>';
    echo '</div>';

    echo '<p><strong>After running, you MUST log out and log back in for changes to take effect.</strong></p>';

    echo '<p>';
    echo '<button onclick="if(confirm(\'Are you ABSOLUTELY SURE? This will permanently delete roles!\')) { window.location.href=\'?run=yes\'; }" class="button">⚠️ Yes, Force Cleanup Now</button> ';
    echo '<a href="' . admin_url() . '" class="button button-secondary">Cancel</a>';
    echo '</p>';
}
?>

</body>
</html>

