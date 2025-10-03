<?php
/**
 * Setup User Roles
 * This script will:
 * 1. Remove unwanted custom roles
 * 2. Keep: Administrator, Newsroom Operator, Trainee
 * 3. Add: Viewer
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

echo "=== Setting Up User Roles ===\n\n";

// Roles to DELETE (remove these custom roles)
$roles_to_delete = [
    'trainee_pending',
    'operator',
    'newsroom_trainee',
    // Keep default WordPress roles: subscriber, contributor, author, editor
];

// Step 1: Remove unwanted roles
echo "Step 1: Removing unwanted roles...\n";
foreach ($roles_to_delete as $role_slug) {
    if (get_role($role_slug)) {
        remove_role($role_slug);
        echo "  ✓ Removed: $role_slug\n";
    } else {
        echo "  - Role not found: $role_slug\n";
    }
}
echo "\n";

// Step 2: Ensure the 4 required roles exist
echo "Step 2: Setting up required roles...\n\n";

// 1. Administrator (already exists in WordPress - just verify)
echo "1. Administrator\n";
$admin_role = get_role('administrator');
if ($admin_role) {
    echo "   ✓ Already exists (WordPress default)\n";
} else {
    echo "   ✗ ERROR: Administrator role missing!\n";
}
echo "\n";

// 2. Newsroom Operator
echo "2. Newsroom Operator\n";
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
        // Custom post type capabilities
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
    echo "   ✓ Created with full content management capabilities\n";
} else {
    echo "   ✓ Already exists\n";
    // Update capabilities
    $caps = array(
        'read', 'edit_posts', 'edit_others_posts', 'edit_published_posts',
        'delete_posts', 'delete_others_posts', 'delete_published_posts',
        'publish_posts', 'upload_files', 'edit_news_articles', 'edit_social_posts',
        'manage_training_content', 'read_news_article', 'read_private_news_articles',
        'edit_news_article', 'edit_news_articles', 'edit_others_news_articles',
        'edit_published_news_articles', 'publish_news_articles', 'delete_news_article',
        'delete_news_articles', 'delete_others_news_articles', 'delete_published_news_articles',
        'read_social_post', 'edit_social_post', 'edit_social_posts', 'edit_others_social_posts',
        'edit_published_social_posts', 'publish_social_posts', 'delete_social_post',
        'delete_social_posts', 'delete_others_social_posts', 'delete_published_social_posts',
    );
    foreach ($caps as $cap) {
        $operator_role->add_cap($cap);
    }
    echo "   ✓ Capabilities updated\n";
}
echo "\n";

// 3. Trainee
echo "3. Trainee\n";
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
    echo "   ✓ Created with read + interact capabilities (reply, like)\n";
} else {
    echo "   ✓ Already exists\n";
    // Update with interaction capabilities
    $trainee_role->add_cap('read');
    $trainee_role->add_cap('read_news_article');
    $trainee_role->add_cap('read_social_post');
    $trainee_role->add_cap('edit_comment');
    $trainee_role->add_cap('add_reply');
    $trainee_role->add_cap('edit_own_reply');
    $trainee_role->add_cap('delete_own_reply');
    $trainee_role->add_cap('like_post');
    $trainee_role->add_cap('unlike_post');
    echo "   ✓ Capabilities updated (read + interact: reply, like)\n";
}
echo "\n";

// 4. Viewer (NEW)
echo "4. Viewer\n";
$viewer_role = get_role('viewer');
if (!$viewer_role) {
    add_role('viewer', 'Viewer', array(
        'read' => true,
        'read_news_article' => true,
        'read_social_post' => true,
    ));
    echo "   ✓ Created with read-only access (no interaction)\n";
} else {
    echo "   ✓ Already exists\n";
    // Ensure only read capabilities
    $viewer_role->add_cap('read');
    $viewer_role->add_cap('read_news_article');
    $viewer_role->add_cap('read_social_post');
    echo "   ✓ Capabilities updated (read-only)\n";
}
echo "\n";

// Step 3: Display final role summary
echo "=== Final Role Summary ===\n\n";

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
        echo "✓ $name ($slug): $user_count users\n";
    } else {
        echo "✗ $name ($slug): NOT FOUND\n";
    }
}

echo "\n=== Setup Complete! ===\n";
echo "\nYour system now has exactly 4 user roles:\n";
echo "1. Administrator - Full system access\n";
echo "2. Newsroom Operator - Can create/edit/delete content\n";
echo "3. Trainee - Can read content + add replies/comments and like posts\n";
echo "4. Viewer - Read-only access to content (no interaction)\n";

