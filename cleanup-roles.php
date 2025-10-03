<?php
/**
 * Cleanup and Setup User Roles
 * Access this file directly via browser: http://localhost/Upwork/David/newsroomsim/cleanup-roles.php
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

// Security check - only allow administrators
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this script.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup User Roles</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #333; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; margin: 10px 0; border-radius: 4px; }
        ul { line-height: 1.8; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .button { display: inline-block; background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 10px 5px 10px 0; }
        .button:hover { background: #005177; }
    </style>
</head>
<body>
    <h1>🔧 Setup User Roles</h1>

<?php
if (isset($_GET['run']) && $_GET['run'] === 'yes') {
    echo '<div class="info"><strong>Processing...</strong></div>';

    // Get all current roles
    global $wp_roles;
    $all_roles = $wp_roles->roles;
    
    // Roles to KEEP
    $roles_to_keep = [
        'administrator',
        'editor',
        'author', 
        'contributor',
        'subscriber',
        'newsroom_operator',
        'trainee',
        'viewer'
    ];

    // Step 1: Remove unwanted roles
    echo '<h2>Step 1: Removing Unwanted Roles</h2><ul>';
    $removed_count = 0;
    foreach ($all_roles as $role_slug => $role_info) {
        if (!in_array($role_slug, $roles_to_keep)) {
            remove_role($role_slug);
            echo "<li>✓ Removed: <code>$role_slug</code> ({$role_info['name']})</li>";
            $removed_count++;
        }
    }
    if ($removed_count === 0) {
        echo '<li>No unwanted roles found.</li>';
    }
    echo '</ul>';

    // Step 2: Setup required roles
    echo '<h2>Step 2: Setting Up Required Roles</h2>';

    // 1. Administrator
    echo '<h3>1. Administrator</h3>';
    if (get_role('administrator')) {
        echo '<p>✓ Already exists (WordPress default)</p>';
    }

    // 2. Newsroom Operator
    echo '<h3>2. Newsroom Operator</h3>';
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
        echo '<p>✓ Created with full content management capabilities</p>';
    } else {
        echo '<p>✓ Already exists</p>';
    }

    // 3. Trainee
    echo '<h3>3. Trainee</h3>';
    $trainee_role = get_role('trainee');
    if (!$trainee_role) {
        add_role('trainee', 'Trainee', [
            'read' => true,
            'read_news_article' => true,
            'read_social_post' => true,
            // Comment/Reply capabilities
            'edit_comment' => true,
            'moderate_comments' => false,
            'edit_comments' => false,
            // Custom capabilities for likes and replies
            'add_reply' => true,
            'edit_own_reply' => true,
            'delete_own_reply' => true,
            'like_post' => true,
            'unlike_post' => true,
        ]);
        echo '<p>✓ Created with read + interact capabilities (reply, like)</p>';
    } else {
        // Update existing role with new capabilities
        $trainee_role->add_cap('read');
        $trainee_role->add_cap('read_news_article');
        $trainee_role->add_cap('read_social_post');
        $trainee_role->add_cap('edit_comment');
        $trainee_role->add_cap('add_reply');
        $trainee_role->add_cap('edit_own_reply');
        $trainee_role->add_cap('delete_own_reply');
        $trainee_role->add_cap('like_post');
        $trainee_role->add_cap('unlike_post');
        echo '<p>✓ Already exists - Updated with interact capabilities (reply, like)</p>';
    }

    // 4. Viewer
    echo '<h3>4. Viewer</h3>';
    if (!get_role('viewer')) {
        add_role('viewer', 'Viewer', [
            'read' => true,
            'read_news_article' => true,
            'read_social_post' => true,
        ]);
        echo '<p>✓ Created with read-only access</p>';
    } else {
        echo '<p>✓ Already exists</p>';
    }

    // Clear cache
    wp_cache_flush();
    delete_option('wp_user_roles');
    $wp_roles = new WP_Roles();

    // Summary
    echo '<div class="success">';
    echo '<h2>✅ Setup Complete!</h2>';
    echo '<p>Your system now has these user roles:</p><ul>';
    
    $final_roles = [
        'administrator' => 'Administrator',
        'newsroom_operator' => 'Newsroom Operator',
        'trainee' => 'Trainee',
        'viewer' => 'Viewer',
    ];

    foreach ($final_roles as $slug => $name) {
        $role = get_role($slug);
        if ($role) {
            $user_count = count(get_users(['role' => $slug]));
            echo "<li>✓ <strong>$name</strong> (<code>$slug</code>): $user_count users</li>";
        }
    }
    
    echo '</ul></div>';
    
    echo '<p><a href="' . admin_url('users.php') . '" class="button">View All Users</a> ';
    echo '<a href="' . admin_url('user-new.php') . '" class="button">Add New User</a></p>';
    
    echo '<div class="info"><strong>Note:</strong> The role dropdown should now show only the correct roles. If you still see old roles, try logging out and logging back in.</div>';

} else {
    // Show confirmation page
    ?>
    <div class="warning">
        <h2>⚠️ Warning</h2>
        <p>This script will:</p>
        <ul>
            <li>Remove ALL custom roles except: <code>newsroom_operator</code>, <code>trainee</code>, <code>viewer</code></li>
            <li>Keep WordPress default roles: Administrator, Editor, Author, Contributor, Subscriber</li>
            <li>Create the 4 main roles if they don't exist</li>
        </ul>
        <p><strong>Users with removed roles will need to be reassigned.</strong></p>
    </div>

    <h2>Your 4 Main Roles:</h2>
    <ul>
        <li><strong>Administrator</strong> - Full system access</li>
        <li><strong>Newsroom Operator</strong> - Can create/edit/delete content</li>
        <li><strong>Trainee</strong> - Can read content + add replies/comments and like posts</li>
        <li><strong>Viewer</strong> - Read-only access to content (no interaction)</li>
    </ul>

    <p>
        <a href="?run=yes" class="button" onclick="return confirm('Are you sure you want to proceed?')">
            🚀 Yes, Setup User Roles Now
        </a>
        <a href="<?php echo admin_url(); ?>" class="button" style="background: #666;">Cancel</a>
    </p>
    <?php
}
?>

</body>
</html>

