<?php
/**
 * Newsroom Training Theme Configuration
 * WordPress-inspired structure for better organization
 */

// Define standalone mode
define('NEWSROOM_STANDALONE', true);

// Theme Information
define('THEME_NAME', 'Newsroom Training Platform');
define('THEME_VERSION', '2.0');
define('THEME_AUTHOR', 'SignalBridge');

// Theme Paths
define('THEME_DIR', __DIR__);
define('THEME_URL', '/themes/newsroom-training');
define('ASSETS_URL', THEME_URL . '/assets');

// Load theme functions
require_once THEME_DIR . '/functions/theme-setup.php';
require_once THEME_DIR . '/functions/post-types.php';
require_once THEME_DIR . '/functions/user-roles.php';
require_once THEME_DIR . '/functions/admin-interface.php';

// Theme setup hook
function newsroom_theme_init() {
    // Add theme support
    newsroom_setup_theme();
    
    // Register post types
    newsroom_register_post_types();
    
    // Create user roles
    newsroom_setup_user_roles();
    
    // Setup admin interface
    newsroom_setup_admin();
}

// Initialize theme
newsroom_theme_init();

// Make user available globally for header
if (function_exists('getCurrentUser')) {
    $GLOBALS['user'] = getCurrentUser();
}

/**
 * Enqueue theme assets
 */
function newsroom_enqueue_assets() {
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' . "\n";
    echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">' . "\n";
    echo '<link href="' . ASSETS_URL . '/css/style.css" rel="stylesheet">' . "\n";
}

function newsroom_enqueue_scripts() {
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>' . "\n";
    echo '<script src="' . ASSETS_URL . '/js/main.js"></script>' . "\n";
}

/**
 * Get template part (WordPress-style)
 */
function get_template_part($slug, $name = null) {
    $template = $slug;
    if ($name) {
        $template .= '-' . $name;
    }
    $template .= '.php';
    
    $file_path = THEME_DIR . '/template-parts/' . $template;
    if (file_exists($file_path)) {
        include $file_path;
    }
}

/**
 * Include header template
 */
function get_header() {
    include THEME_DIR . '/header.php';
}

/**
 * Include footer template
 */
function get_footer() {
    include THEME_DIR . '/footer.php';
}

/**
 * WordPress-style functions for compatibility
 */
function esc_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_attr($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function sanitize_text_field($str) {
    return trim(strip_tags($str));
}

// WordPress core functions simulation
if (!function_exists('language_attributes')) {
    function language_attributes() {
        echo 'lang="en"';
    }
}

if (!function_exists('bloginfo')) {
    function bloginfo($show = '') {
        switch ($show) {
            case 'charset':
                echo 'UTF-8';
                break;
            case 'name':
                echo 'Newsroom Training Platform';
                break;
            default:
                echo 'Newsroom Training Platform';
        }
    }
}

if (!function_exists('body_class')) {
    function body_class($class = '') {
        echo 'class="' . esc_attr($class) . '"';
    }
}

if (!function_exists('wp_head')) {
    function wp_head() {
        newsroom_enqueue_assets();
    }
}

if (!function_exists('wp_footer')) {
    function wp_footer() {
        newsroom_enqueue_scripts();
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '') {
        return $path ? $path : '/';
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        $user = getCurrentUser();
        if (!$user) return false;
        
        $role_caps = [
            'operator' => [
                'edit_posts', 'publish_posts', 'delete_posts', 'manage_content',
                'manage_users', 'edit_news', 'edit_social', 'admin_access',
                'manage_training_content'
            ],
            'trainee' => [
                'read_content'
            ]
        ];
        
        $user_role = $user['role'] ?? 'trainee';
        $caps = $role_caps[$user_role] ?? [];
        
        return in_array($capability, $caps);
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        $user = getCurrentUser();
        return (object) [
            'display_name' => $user ? $user['name'] : 'User',
            'user_login' => $user ? $user['email'] : '',
        ];
    }
}

if (!function_exists('wp_logout_url')) {
    function wp_logout_url($redirect = '') {
        return 'logout.php' . ($redirect ? '?redirect=' . urlencode($redirect) : '');
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return 'admin.php' . ($path ? '?' . $path : '');
    }
}
?>