<?php
/**
 * Web-based User Role Setup
 * URL: yourdomain.com/newsroomsim/setup-roles-web.php
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Newsroom User Roles Setup</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 900px; 
            margin: 0 auto; 
            padding: 20px; 
            background: #f0f0f1; 
            line-height: 1.6;
        }
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.13); 
        }
        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            border-bottom: 3px solid #0073aa; 
            padding-bottom: 20px; 
        }
        .step { 
            background: #f8f9fa; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 6px; 
            border-left: 4px solid #0073aa; 
        }
        .success { 
            background: #d1ecf1; 
            border-left-color: #28a745; 
            color: #0c5460; 
        }
        .warning { 
            background: #fff3cd; 
            border-left-color: #ffc107; 
            color: #856404; 
        }
        .error { 
            background: #f8d7da; 
            border-left-color: #dc3545; 
            color: #721c24; 
        }
        h1 { color: #23282d; margin: 0; }
        h2 { color: #0073aa; margin-top: 0; }
        h3 { color: #50575e; margin-bottom: 10px; }
        .checkmark { color: #28a745; font-weight: bold; }
        .cross { color: #dc3545; font-weight: bold; }
        .info { color: #0073aa; font-weight: bold; }
        ul { margin: 15px 0; padding-left: 25px; }
        li { margin: 8px 0; }
        .role-info { 
            background: #e7f3ff; 
            padding: 15px; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .final-summary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 8px; 
            text-align: center; 
            margin-top: 30px; 
        }
        .btn { 
            background: #0073aa; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 4px; 
            text-decoration: none; 
            display: inline-block; 
            margin: 10px 5px; 
            font-weight: 500;
        }
        .btn:hover { background: #005177; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Newsroom User Roles Setup</h1>
            <p>Configuring your system with the correct user roles...</p>
        </div>

        <?php
        // Roles to DELETE (remove these custom roles)
        $roles_to_delete = [
            'trainee_pending',
            'operator', 
            'newsroom_trainee',
        ];

        // Step 1: Remove unwanted roles
        echo '<div class="step">';
        echo '<h2>Step 1: Cleaning up old roles</h2>';
        echo '<ul>';
        $removed_count = 0;
        foreach ($roles_to_delete as $role_slug) {
            if (get_role($role_slug)) {
                remove_role($role_slug);
                echo "<li><span class='checkmark'>✓</span> Removed: <strong>$role_slug</strong></li>";
                $removed_count++;
            } else {
                echo "<li><span class='info'>ℹ</span> Role not found: $role_slug</li>";
            }
        }
        echo '</ul>';
        if ($removed_count > 0) {
            echo "<p class='success'>Removed $removed_count old roles successfully.</p>";
        } else {
            echo "<p class='info'>No old roles to remove.</p>";
        }
        echo '</div>';

        // Step 2: Setup required roles
        echo '<div class="step">';
        echo '<h2>Step 2: Setting up the 4 main roles</h2>';

        // 1. Administrator
        echo '<div class="role-info">';
        echo '<h3>1. Administrator</h3>';
        $admin_role = get_role('administrator');
        if ($admin_role) {
            echo "<p><span class='checkmark'>✓</span> Already exists (WordPress default)</p>";
        } else {
            echo "<p><span class='cross'>✗</span> ERROR: Administrator role missing!</p>";
        }
        echo '</div>';

        // 2. Newsroom Operator
        echo '<div class="role-info">';
        echo '<h3>2. Newsroom Operator</h3>';
        $operator_role = get_role('newsroom_operator');
        if (!$operator_role) {
            add_role('newsroom_operator', 'Newsroom Operator', array(
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'upload_files' => true,
                'edit_news_articles' => true,
                'edit_social_posts' => true,
                'manage_training_content' => true,
                'edit_news_article' => true,
                'edit_others_news_articles' => true,
                'publish_news_articles' => true,
                'read_private_news_articles' => true,
                'delete_news_article' => true,
                'delete_news_articles' => true,
                'delete_others_news_articles' => true,
                'delete_published_news_articles' => true,
                'edit_social_post' => true,
                'edit_others_social_posts' => true,
                'publish_social_posts' => true,
                'read_private_social_posts' => true,
                'delete_social_post' => true,
                'delete_social_posts' => true,
                'delete_others_social_posts' => true,
                'delete_published_social_posts' => true,
            ));
            echo "<p><span class='checkmark'>✓</span> Created with full content management capabilities</p>";
        } else {
            echo "<p><span class='checkmark'>✓</span> Already exists</p>";
            // Update capabilities
            $caps = array(
                'read', 'edit_posts', 'delete_posts', 'publish_posts', 'upload_files',
                'edit_news_articles', 'edit_social_posts', 'manage_training_content',
                'edit_news_article', 'edit_others_news_articles', 'publish_news_articles',
                'read_private_news_articles', 'delete_news_article', 'delete_news_articles',
                'delete_others_news_articles', 'delete_published_news_articles',
                'edit_social_post', 'edit_others_social_posts', 'publish_social_posts',
                'read_private_social_posts', 'delete_social_post', 'delete_social_posts',
                'delete_others_social_posts', 'delete_published_social_posts',
            );
            foreach ($caps as $cap) {
                $operator_role->add_cap($cap);
            }
            echo "<p><span class='info'>ℹ</span> Capabilities updated</p>";
        }
        echo '</div>';

        // 3. Trainee
        echo '<div class="role-info">';
        echo '<h3>3. Trainee</h3>';
        $trainee_role = get_role('trainee');
        if (!$trainee_role) {
            add_role('trainee', 'Trainee', array(
                'read' => true,
                'read_news_article' => true,
                'read_social_post' => true,
                'edit_comment' => true,
                'moderate_comments' => false,
                'edit_comments' => false,
                'add_reply' => true,
                'edit_own_reply' => true,
                'delete_own_reply' => true,
                'like_post' => true,
                'unlike_post' => true,
            ));
            echo "<p><span class='checkmark'>✓</span> Created with read access + interaction capabilities</p>";
        } else {
            echo "<p><span class='checkmark'>✓</span> Already exists</p>";
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
            echo "<p><span class='info'>ℹ</span> Capabilities updated</p>";
        }
        echo '</div>';

        // 4. Viewer
        echo '<div class="role-info">';
        echo '<h3>4. Viewer</h3>';
        $viewer_role = get_role('viewer');
        if (!$viewer_role) {
            add_role('viewer', 'Viewer', array(
                'read' => true,
                'read_news_article' => true,
                'read_social_post' => true,
            ));
            echo "<p><span class='checkmark'>✓</span> Created with read-only access</p>";
        } else {
            echo "<p><span class='checkmark'>✓</span> Already exists</p>";
            // Ensure only read capabilities
            $viewer_role->add_cap('read');
            $viewer_role->add_cap('read_news_article');
            $viewer_role->add_cap('read_social_post');
            echo "<p><span class='info'>ℹ</span> Capabilities updated (read-only)</p>";
        }
        echo '</div>';

        echo '</div>';

        // Clear cache
        wp_cache_flush();
        delete_option('wp_user_roles');

        // Final summary
        echo '<div class="final-summary">';
        echo '<h2>🎉 Setup Complete!</h2>';
        echo '<p>Your system now has exactly 4 user roles:</p>';
        echo '<ul style="text-align: left; display: inline-block;">';
        echo '<li><strong>Administrator</strong> - Full system access</li>';
        echo '<li><strong>Newsroom Operator</strong> - Can create/edit/delete content</li>';
        echo '<li><strong>Trainee</strong> - Can read content + add replies/comments and like posts</li>';
        echo '<li><strong>Viewer</strong> - Read-only access to content (no interaction)</li>';
        echo '</ul>';
        echo '<br><br>';
        echo '<a href="wp-admin/" class="btn">Go to WordPress Admin</a>';
        echo '<a href="wp-admin/users.php" class="btn">Manage Users</a>';
        echo '</div>';

        // Show current roles for verification
        echo '<div class="step">';
        echo '<h2>Verification: Current Roles in System</h2>';
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        echo '<ul>';
        foreach ($wp_roles->roles as $role_slug => $role_info) {
            $count = count_users()['avail_roles'][$role_slug] ?? 0;
            echo "<li><strong>{$role_info['name']}</strong> ($role_slug) - $count users</li>";
        }
        echo '</ul>';
        echo '</div>';
        ?>

    </div>
</body>
</html>
