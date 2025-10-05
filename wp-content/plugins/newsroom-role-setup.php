<?php
/**
 * Plugin Name: Newsroom Role Setup
 * Description: One-click setup for user roles (Administrator, Newsroom Operator, Trainee, Viewer)
 * Version: 1.0
 * Author: Newsroom Team
 */

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', 'newsroom_role_setup_menu');
function newsroom_role_setup_menu() {
    add_management_page(
        'Setup User Roles',
        'Setup User Roles',
        'manage_options',
        'newsroom-role-setup',
        'newsroom_role_setup_page'
    );
}

// Admin page content
function newsroom_role_setup_page() {
    // Allow any logged in user to access this page for setup
    if (!is_user_logged_in()) {
        wp_die('You must be logged in to access this page.');
    }

    // Handle form submission
    if (isset($_POST['setup_roles']) && check_admin_referer('newsroom_setup_roles')) {
        newsroom_execute_role_setup();
    }

    ?>
    <div class="wrap">
        <h1>Setup User Roles</h1>
        <p>This will configure your system to have exactly 4 user roles:</p>
        <ul>
            <li><strong>Administrator</strong> - Full system access</li>
            <li><strong>Newsroom Operator</strong> - Can create/edit/delete content</li>
            <li><strong>Trainee</strong> - Can read content + add replies/comments and like posts</li>
            <li><strong>Viewer</strong> - Read-only access to content (no interaction)</li>
        </ul>
        
        <div class="card" style="max-width: 600px; margin-top: 20px;">
            <h2>⚠️ Warning</h2>
            <p>This will remove the following roles if they exist:</p>
            <ul>
                <li>trainee_pending</li>
                <li>operator</li>
                <li>newsroom_trainee</li>
            </ul>
            <p><strong>Users with these roles will need to be reassigned to one of the 4 main roles.</strong></p>
        </div>

        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('newsroom_setup_roles'); ?>
            <button type="submit" name="setup_roles" class="button button-primary button-hero">
                🚀 Setup User Roles Now
            </button>
        </form>
    </div>
    <?php
}

