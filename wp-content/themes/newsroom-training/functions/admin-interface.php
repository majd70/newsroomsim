<?php
/**
 * Admin Interface Functions
 */

/**
 * Setup admin interface
 */
function newsroom_setup_admin() {
    // Admin interface setup
}

/**
 * Get admin URL
 */
function admin_url($path = '') {
    return $path ?: 'admin.php';
}

/**
 * Admin dashboard functions
 */
function newsroom_admin_dashboard() {
    if (!current_user_can('admin_access')) {
        return false;
    }
    
    $stats = newsroom_get_content_stats();
    $user_stats = newsroom_get_user_stats();
    
    return [
        'content' => $stats,
        'users' => $user_stats
    ];
}

/**
 * Content management functions
 */
function newsroom_admin_get_recent_content($limit = 10) {
    $content = getContent();
    
    // Sort by timestamp (newest first)
    usort($content, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return array_slice($content, 0, $limit);
}

/**
 * User management functions
 */
function newsroom_admin_get_pending_users() {
    return loadPendingUsers();
}

function newsroom_admin_approve_user($user_id) {
    $pending = loadPendingUsers();
    $users = loadUsers();
    
    foreach ($pending as $key => $user) {
        if ($user['id'] === $user_id) {
            // Move user from pending to active
            $users[] = $user;
            unset($pending[$key]);
            
            // Save both files
            saveUsers($users);
            savePendingUsers(array_values($pending));
            
            return true;
        }
    }
    
    return false;
}

function newsroom_admin_reject_user($user_id) {
    $pending = loadPendingUsers();
    
    foreach ($pending as $key => $user) {
        if ($user['id'] === $user_id) {
            unset($pending[$key]);
            savePendingUsers(array_values($pending));
            return true;
        }
    }
    
    return false;
}
?>