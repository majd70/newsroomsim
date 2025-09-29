<?php
/**
 * User Role Management
 * WordPress-inspired user role system
 */

/**
 * Setup user roles
 */
function newsroom_setup_user_roles() {
    // Roles are handled by the original authentication system
    // This provides WordPress-like structure for organization
}

/**
 * Check if current user has capability
 */
function current_user_can($capability) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $role_caps = [
        'operator' => [
            'edit_posts', 'publish_posts', 'delete_posts', 'manage_content',
            'manage_users', 'edit_news', 'edit_social', 'admin_access'
        ],
        'trainee' => [
            'read_content'
        ]
    ];
    
    $user_role = $user['role'] ?? 'trainee';
    $caps = $role_caps[$user_role] ?? [];
    
    return in_array($capability, $caps);
}

/**
 * Get current user display name
 */
function get_current_user_display_name() {
    $user = getCurrentUser();
    return $user ? $user['name'] : 'User';
}

/**
 * Check if user is logged in
 */
function is_user_logged_in() {
    return isLoggedIn(); // Use original function
}

/**
 * Get logout URL
 */
function get_logout_url($redirect = '') {
    return 'logout.php' . ($redirect ? '?redirect=' . urlencode($redirect) : '');
}

/**
 * Get login URL
 */
function get_login_url($redirect = '') {
    return 'login.php' . ($redirect ? '?redirect=' . urlencode($redirect) : '');
}

/**
 * Require specific capability
 */
function newsroom_require_capability($capability) {
    if (!current_user_can($capability)) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get user statistics
 */
function newsroom_get_user_stats() {
    $users = loadUsers();
    $pending = loadPendingUsers();
    
    $stats = [
        'total_users' => count($users),
        'operators' => 0,
        'trainees' => 0,
        'pending' => count($pending)
    ];
    
    foreach ($users as $user) {
        if ($user['role'] === 'operator') {
            $stats['operators']++;
        } else {
            $stats['trainees']++;
        }
    }
    
    return $stats;
}
?>