<?php
/**
 * Theme Setup Functions
 */

/**
 * Setup theme features
 */
function newsroom_setup_theme() {
    // Theme supports (WordPress-style organization)
    // This is a structure for better code organization
}

/**
 * Get home URL
 */
function home_url($path = '') {
    return $path ? $path : '/';
}

/**
 * WordPress-style conditional functions
 */
function is_front_page() {
    return $_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php';
}

function is_admin() {
    return strpos($_SERVER['REQUEST_URI'], 'admin.php') !== false;
}

/**
 * Theme customization functions
 */
function newsroom_get_theme_option($option, $default = '') {
    // For future theme customization options
    return $default;
}

/**
 * Security functions
 */
function wp_verify_nonce($nonce, $action) {
    // Simple nonce verification
    return hash('sha256', $action . session_id()) === $nonce;
}

function wp_create_nonce($action) {
    return hash('sha256', $action . session_id());
}
?>