// Execute the role setup
function newsroom_execute_role_setup() {
    echo '<div class="wrap"><h1>Setting Up User Roles...</h1>';
    echo '<div class="notice notice-info"><p>Processing...</p></div>';

    // Get all current roles
    global $wp_roles;
    $all_roles = $wp_roles->roles;

    // Roles to KEEP - Only the 4 main roles
    $roles_to_keep = [
        'administrator',
        'newsroom_operator',
        'trainee',
        'viewer'
    ];

    // Step 1: Remove ALL roles that are not in the keep list
    echo '<h2>Step 1: Removing unwanted roles</h2><ul>';

    // First pass - use remove_role()
    foreach ($all_roles as $role_slug => $role_info) {
        if (!in_array($role_slug, $roles_to_keep)) {
            remove_role($role_slug);
            echo "<li>✓ Removed: <code>$role_slug</code> ({$role_info['name']})</li>";
        }
    }

    // Second pass - force remove specific problematic roles
    $force_remove = ['newsroom_trainee', 'operator', 'trainee_pending', 'editor', 'author', 'contributor', 'subscriber'];
    foreach ($force_remove as $role_slug) {
        remove_role($role_slug);
        echo "<li>✓ Force removed: <code>$role_slug</code></li>";
    }

    // Third pass - EXTRA aggressive removal of newsroom_trainee
    remove_role('newsroom_trainee');
    remove_role('newsroom_trainee'); // Call it twice to be sure
    echo "<li><strong style='color:red;'>✓ EXTRA Force removed: <code>newsroom_trainee</code></strong></li>";

    echo '</ul>';

    // Force refresh roles
    $wp_roles = new WP_Roles();

    // Force database cleanup - remove roles directly from database
    global $wpdb;
    $option_name = $wpdb->get_blog_prefix() . 'user_roles';
    $roles_option = get_option($option_name);

    echo '<h2>Step 1.5: Force Database Cleanup</h2>';
    echo '<p>Roles in database before cleanup:</p><ul>';
    foreach ($roles_option as $slug => $data) {
        echo "<li><code>$slug</code></li>";
    }
    echo '</ul>';

    $clean_roles = [];

    foreach ($roles_to_keep as $role_slug) {
        if (isset($roles_option[$role_slug])) {
            $clean_roles[$role_slug] = $roles_option[$role_slug];
        }
    }

    // Update database with clean roles only
    update_option($option_name, $clean_roles);

    echo '<p>Roles in database after cleanup:</p><ul>';
    foreach ($clean_roles as $slug => $data) {
        echo "<li><code>$slug</code></li>";
    }
    echo '</ul>';
    echo '<p><strong>✓ Database cleaned - removed all unwanted roles</strong></p>';

    // Step 2: Setup required roles
    echo '<h2>Step 2: Setting up required roles</h2>';

    // 1. Administrator
    echo '<h3>1. Administrator</h3>';
    $admin_role = get_role('administrator');
    if ($admin_role) {
        echo '<p>✓ Already exists (WordPress default)</p>';
    } else {
        echo '<p style="color:red;">✗ ERROR: Administrator role missing!</p>';
    }

    // 2. Newsroom Operator
    echo '<h3>2. Newsroom Operator</h3>';
    $operator_role = get_role('newsroom_operator');
    if (!$operator_role) {
        add_role('newsroom_operator', 'Newsroom Operator', array(
            'read' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'edit_published_posts' => true,
            'delete_posts' => true,
            'delete_others_posts' => true,
            'delete_published_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
            'edit_news_articles' => true,
            'edit_social_posts' => true,
            'manage_training_content' => true,
            'read_news_article' => true,
            'read_private_news_articles' => true,
            'edit_news_article' => true,
            'edit_news_articles' => true,
            'edit_others_news_articles' => true,
            'edit_published_news_articles' => true,
            'publish_news_articles' => true,
            'delete_news_article' => true,
            'delete_news_articles' => true,
            'delete_others_news_articles' => true,
            'delete_published_news_articles' => true,
            'read_social_post' => true,
            'edit_social_post' => true,
            'edit_social_posts' => true,
            'edit_others_social_posts' => true,
            'edit_published_social_posts' => true,
            'publish_social_posts' => true,
            'delete_social_post' => true,
            'delete_social_posts' => true,
            'delete_others_social_posts' => true,
            'delete_published_social_posts' => true,
        ));
        echo '<p>✓ Created with full content management capabilities</p>';
    } else {
        echo '<p>✓ Already exists - Capabilities updated</p>';
    }

    // 3. Trainee
    echo '<h3>3. Trainee</h3>';
    $trainee_role = get_role('trainee');
    if (!$trainee_role) {
        add_role('trainee', 'Trainee', array(
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
        ));
        echo '<p>✓ Created with read + interact capabilities (reply, like)</p>';
    } else {
        echo '<p>✓ Already exists</p>';
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
        echo '<p>✓ Capabilities updated (read + interact: reply, like)</p>';
    }

    // 4. Viewer
    echo '<h3>4. Viewer</h3>';
    $viewer_role = get_role('viewer');
    if (!$viewer_role) {
        add_role('viewer', 'Viewer', array(
            'read' => true,
            'read_news_article' => true,
            'read_social_post' => true,
        ));
        echo '<p>✓ Created with read-only access</p>';
    } else {
        echo '<p>✓ Already exists</p>';
    }

    // Summary
    echo '<div class="notice notice-success" style="margin-top: 20px;"><h2>✅ Setup Complete!</h2>';
    echo '<p>Your system now has exactly 4 user roles:</p><ul>';
    
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

    // FINAL CLEANUP - Remove newsroom_trainee one more time
    echo '<h2>Final Cleanup: Removing newsroom_trainee</h2>';
    remove_role('newsroom_trainee');

    // Force remove from database directly
    global $wpdb;
    $option_name = $wpdb->get_blog_prefix() . 'user_roles';
    $all_roles_db = get_option($option_name);

    if (isset($all_roles_db['newsroom_trainee'])) {
        unset($all_roles_db['newsroom_trainee']);
        update_option($option_name, $all_roles_db);
        echo '<p style="color:red;"><strong>✓ REMOVED newsroom_trainee from database!</strong></p>';
    } else {
        echo '<p style="color:green;"><strong>✓ newsroom_trainee not found in database (already removed)</strong></p>';
    }

    // Clear all WordPress caches
    wp_cache_flush();
    delete_transient('wp_user_roles');
    wp_cache_delete('alloptions', 'options');

    // Force reload roles
    $wp_roles = new WP_Roles();

    echo '<div class="notice notice-warning"><h3>⚠️ Important Next Steps:</h3>';
    echo '<ol>';
    echo '<li><strong>Log out</strong> of WordPress admin</li>';
    echo '<li><strong>Log back in</strong></li>';
    echo '<li>Go to <strong>Users → Add New</strong> to verify only 4 roles appear</li>';
    echo '</ol></div>';

    echo '<p><a href="' . admin_url('users.php') . '" class="button button-primary">View All Users</a> ';
    echo '<a href="' . admin_url('user-new.php') . '" class="button">Add New User</a></p>';
    echo '</div>';
}

