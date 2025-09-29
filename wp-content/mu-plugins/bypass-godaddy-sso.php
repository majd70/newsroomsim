<?php
/*
Plugin Name: Bypass GoDaddy SSO
Description: Allows direct access to /wp-admin without GoDaddy SSO links.
Version: 1.0
Author: You
*/

// Force WordPress admin access
add_action('init', function() {
    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false) {
        // Make sure user can log in normally
        if (!is_user_logged_in() && isset($_POST['log']) && isset($_POST['pwd'])) {
            $creds = [
                'user_login'    => $_POST['log'],
                'user_password' => $_POST['pwd'],
                'remember'      => true
            ];
            $user = wp_signon($creds, false);
            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
            }
        }
    }
});
