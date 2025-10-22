<?php
/**
 * Newsroom Training Theme Functions
 * WordPress theme functions file
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme version
define('THEME_VERSION', '2.0.0');

/**
 * Theme setup
 */
function newsroom_theme_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'newsroom-training'),
        'footer' => __('Footer Menu', 'newsroom-training')
    ));
}
add_action('after_setup_theme', 'newsroom_theme_setup');

/**
 * Enqueue scripts and styles
 */
function newsroom_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('newsroom-style', get_stylesheet_uri(), array(), THEME_VERSION);
    
    // Enqueue scripts
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array(), '5.1.3', true);
    wp_enqueue_script('newsroom-main', get_template_directory_uri() . '/assets/js/main.js', array('bootstrap-js'), THEME_VERSION, true);
    wp_enqueue_script('newsroom-realtime', get_template_directory_uri() . '/assets/js/realtime-updates.js', array('newsroom-main'), THEME_VERSION . '-v7', true);
    
    // Localize script for AJAX - attach to real-time script to ensure it's available
    wp_localize_script('newsroom-realtime', 'newsroom_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('newsroom_nonce'),
        'add_comment_nonce' => wp_create_nonce('add_comment_inline_nonce'),
        'get_comments_nonce' => wp_create_nonce('get_comments_nonce'),
        'wp_current_time' => current_time('Y-m-d H:i:s'),
        'wp_timezone_offset' => get_option('gmt_offset')
    ));
}
add_action('wp_enqueue_scripts', 'newsroom_enqueue_scripts');

/**
 * Add admin menu for newsroom management
 */
function newsroom_admin_menu() {
    // Main menu page
    add_menu_page(
        __('Newsroom Training', 'newsroom-training'),
        __('Newsroom Training', 'newsroom-training'),
        'edit_posts',
        'newsroom-training',
        'newsroom_admin_dashboard_page',
        'dashicons-media-document',
        30
    );
    
    // Dashboard submenu
    add_submenu_page(
        'newsroom-training',
        __('Admin Panel', 'newsroom-training'),
        __('Admin Panel', 'newsroom-training'),
        'edit_posts',
        'newsroom-training',
        'newsroom_admin_dashboard_page'
    );
    
    // Create Content submenu
    add_submenu_page(
        'newsroom-training',
        __('Create Content', 'newsroom-training'),
        __('Create Content', 'newsroom-training'),
        'edit_posts',
        'newsroom-create-content',
        'newsroom_admin_create_content_page'
    );
}
add_action('admin_menu', 'newsroom_admin_menu');

/**
 * Register custom post types
 */
function newsroom_register_post_types() {
    // News Articles
 function newsroom_register_news_articles() {
    register_post_type('news_article', [
        'labels' => [
            'name'          => __('News Articles'),
            'singular_name' => __('News Article'),
            'add_new_item'  => __('Add News Article'),
            'edit_item'     => __('Edit News Article'),
            'new_item'      => __('New News Article'),
            'view_item'     => __('View News Article'),
            'search_items'  => __('Search News Articles'),
        ],
        'public'        => true,
        'has_archive'   => true,
        'show_in_menu'  => 'newsroom-training',
        'menu_icon'     => 'dashicons-media-document',
        'supports'      => ['title', 'editor', 'author', 'thumbnail', 'custom-fields'],
        'capability_type' => 'news_article',
        'map_meta_cap'    => true,
    ]);
}
add_action('init', 'newsroom_register_news_articles');
    // Social Media Posts
    register_post_type('social_post', array(
        'labels' => array(
            'name' => __('Social Posts', 'newsroom-training'),
            'singular_name' => __('Social Post', 'newsroom-training'),
            'add_new' => __('Add New Post', 'newsroom-training'),
            'add_new_item' => __('Add New Social Post', 'newsroom-training'),
            'edit_item' => __('Edit Social Post', 'newsroom-training'),
            'new_item' => __('New Social Post', 'newsroom-training'),
            'view_item' => __('View Social Post', 'newsroom-training'),
            'search_items' => __('Search Social Posts', 'newsroom-training'),
            'not_found' => __('No social posts found', 'newsroom-training'),
            'not_found_in_trash' => __('No social posts found in trash', 'newsroom-training')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'menu_icon' => 'dashicons-share',
        'show_in_rest' => true
    ));
}
add_action('init', 'newsroom_register_post_types');

/**
 * Register taxonomies
 */
function newsroom_register_taxonomies() {
    // News Categories
function newsroom_register_news_category() {
    register_taxonomy('news_category', 'news_article', [
        'label'        => __('News Categories'),
        'rewrite'      => ['slug' => 'news-category'],
        'hierarchical' => true,
        'public'       => true,
        'show_admin_column' => true,
    ]);
}
add_action('init', 'newsroom_register_news_category');
    
    // Social Media Platforms
    register_taxonomy('social_platform', 'social_post', array(
        'labels' => array(
            'name' => __('Social Platforms', 'newsroom-training'),
            'singular_name' => __('Social Platform', 'newsroom-training'),
            'search_items' => __('Search Platforms', 'newsroom-training'),
            'all_items' => __('All Platforms', 'newsroom-training'),
            'edit_item' => __('Edit Platform', 'newsroom-training'),
            'update_item' => __('Update Platform', 'newsroom-training'),
            'add_new_item' => __('Add New Platform', 'newsroom-training'),
            'new_item_name' => __('New Platform Name', 'newsroom-training')
        ),
        'hierarchical' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'platform'),
        'show_in_rest' => true
    ));
    
    // Training Scenarios
    register_taxonomy('training_scenario', array('news_article', 'social_post'), array(
        'labels' => array(
            'name' => __('Training Scenarios', 'newsroom-training'),
            'singular_name' => __('Training Scenario', 'newsroom-training'),
            'search_items' => __('Search Scenarios', 'newsroom-training'),
            'all_items' => __('All Scenarios', 'newsroom-training'),
            'edit_item' => __('Edit Scenario', 'newsroom-training'),
            'update_item' => __('Update Scenario', 'newsroom-training'),
            'add_new_item' => __('Add New Scenario', 'newsroom-training'),
            'new_item_name' => __('New Scenario Name', 'newsroom-training')
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'scenario'),
        'show_in_rest' => true
    ));
}
add_action('init', 'newsroom_register_taxonomies');

function newsroom_handle_news_publish() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsroom_action'])) {
        if ($_POST['newsroom_action'] === 'publish_news') {

            if (!isset($_POST['newsroom_quick_post_nonce']) ||
                !wp_verify_nonce($_POST['newsroom_quick_post_nonce'], 'newsroom_quick_post')) {
                echo '<div class="notice notice-error"><p>Security check failed.</p></div>';
                return;
            }

            $post_data = [
                'post_title'   => sanitize_text_field($_POST['headline']),
                'post_content' => sanitize_textarea_field($_POST['body']),
                'post_status'  => 'publish',
                'post_type'    => 'news_article',
            ];

            $post_id = wp_insert_post($post_data);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, 'author', sanitize_text_field($_POST['author']));

                if (!empty($_POST['category'])) {
                    wp_set_post_terms($post_id, [sanitize_text_field($_POST['category'])], 'news_category');
                }

                echo '<div class="notice notice-success"><p>✅ News article published!</p></div>';
            } else {
                $err = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
                echo '<div class="notice notice-error"><p>❌ Failed to publish: ' . esc_html($err) . '</p></div>';
            }
        }
    }
}
add_action('admin_init', 'newsroom_handle_news_publish');


/**
 * Add custom user roles
 */
function newsroom_add_user_roles() {
    add_role('newsroom_operator', __('Newsroom Operator', 'newsroom-training'), array(
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => true,
        'publish_posts' => true,
        'upload_files' => true,
        'edit_news_articles' => true,
        'edit_social_posts' => true,
        'manage_training_content' => true
    ));
    
    add_role('newsroom_trainee', __('Newsroom Trainee', 'newsroom-training'), array(
        'read' => true
    ));
}
add_action('init', 'newsroom_add_user_roles');

function newsroom_add_caps_to_operator() {
    $role = get_role('newsroom_operator');
    if (!$role) return;

    $caps = [
        'read_news_article',
        'read_private_news_articles',
        'edit_news_article',
        'edit_news_articles',
        'edit_others_news_articles',
        'publish_news_articles',
        'delete_news_article',
        'delete_news_articles',
        'delete_others_news_articles',
    ];

    foreach ($caps as $cap) {
        $role->add_cap($cap);
    }
}
add_action('init', 'newsroom_add_caps_to_operator');


/**
 * Custom meta boxes
 */
function newsroom_add_meta_boxes() {
    // News article meta box
    add_meta_box(
        'news_details',
        __('News Details', 'newsroom-training'),
        'newsroom_news_meta_box_callback',
        'news_article',
        'normal',
        'high'
    );
    
    // Social post meta box
    add_meta_box(
        'social_details',
        __('Social Media Details', 'newsroom-training'),
        'newsroom_social_meta_box_callback',
        'social_post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'newsroom_add_meta_boxes');

/**
 * News meta box callback
 */
function newsroom_news_meta_box_callback($post) {
    wp_nonce_field('newsroom_save_meta', 'newsroom_meta_nonce');
    
    $breaking = get_post_meta($post->ID, '_news_breaking', true);
    $author_name = get_post_meta($post->ID, '_news_author', true);
    $featured_image = get_post_meta($post->ID, '_news_featured_image', true);
    $video_embed = get_post_meta($post->ID, '_news_video_embed', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="news_breaking"><?php _e('Breaking News', 'newsroom-training'); ?></label></th>
            <td>
                <input type="checkbox" id="news_breaking" name="news_breaking" value="1" <?php checked($breaking, '1'); ?> />
                <label for="news_breaking"><?php _e('Mark as breaking news', 'newsroom-training'); ?></label>
            </td>
        </tr>
        <tr>
            <th><label for="news_author"><?php _e('Author Name', 'newsroom-training'); ?></label></th>
            <td><input type="text" id="news_author" name="news_author" value="<?php echo esc_attr($author_name); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="news_featured_image"><?php _e('Featured Image URL', 'newsroom-training'); ?></label></th>
            <td><input type="url" id="news_featured_image" name="news_featured_image" value="<?php echo esc_url($featured_image); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="news_video_embed"><?php _e('Video Embed Code', 'newsroom-training'); ?></label></th>
            <td><textarea id="news_video_embed" name="news_video_embed" rows="3" class="large-text"><?php echo esc_textarea($video_embed); ?></textarea></td>
        </tr>
    </table>
    <?php
}

/**
 * Social post meta box callback
 */
function newsroom_social_meta_box_callback($post) {
    wp_nonce_field('newsroom_save_meta', 'newsroom_meta_nonce');
    
    $platform = get_post_meta($post->ID, '_social_platform', true);
    $display_name = get_post_meta($post->ID, '_social_display_name', true);
    $handle = get_post_meta($post->ID, '_social_handle', true);
    $avatar = get_post_meta($post->ID, '_social_avatar', true);
    $media = get_post_meta($post->ID, '_social_media', true);
    $likes = get_post_meta($post->ID, '_social_likes', true);
    $comments = get_post_meta($post->ID, '_social_comments', true);
    $retweets = get_post_meta($post->ID, '_social_retweets', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="social_platform"><?php _e('Platform', 'newsroom-training'); ?></label></th>
            <td>
                <select id="social_platform" name="social_platform">
                    <option value="twitter" <?php selected($platform, 'twitter'); ?>>Twitter</option>
                    <option value="facebook" <?php selected($platform, 'facebook'); ?>>Facebook</option>
                    <option value="instagram" <?php selected($platform, 'instagram'); ?>>Instagram</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="social_display_name"><?php _e('Display Name', 'newsroom-training'); ?></label></th>
            <td><input type="text" id="social_display_name" name="social_display_name" value="<?php echo esc_attr($display_name); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="social_handle"><?php _e('Handle/Username', 'newsroom-training'); ?></label></th>
            <td><input type="text" id="social_handle" name="social_handle" value="<?php echo esc_attr($handle); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="social_avatar"><?php _e('Avatar URL', 'newsroom-training'); ?></label></th>
            <td><input type="url" id="social_avatar" name="social_avatar" value="<?php echo esc_url($avatar); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="social_media"><?php _e('Media URL', 'newsroom-training'); ?></label></th>
            <td><input type="url" id="social_media" name="social_media" value="<?php echo esc_url($media); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="social_likes"><?php _e('Likes Count', 'newsroom-training'); ?></label></th>
            <td><input type="number" id="social_likes" name="social_likes" value="<?php echo esc_attr($likes); ?>" class="small-text" min="0" /></td>
        </tr>
        <tr>
            <th><label for="social_comments"><?php _e('Comments Count', 'newsroom-training'); ?></label></th>
            <td><input type="number" id="social_comments" name="social_comments" value="<?php echo esc_attr($comments); ?>" class="small-text" min="0" /></td>
        </tr>
        <tr>
            <th><label for="social_retweets"><?php _e('Retweets/Shares Count', 'newsroom-training'); ?></label></th>
            <td><input type="number" id="social_retweets" name="social_retweets" value="<?php echo esc_attr($retweets); ?>" class="small-text" min="0" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Save meta box data
 */
function newsroom_save_meta_box_data($post_id) {
    if (!isset($_POST['newsroom_meta_nonce']) || !wp_verify_nonce($_POST['newsroom_meta_nonce'], 'newsroom_save_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save news meta
    if (get_post_type($post_id) === 'news_article') {
        update_post_meta($post_id, '_news_breaking', isset($_POST['news_breaking']) ? '1' : '0');
        update_post_meta($post_id, '_news_author', sanitize_text_field($_POST['news_author']));
        update_post_meta($post_id, '_news_featured_image', esc_url_raw($_POST['news_featured_image']));
        update_post_meta($post_id, '_news_video_embed', wp_kses_post($_POST['news_video_embed']));
    }
    
    // Save social meta
    if (get_post_type($post_id) === 'social_post') {
        update_post_meta($post_id, '_social_platform', sanitize_text_field($_POST['social_platform']));
        update_post_meta($post_id, '_social_display_name', sanitize_text_field($_POST['social_display_name']));
        update_post_meta($post_id, '_social_handle', sanitize_text_field($_POST['social_handle']));
        update_post_meta($post_id, '_social_avatar', esc_url_raw($_POST['social_avatar']));
        update_post_meta($post_id, '_social_media', esc_url_raw($_POST['social_media']));
        update_post_meta($post_id, '_social_likes', intval($_POST['social_likes']));
        update_post_meta($post_id, '_social_comments', intval($_POST['social_comments']));
        update_post_meta($post_id, '_social_retweets', intval($_POST['social_retweets']));
    }
}
add_action('save_post', 'newsroom_save_meta_box_data');

/**
 * Restrict access to training content
 */
function newsroom_restrict_access() {
    if (!is_admin() && !is_user_logged_in()) {
        $restricted_pages = array('news_article', 'social_post');
        
        if (is_singular($restricted_pages) || is_post_type_archive($restricted_pages)) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
    }
}
add_action('template_redirect', 'newsroom_restrict_access');

/**
 * Add query vars for filtering
 */
function newsroom_add_query_vars($vars) {
    $vars[] = 'filter';
    $vars[] = 'scenario';
    return $vars;
}
add_filter('query_vars', 'newsroom_add_query_vars');

/**
 * Handle access restrictions
 */
function newsroom_access_check() {
    // Skip for admin areas and logged in users
    if (is_admin() || is_user_logged_in()) {
        return;
    }
    
    // Redirect to login for content
    if (is_home() || is_front_page() || is_singular(array('news_article', 'social_post'))) {
        wp_redirect(wp_login_url(get_permalink()));
        exit;
    }
}
add_action('template_redirect', 'newsroom_access_check');

// Redirect after login based on role
// Force custom login redirect regardless of ?redirect_to=
// Force custom login redirect regardless of ?redirect_to= (runs late)
function newsroom_login_redirect($redirect_to, $request, $user) {
    // If we don't have a real user yet, send to home
    if (!isset($user->roles) || !is_array($user->roles)) {
        return home_url('/');
    }

    // Admins & Editors -> Dashboard
    if (in_array('administrator', $user->roles, true) || in_array('editor', $user->roles, true)) {
        return admin_url();
    }

    // Everyone else -> Homepage
    return home_url('/');
}
add_filter('login_redirect', 'newsroom_login_redirect', 100, 3);

/**
 * ALWAYS send users to the front site after login (even admins).
 * Works for wp-login.php, wp-admin, and custom forms.
 */
function newsroom_login_redirect_all( $redirect_to, $requested, $user ) {
    if ( is_wp_error( $user ) || empty( $user ) ) {
        return $redirect_to; // failed login
    }

    // Send everyone to the homepage (change to a page if you want)
    return home_url('/');
}
add_filter( 'login_redirect', 'newsroom_login_redirect_all', 999, 3 );

/**
 * If a user logs in by visiting /wp-admin first, WP tries to drop them on the Dashboard.
 * We mark the session at login, and on the first admin load we bounce them to the front.
 * Later visits to /wp-admin will work normally.
 */
function newsroom_mark_just_logged_in( $user_login, $user ) {
    set_transient( 'newsroom_just_logged_in_' . $user->ID, 1, 60 ); // 1 minute is enough
}
add_action( 'wp_login', 'newsroom_mark_just_logged_in', 10, 2 );

function newsroom_redirect_admin_once_after_login() {
    if ( ! is_user_logged_in() || ! is_admin() ) {
        return;
    }

    // don't interfere with AJAX or post actions
    if ( defined('DOING_AJAX') && DOING_AJAX ) return;

    $user = wp_get_current_user();
    if ( get_transient( 'newsroom_just_logged_in_' . $user->ID ) ) {
        delete_transient( 'newsroom_just_logged_in_' . $user->ID );
        wp_safe_redirect( home_url('/') ); // change if you want a specific page
        exit;
    }
}
add_action( 'admin_init', 'newsroom_redirect_admin_once_after_login' );

// Final redirect after WordPress authenticates the user
function newsroom_redirect_after_login($user_login, $user) {
    if (user_can($user, 'administrator') || user_can($user, 'editor')) {
        wp_safe_redirect(admin_url());
    } else {
        wp_safe_redirect(home_url('/'));
    }
    exit;
}
add_action('wp_login', 'newsroom_redirect_after_login', 10, 2);

// If already logged in, never show login/register pages
function newsroom_bounce_logged_in_from_auth_pages() {
    if (!is_user_logged_in()) return;

    // Adjust these if your slugs differ
    if (function_exists('is_page') && (is_page('login') || is_page('register'))) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
}
add_action('template_redirect', 'newsroom_bounce_logged_in_from_auth_pages');

// Keep non-admins out of /wp-admin (they'll still be able to log in)
add_action('admin_init', function () {
    if (!current_user_can('administrator') && !wp_doing_ajax()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
});

// After logout, go to homepage
add_filter('logout_redirect', function($redirect_to, $requested, $user){
    return home_url('/');
}, 10, 3);

/**
 * Add default avatar for social posts
 */
function newsroom_create_default_avatar() {
    $upload_dir = wp_upload_dir();
    $avatar_dir = $upload_dir['basedir'] . '/newsroom-avatars';
    
    if (!file_exists($avatar_dir)) {
        wp_mkdir_p($avatar_dir);
    }
    
    // Create a simple default avatar SVG
    $default_avatar = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="50" fill="#6c757d"/>
    <circle cx="50" cy="35" r="18" fill="#fff"/>
    <ellipse cx="50" cy="85" rx="25" ry="20" fill="#fff"/>
</svg>';
    
    $avatar_file = $avatar_dir . '/default-avatar.svg';
    if (!file_exists($avatar_file)) {
        file_put_contents($avatar_file, $default_avatar);
    }
}
add_action('after_switch_theme', 'newsroom_create_default_avatar');

function newsroom_pending_user_approvals() {
    $pending_users = get_users([
        'meta_key'   => 'newsroom_status',
        'meta_value' => 'pending'
    ]);

    if (empty($pending_users)) {
        echo '<p style="text-align: center;">No pending user approvals</p>';
        return;
    }

    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>Username</th><th>Email</th><th>Actions</th></tr></thead><tbody>';

    foreach ($pending_users as $user) {
        echo '<tr>';
        echo '<td>' . esc_html($user->user_login) . '</td>';
        echo '<td>' . esc_html($user->user_email) . '</td>';
        echo '<td>';
        echo '<a class="button button-primary" href="' . esc_url(admin_url("admin.php?page=newsroom-training&approve_user={$user->ID}")) . '">Approve</a> ';
        echo '<a class="button button-secondary" href="' . esc_url(admin_url("admin.php?page=newsroom-training&reject_user={$user->ID}")) . '">Reject</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
add_action('admin_init', 'newsroom_process_user_approval');

function newsroom_process_user_approval() {
    if (!current_user_can('manage_options')) return;

    // Approve
    if (isset($_GET['approve_user'])) {
        $user_id = intval($_GET['approve_user']);
        update_user_meta($user_id, 'newsroom_status', 'approved');

        $user = new WP_User($user_id);
        $user->set_role('trainee'); // Give trainee role

        wp_redirect(admin_url('admin.php?page=newsroom-training&approved=1'));
        exit;
    }

    // Reject
    if (isset($_GET['reject_user'])) {
        $user_id = intval($_GET['reject_user']);
        update_user_meta($user_id, 'newsroom_status', 'rejected');

        $user = new WP_User($user_id);
        $user->set_role(''); // Remove role

        wp_redirect(admin_url('admin.php?page=newsroom-training&rejected=1'));
        exit;
    }
}
add_filter('authenticate', 'newsroom_block_rejected_users', 30, 3);

function newsroom_block_rejected_users($user, $username, $password) {
    if ($user instanceof WP_User) {
        $status = get_user_meta($user->ID, 'newsroom_status', true);

        if ($status === 'pending') {
            return new WP_Error('pending_approval', 'Your account is pending admin approval.');
        }

        if ($status === 'rejected') {
            return new WP_Error('account_rejected', 'Your account was rejected by the admin.');
        }
    }
    return $user;
}

// Create "Trainee" role on theme/plugin activation
function newsroom_add_trainee_role() {
    add_role('trainee', 'Trainee', [
        'read' => true, // Allow reading
        // No post editing/publishing
    ]);
}
add_action('init', 'newsroom_add_trainee_role');

// On new user registration → mark as pending + no role
add_action('user_register', 'newsroom_mark_user_pending', 100);
function newsroom_mark_user_pending($user_id) {
    update_user_meta($user_id, 'newsroom_status', 'pending');

    // Remove all roles so user cannot login
    $user = new WP_User($user_id);
    $user->set_role('');
}

add_filter('authenticate', 'newsroom_block_unapproved_users', 30, 3);
function newsroom_block_unapproved_users($user, $username, $password) {
    if ($user instanceof WP_User) {
        $status = get_user_meta($user->ID, 'newsroom_status', true);

        if ($status === 'pending') {
            return new WP_Error('pending_approval', 'Your account is pending admin approval.');
        }
        if ($status === 'rejected') {
            return new WP_Error('account_rejected', 'Your account has been rejected by admin.');
        }
    }
    return $user;
}
// Hide WP Admin Bar on the front-end for all users
add_filter('show_admin_bar', '__return_false');

// Also remove the top margin WP adds when the bar is present
add_action('get_header', function () {
    remove_action('wp_head', '_admin_bar_bump_cb');
});
/**
 * Initialize default content and settings
 */
function newsroom_theme_activation() {
    // Flush rewrite rules
    newsroom_register_post_types();
    newsroom_register_taxonomies();
    flush_rewrite_rules();
    
    // Create default avatar
    newsroom_create_default_avatar();
    
    // Set default permalink structure
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
    $wp_rewrite->flush_rules();
}
add_action('after_switch_theme', 'newsroom_theme_activation');

/**
 * Admin dashboard page - Only works in WordPress admin
 */
function newsroom_admin_dashboard_page() {
    // Ensure we're in WordPress admin
    if (!is_admin() || !function_exists('wp_count_posts')) {
        wp_die(__('This page can only be accessed from WordPress admin.', 'newsroom-training'));
    }
    
    $news_count = wp_count_posts('news_article');
    $social_count = wp_count_posts('social_post');
    $user_count = count_users();
    $operators = get_users(array('role' => 'newsroom_operator'));
    
    // Get recent content
    $recent_news = get_posts(array(
        'post_type' => 'news_article',
        'numberposts' => 10,
        'post_status' => 'publish'
    ));
    
    $recent_social = get_posts(array(
        'post_type' => 'social_post', 
        'numberposts' => 10,
        'post_status' => 'publish'
    ));
    ?>
    
    <!-- Add Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <div class="wrap">
        <h1><?php _e('Admin Panel', 'newsroom-training'); ?></h1>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $user_count['total_users']; ?></h4>
                                <p class="mb-0">Active Users</p>
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>0</h4>
                                <p class="mb-0">Pending Approvals</p>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                               <h4>
    <?php
    $news_total   = isset($news_count->publish) ? $news_count->publish : 0;
    $social_total = isset($social_count->publish) ? $social_count->publish : 0;
    echo $news_total + $social_total;
    ?>
</h4>
                                <p class="mb-0">Total Posts</p>
                            </div>
                            <div>
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count($operators); ?></h4>
                                <p class="mb-0">Operators</p>
                            </div>
                            <div>
                                <i class="fas fa-user-cog fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- No Pending Approvals (WordPress handles user registration differently) -->
            <div class="col-lg-8" style=" border: 1px solid #dee2e6;border-radius: 0.375rem;">
                <div class="wrap">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-check me-2"></i>
                            Pending User Approvals
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="py-4 text-muted">
                            <!-- <i class="fas fa-check-circle fa-3x mb-3"></i> -->
                            <?php newsroom_pending_user_approvals(); ?>
                            <!--<small>User registration is handled by WordPress admin</small> -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo admin_url('admin.php?page=newsroom-create-content'); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Content
                            </a>
                         <!--   <a href="<?php echo admin_url('edit.php?post_type=news_article'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                            <a href="<?php echo admin_url('edit.php?post_type=social_post'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-share me-2"></i>Manage Social
                            </a> -->
                        </div>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            System Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Platform:</strong> Newsroom Training<br>
                            <strong>Version:</strong> 2.0<br>
                            <strong>Last Updated:</strong> <?php echo date('M j, Y'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Users Table -->
        <div class="row mt-4" style=" border: 1px solid #dee2e6;border-radius: 0.375rem;">
            <div class="col-12">
                <div class="wrap">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            Active Users
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $all_users = get_users();
                                    foreach ($all_users as $user): 
                                    ?>
                                        <tr>
                                            <td><?php echo esc_html($user->display_name); ?></td>
                                            <td><?php echo esc_html($user->user_email); ?></td>
                                            <td>
                                                <?php 
                                                $roles = $user->roles;
                                                $role = reset($roles);
                                                $badge_class = in_array($role, ['newsroom_operator', 'administrator']) ? 'primary' : 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>">
                                                    <?php echo esc_html(ucfirst(str_replace('newsroom_', '', $role))); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($user->user_registered)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .card { margin-bottom: 1rem; border: 1px solid #dee2e6; border-radius: 0.375rem; }
        .card-body { padding: 1.25rem; }
        .card-header { padding: 0.75rem 1.25rem; background-color: rgba(0,0,0,.03); border-bottom: 1px solid rgba(0,0,0,.125); }
        .bg-primary { background-color: #0d6efd !important; }
        .bg-warning { background-color: #ffc107 !important; }
        .bg-success { background-color: #198754 !important; }
        .bg-info { background-color: #0dcaf0 !important; }
        .text-white { color: #fff !important; }
        .btn { padding: 0.375rem 0.75rem; margin-bottom: 0.5rem; text-decoration: none; border-radius: 0.375rem; display: inline-block; }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
        .btn-outline-secondary { border: 1px solid #6c757d; color: #6c757d; }
        .d-grid { display: grid !important; }
        .gap-2 { gap: 0.5rem !important; }
        .table { width: 100%; margin-bottom: 1rem; vertical-align: top; border-color: #dee2e6; }
        .table th, .table td { padding: 0.5rem; border-top: 1px solid #dee2e6; }
        .badge { padding: 0.35em 0.65em; font-size: 0.75em; border-radius: 0.375rem; }
    </style>
    <?php
}

/**
 * Create Content admin page - Only works in WordPress admin
 */
function newsroom_admin_create_content_page() {
    // Ensure we're in WordPress admin
    if (!is_admin() || !function_exists('wp_verify_nonce')) {
        wp_die(__('This page can only be accessed from WordPress admin.', 'newsroom-training'));
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsroom_nonce']) && wp_verify_nonce($_POST['newsroom_nonce'], 'newsroom_quick_post')) {
        $post_type = sanitize_text_field($_POST['post_type']);
        
        if ($post_type === 'news_article') {
            $post_data = array(
                'post_title' => sanitize_text_field($_POST['headline']),
                'post_content' => wp_kses_post($_POST['body']),
                'post_type' => 'news_article',
                'post_status' => 'publish',
                'meta_input' => array(
                    '_news_author' => sanitize_text_field($_POST['author']),
                    '_news_breaking' => isset($_POST['breaking']) ? '1' : '0',
                    '_news_featured_image' => esc_url_raw($_POST['featured_image']),
                    '_news_video_embed' => sanitize_textarea_field($_POST['video_embed'])
                )
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id && !is_wp_error($post_id)) {
                if (!empty($_POST['category'])) {
                    wp_set_post_terms($post_id, array($_POST['category']), 'news_category');
                }
                echo '<div class="notice notice-success"><p>' . __('News article created successfully!', 'newsroom-training') . '</p></div>';
            }
        } elseif (in_array($post_type, array('social_twitter', 'social_facebook', 'social_instagram'))) {
            $platform = str_replace('social_', '', $post_type);
            
            $post_data = array(
                'post_title' => sanitize_text_field($_POST['display_name']) . ' - ' . ucfirst($platform) . ' Post',
                'post_content' => wp_kses_post($_POST['text']),
                'post_type' => 'social_post',
                'post_status' => 'publish',
                'meta_input' => array(
                    '_social_platform' => $platform,
                    '_social_display_name' => sanitize_text_field($_POST['display_name']),
                    '_social_handle' => sanitize_text_field($_POST['handle']),
                    '_social_avatar' => esc_url_raw($_POST['avatar']),
                    '_social_media' => esc_url_raw($_POST['media']),
                    // '_social_likes' => intval($_POST['likes']),
                    // '_social_comments' => intval($_POST['comments']),
                    // '_social_retweets' => intval($_POST['retweets'])
                )
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id && !is_wp_error($post_id)) {
                wp_set_post_terms($post_id, array($platform), 'social_platform');
                echo '<div class="notice notice-success"><p>' . ucfirst($platform) . __(' post created successfully!', 'newsroom-training') . '</p></div>';
            }
        }
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Create Content', 'newsroom-training'); ?></h1>
        
        <!-- Content Type Tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="#news-tab" class="nav-tab nav-tab-active" id="news-tab-btn"><?php _e('News Article', 'newsroom-training'); ?></a>
            <a href="#twitter-tab" class="nav-tab" id="twitter-tab-btn"><?php _e('X (Twitter)', 'newsroom-training'); ?></a>
            <a href="#facebook-tab" class="nav-tab" id="facebook-tab-btn"><?php _e('Facebook', 'newsroom-training'); ?></a>
            <a href="#instagram-tab" class="nav-tab" id="instagram-tab-btn"><?php _e('Instagram', 'newsroom-training'); ?></a>
        </h2>
        
        <!-- News Article Form -->
        <div id="news-tab-content" class="tab-content active">
            <form method="post" action="">
                <?php wp_nonce_field('newsroom_quick_post', 'newsroom_nonce'); ?>
                <input type="hidden" name="post_type" value="news_article">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="headline"><?php _e('Headline', 'newsroom-training'); ?> *</label></th>
                        <td><input type="text" id="headline" name="headline" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="author"><?php _e('Author', 'newsroom-training'); ?> *</label></th>
                        <td><input type="text" id="author" name="author" class="regular-text" placeholder="Author alias" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="body"><?php _e('Article Body', 'newsroom-training'); ?> *</label></th>
                        <td><textarea id="body" name="body" rows="8" class="large-text" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="category"><?php _e('Category', 'newsroom-training'); ?></label></th>
                        <td>
                            <select id="category" name="category">
                                <option value=""><?php _e('Select Category', 'newsroom-training'); ?></option>
                                <option value="Breaking News"><?php _e('Breaking News', 'newsroom-training'); ?></option>
                                <option value="Politics"><?php _e('Politics', 'newsroom-training'); ?></option>
                                <option value="Business"><?php _e('Business', 'newsroom-training'); ?></option>
                                <option value="Technology"><?php _e('Technology', 'newsroom-training'); ?></option>
                                <option value="Sports"><?php _e('Sports', 'newsroom-training'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="featured_image"><?php _e('Featured Image URL', 'newsroom-training'); ?></label></th>
                        <td><input type="url" id="featured_image" name="featured_image" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="video_embed"><?php _e('Video Embed URL', 'newsroom-training'); ?></label></th>
                        <td><input type="url" id="video_embed" name="video_embed" class="regular-text" placeholder="YouTube or Vimeo URL"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breaking"><?php _e('Breaking News', 'newsroom-training'); ?></label></th>
                        <td><input type="checkbox" id="breaking" name="breaking" value="1"> <?php _e('Mark as breaking news', 'newsroom-training'); ?></td>
                    </tr>
                </table>
                
                <?php submit_button(__('Publish Article', 'newsroom-training')); ?>
            </form>
        </div>        
        <!-- Social Media Forms -->
        <?php foreach (array('twitter', 'facebook', 'instagram') as $platform): ?>
        <div id="<?php echo $platform; ?>-tab-content" class="tab-content">
            <form method="post" action="">
                <?php wp_nonce_field('newsroom_quick_post', 'newsroom_nonce'); ?>
                <input type="hidden" name="post_type" value="social_<?php echo $platform; ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_display_name"><?php _e('Display Name', 'newsroom-training'); ?> *</label></th>
                        <td><input type="text" id="<?php echo $platform; ?>_display_name" name="display_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_handle"><?php _e('Handle/Username', 'newsroom-training'); ?> *</label></th>
                        <td><input type="text" id="<?php echo $platform; ?>_handle" name="handle" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_text"><?php _e('Post Text', 'newsroom-training'); ?> *</label></th>
                        <td><textarea id="<?php echo $platform; ?>_text" name="text" rows="4" class="large-text" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_avatar"><?php _e('Avatar URL', 'newsroom-training'); ?></label></th>
                        <td><input type="url" id="<?php echo $platform; ?>_avatar" name="avatar" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_media"><?php _e('Media URL', 'newsroom-training'); ?></label></th>
                        <td><input type="url" id="<?php echo $platform; ?>_media" name="media" class="regular-text"></td>
                    </tr>
                    <!-- <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_likes"><?php _e('Likes', 'newsroom-training'); ?></label></th>
                        <td><input type="number" id="<?php echo $platform; ?>_likes" name="likes" class="small-text" value="0" min="0"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_comments"><?php _e('Comments', 'newsroom-training'); ?></label></th>
                        <td><input type="number" id="<?php echo $platform; ?>_comments" name="comments" class="small-text" value="0" min="0"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="<?php echo $platform; ?>_retweets"><?php _e('Retweets/Shares', 'newsroom-training'); ?></label></th>
                        <td><input type="number" id="<?php echo $platform; ?>_retweets" name="retweets" class="small-text" value="0" min="0"></td>
                    </tr> -->
                </table>
                
                <?php submit_button(__('Publish Post', 'newsroom-training')); ?>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .nav-tab-active { background: #fff !important; }
    </style>
    
    <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').removeClass('active');
                $(this).addClass('nav-tab-active');
                var target = $(this).attr('href') + '-content';
                $(target).addClass('active');
            });
        });
    </script>
    <?php
}
/**
 * --- FIX: keep Create Content working & avoid duplicate CPT ---
 * 1) Remove any old "newsroom_register_news_articles" registration
 * 2) Ensure our admin menu slugs exist
 * 3) Register News Article CPT exactly once and attach it under the Newsroom menu
 */

// 1) If an older CPT registration was hooked, unhook it before init runs.
add_action('after_setup_theme', function () {
    // This line harmlessly does nothing if the action doesn’t exist.
    remove_action('init', 'newsroom_register_news_articles');
    // Also remove any previous duplicate function you added
    remove_action('init', 'newsroom_register_news_article_cpt');
});

// 2) Make sure the admin menu uses these slugs.
add_action('admin_menu', function () {
    // Top level (already in your theme, shown here for reference):
    // add_menu_page(
    //     __('Newsroom Training', 'newsroom-training'),
    //     __('Newsroom Training', 'newsroom-training'),
    //     'edit_posts',
    //     'newsroom-training',
    //     'newsroom_admin_dashboard_page',
    //     'dashicons-media-document',
    //     30
    // );

    // Dashboard submenu (kept the same):
    add_submenu_page(
        'newsroom-training',
        __('Admin Panel', 'newsroom-training'),
        __('Admin Panel', 'newsroom-training'),
        'edit_posts',
        'newsroom-training',
        'newsroom_admin_dashboard_page'
    );

    // Create Content submenu (kept the same SLUG)
    add_submenu_page(
        'newsroom-training',
        __('Create Content', 'newsroom-training'),
        __('Create Content', 'newsroom-training'),
        'edit_posts',
        'newsroom-create-content',
        'newsroom_admin_create_content_page'
    );
}, 9); // run early so CPT can attach under this parent

// 3) Single source of truth for the News Article CPT
// Register News Article custom post type (only once)
add_action('init', function () {
    if (post_type_exists('news_article')) {
        return; // Prevent double registration
    }

    $labels = array(
        'name'               => __('News Articles', 'newsroom-training'),
        'singular_name'      => __('News Article', 'newsroom-training'),
        'menu_name'          => __('News Articles', 'newsroom-training'),
        'add_new'            => __('Add New', 'newsroom-training'),
        'add_new_item'       => __('Add New News Article', 'newsroom-training'),
        'edit_item'          => __('Edit News Article', 'newsroom-training'),
        'new_item'           => __('New News Article', 'newsroom-training'),
        'view_item'          => __('View News Article', 'newsroom-training'),
        'all_items'          => __('All News Articles', 'newsroom-training'),
        'search_items'       => __('Search News Articles', 'newsroom-training'),
        'not_found'          => __('No news articles found.', 'newsroom-training'),
        'not_found_in_trash' => __('No news articles found in Trash.', 'newsroom-training'),
    );

    register_post_type('news_article', array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        // Attach under your existing "Newsroom Training" menu
        'show_in_menu'       => 'newsroom-training',
        'query_var'          => true,
        'rewrite'            => array('slug' => 'newsroom-article'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
    ));
});

/**
 * ✅ Frontend routes (without creating Pages)
 */

/**
 * ==========================================================
 * Frontend routes for Newsroom (no Pages needed)
 * - clean URLs
 * - capability-checked
 * - independent of wp-admin callbacks (avoids is_admin() block)
 * ==========================================================
 */

// 1) Rewrite rules → /admin-panel/ and /create-content/
function newsroom_frontend_rewrite_rules() {
    add_rewrite_rule('^admin-panel/?$', 'index.php?newsroom_page=admin_panel', 'top');
    add_rewrite_rule('^create-content/?$', 'index.php?newsroom_page=create_content', 'top');
}
add_action('init', 'newsroom_frontend_rewrite_rules');

// 2) Register query var ?newsroom_page=...
function newsroom_frontend_query_vars($vars) {
    $vars[] = 'newsroom_page';
    return $vars;
}
add_filter('query_vars', 'newsroom_frontend_query_vars');

// 3) Capability helper
function newsroom_frontend_can_access() {
    // Adjust capability if needed (e.g., 'edit_posts' → 'read' for trainees)
    return is_user_logged_in() && current_user_can('edit_posts');
}

// 4) Frontend "Admin Panel" renderer (safe, no is_admin requirement)
function newsroom_frontend_render_admin_panel() {
    // Lightweight dashboard summary (uses your CPTs if they exist)
    $news_count   = 0;
    $social_count = 0;

    if (function_exists('wp_count_posts')) {
        $nc = wp_count_posts('news_article');
        if ($nc && isset($nc->publish)) $news_count = (int) $nc->publish;
        $sc = wp_count_posts('social_post');
        if ($sc && isset($sc->publish)) $social_count = (int) $sc->publish;
    }

    ?>
    <div class="wrap newsroom-frontend-wrap">
        <h1 style="margin: 0 0 16px;">Newsroom Admin Panel (Frontend)</h1>
        <p style="margin:0 0 24px;">This is a frontend view for operators. Your wp-admin screens remain unchanged.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
            <div style="border:1px solid #e2e2e2;border-radius:12px;padding:16px;">
                <h3 style="margin:0 0 6px;">Published News Articles</h3>
                <div style="font-size:28px;line-height:1.1;"><?php echo esc_html($news_count); ?></div>
            </div>
            <div style="border:1px solid #e2e2e2;border-radius:12px;padding:16px;">
                <h3 style="margin:0 0 6px;">Published Social Posts</h3>
                <div style="font-size:28px;line-height:1.1;"><?php echo esc_html($social_count); ?></div>
            </div>
        </div>

        <div style="margin-top:24px;">
            <a href="<?php echo esc_url( home_url('/create-content/') ); ?>" style="display:inline-block;padding:10px 16px;border-radius:8px;border:1px solid #2271b1;text-decoration:none;">
                Create Content →
            </a>
        </div>
    </div>
    <?php
}

// 5) Frontend "Create Content" renderer (safe post creator for your CPT)
function newsroom_frontend_render_create_content() {

    // Handle POST (create a news_article)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsroom_frontend_nonce'])
        && wp_verify_nonce($_POST['newsroom_frontend_nonce'], 'newsroom_frontend_create')
        && current_user_can('edit_posts')) {

        $headline = isset($_POST['headline']) ? sanitize_text_field($_POST['headline']) : '';
        $body     = isset($_POST['body']) ? wp_kses_post($_POST['body']) : '';
        $cat      = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

        $post_id = wp_insert_post(array(
            'post_title'   => $headline,
            'post_content' => $body,
            'post_type'    => 'news_article', // uses your CPT if registered
            'post_status'  => 'publish',
        ));

        if (!is_wp_error($post_id) && $post_id) {
            // If your theme registered 'news_category' taxonomy, assign it
            if ($cat !== '' && taxonomy_exists('news_category')) {
                wp_set_post_terms($post_id, array($cat), 'news_category');
            }

            echo '<div class="notice notice-success" style="margin:16px 0;padding:12px;border-left:4px solid #46b450;background:#f7fff7;">
                    <p style="margin:0;"><strong>Success:</strong> Article created. 
                    <a href="'.esc_url(get_permalink($post_id)).'">View</a> · 
                    <a href="'.esc_url(get_edit_post_link($post_id)).'">Edit</a></p>
                  </div>';
        } else {
            echo '<div class="notice notice-error" style="margin:16px 0;padding:12px;border-left:4px solid #dc3232;background:#fff7f7;">
                    <p style="margin:0;"><strong>Error:</strong> Could not create the article.</p>
                  </div>';
        }
    }

    // Fetch existing categories from 'news_category' taxonomy if available
    $terms = taxonomy_exists('news_category') ? get_terms(array(
        'taxonomy'   => 'news_category',
        'hide_empty' => false,
        'number'     => 200,
    )) : array();

    ?>
    <div class="wrap newsroom-frontend-wrap">
        <h1 style="margin:0 0 16px;">Create Content (Frontend)</h1>
        <p style="margin:0 0 12px;">Publish a <code>news_article</code> quickly. Your admin screen remains unchanged.</p>

        <form method="post" style="display:grid;gap:14px;max-width:780px;">
            <?php wp_nonce_field('newsroom_frontend_create', 'newsroom_frontend_nonce'); ?>

            <label>
                <span style="display:block;font-weight:600;margin-bottom:6px;">Headline</span>
                <input type="text" name="headline" required
                       style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;">
            </label>

            <label>
                <span style="display:block;font-weight:600;margin-bottom:6px;">Article Body</span>
                <textarea name="body" rows="10" required
                          style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;"></textarea>
            </label>

            <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                <label>
                    <span style="display:block;font-weight:600;margin-bottom:6px;">Category (news_category)</span>
                    <select name="category" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;">
                        <option value="">— Optional —</option>
                        <?php foreach ($terms as $t): ?>
                            <option value="<?php echo esc_attr($t->slug); ?>"><?php echo esc_html($t->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>

            <button type="submit"
                    style="cursor:pointer;display:inline-block;padding:10px 16px;border-radius:8px;border:1px solid #2271b1;background:#2271b1;color:#fff;">
                Publish Article
            </button>
        </form>

        <div style="margin-top:20px;">
            <a href="<?php echo esc_url( home_url('/admin-panel/') ); ?>" style="text-decoration:none;">← Back to Admin Panel</a>
        </div>
    </div>
    <?php
}

// 6) Route handler (wrap with theme header/footer, block unauthorized users)
function newsroom_frontend_template_redirect() {
    $page = get_query_var('newsroom_page');
    if (!$page) return;

    // Force login if needed
    if (!is_user_logged_in()) {
        // Sends to wp-login.php?redirect_to=...
        auth_redirect();
    }

    if (!newsroom_frontend_can_access()) {
        wp_die(__('You do not have permission to view this page.'));
    }

    // Render inside theme layout
    get_header();
    echo '<main class="newsroom-frontend-main" style="margin:24px auto;max-width:1100px;padding:0 16px;">';

    if ($page === 'admin_panel') {
        newsroom_frontend_render_admin_panel();
    } elseif ($page === 'create_content') {
        newsroom_frontend_render_create_content();
    } else {
        echo '<div class="wrap"><h1>Not Found</h1><p>Unknown newsroom route.</p></div>';
    }

    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'newsroom_frontend_template_redirect');

// 7) Flush rewrites when theme is (re)activated
function newsroom_frontend_flush_rewrites_on_switch() {
    newsroom_frontend_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'newsroom_frontend_flush_rewrites_on_switch');


/**
 * ✅ Frontend routes without creating Pages
 */
function newsroom_rewrite_rules() {
    add_rewrite_rule('^APCex/admin-panel/?$', 'index.php?newsroom_page=admin_panel', 'top');
    add_rewrite_rule('^APCex/create-content/?$', 'index.php?newsroom_page=create_content', 'top');
}
add_action('init', 'newsroom_rewrite_rules');

function newsroom_query_vars($vars) {
    $vars[] = 'newsroom_page';
    return $vars;
}
add_filter('query_vars', 'newsroom_query_vars');

function newsroom_template_redirect() {
    $page = get_query_var('newsroom_page');
    if ($page === 'admin_panel') {
        get_header();
        echo '<main class="newsroom-frontend">';
        newsroom_admin_panel_page();
        echo '</main>';
        get_footer();
        exit;
    }
    if ($page === 'create_content') {
        get_header();
        echo '<main class="newsroom-frontend">';
        newsroom_create_content_page();
        echo '</main>';
        get_footer();
        exit;
    }
}
add_action('template_redirect', 'newsroom_template_redirect');

/**
 * 🔄 Flush permalinks on theme activation
 */
function newsroom_flush_rewrites() {
    newsroom_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'newsroom_flush_rewrites');

/**
 * ✅ Frontend routes (force catch-all)
 */
function newsroom_force_routes() {
    add_rewrite_tag('%newsroom_page%', '([^&]+)'); // register custom query var

    add_rewrite_rule('^APCex/admin-panel/?$', 'index.php?newsroom_page=admin_panel', 'top');
    add_rewrite_rule('^APCex/create-content/?$', 'index.php?newsroom_page=create_content', 'top');
}
add_action('init', 'newsroom_force_routes');

function newsroom_template_loader() {
    $page = get_query_var('newsroom_page');
    if (!$page) {
        return; // nothing to do
    }

    get_header();
    echo '<main class="newsroom-frontend">';

    if ($page === 'admin_panel') {
        newsroom_admin_panel_page();
    } elseif ($page === 'create_content') {
        newsroom_create_content_page();
    }

    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'newsroom_template_loader');

// Add custom Admin Panel and Create Content pages inside wp-admin
add_action('admin_menu', 'newsroom_register_admin_pages');

function newsroom_register_admin_pages() {
    // Main Admin Panel menu
    add_menu_page(
        'Newsroom Admin Panel',      // Page title
        'Newsroom Panel',            // Menu title
        'manage_options',            // Capability
        'newsroom-admin-panel',      // Slug
        'newsroom_admin_panel_page', // Callback function
        'dashicons-admin-generic',   // Icon
        2                            // Position
    );

    // Submenu for Create Content
    add_submenu_page(
        'newsroom-admin-panel',       // Parent slug
        'Create Content',             // Page title
        'Create Content',             // Menu title
        'edit_posts',                 // Capability
        'newsroom-create-content',    // Slug
        'newsroom_create_content_page'// Callback function
    );
}

// Admin Panel content
function newsroom_admin_panel_page() {
    echo '<div class="wrap"><h1>Welcome to the Newsroom Admin Panel</h1></div>';
}

// Create Content page content
function newsroom_create_content_page() {
    echo '<div class="wrap"><h1>Create New Content</h1>';
    echo '<p>This is where you will create and manage your content.</p></div>';
}

// Force auto-generate likes, comments, retweets on publish/update
function newsroom_force_social_random_counts( $post_id, $post, $update ) {
    // Only apply to Social Posts
    if ( $post->post_type !== 'social_post' ) {
        return;
    }

    // Generate new random values every time you publish/update
    update_post_meta( $post_id, 'likes', absint( wp_rand(30, 1200) ) );
    update_post_meta( $post_id, 'comments', absint( wp_rand(0, 50) ) );
    update_post_meta( $post_id, 'retweets', absint( wp_rand(0, 200) ) );
}
add_action( 'save_post', 'newsroom_force_social_random_counts', 20, 3 );


// Generate random like, comment, and tweet counts on publish
function newsroom_generate_random_counts($post_id, $post, $update) {
    // Run only when publishing a new post (not on autosave or update)
    if ($post->post_status !== 'publish' || $update) {
        return;
    }

    // Generate random numbers
    $like_count    = rand(50, 500);   // Random likes
    $comment_count = rand(5, 100);    // Random comments
    $tweet_count   = rand(10, 200);   // Random tweets

    // Save them as post meta
    update_post_meta($post_id, '_random_like_count', $like_count);
    update_post_meta($post_id, '_random_comment_count', $comment_count);
    update_post_meta($post_id, '_random_tweet_count', $tweet_count);
}
add_action('save_post', 'newsroom_generate_random_counts', 10, 3);



function newsroom_display_random_counts($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $like_count    = get_post_meta($post_id, '_random_like_count', true);
    $comment_count = get_post_meta($post_id, '_random_comment_count', true);
    $tweet_count   = get_post_meta($post_id, '_random_tweet_count', true);

    if ($like_count || $comment_count || $tweet_count) {
        echo '<div class="post-random-counts">';
        echo '<span class="likes">👍 ' . intval($like_count) . ' Likes</span> ';
        echo '<span class="comments">💬 ' . intval($comment_count) . ' Comments</span> ';
        echo '<span class="tweets">🐦 ' . intval($tweet_count) . ' Tweets</span>';
        echo '</div>';
    }
}

function theme_enqueue_fontawesome_local() {
    wp_enqueue_style(
        'font-awesome-local',
        get_template_directory_uri() . '/assets/fontawesome/css/all.min.css',
        array(),
        '6.7.2'
    );
}
add_action('wp_enqueue_scripts', 'theme_enqueue_fontawesome_local');


add_action('wp_head', 'sb_headline_fallback', 1);
function sb_headline_fallback(){
    if (is_admin()) return;
    ?>
    <script>
    (function(){
        if (document.getElementById('sb-headline')) return;

        var el = document.createElement('div');
        el.id = 'sb-headline';
        el.textContent = 'SIGNAL BRIDGE NEWSROOM SIM';
        el.style.textAlign = 'center';
        el.style.fontWeight = '700';
        el.style.fontSize = '28px';
        el.style.margin = '20px 0 0';

        function insertAfterHeader() {
            if (document.getElementById('sb-headline')) return true;
            var selectors = [
                'header',
                '#masthead',
                '.site-header',
                '.header',
                '[role="banner"]',
                '.site-top',
                '.main-header',
                '.navbar'
            ];
            var header = null;
            for (var i = 0; i < selectors.length; i++) {
                header = document.querySelector(selectors[i]);
                if (header) break;
            }
            if (header && header.parentNode) {
                header.parentNode.insertBefore(el, header.nextSibling);
                return true;
            }
            return false;
        }

        document.addEventListener('DOMContentLoaded', function(){
            if (insertAfterHeader()) return;

            // Observe DOM for header appearing (for themes/page-builders that render later)
            var observer;
            var timeout = setTimeout(function(){
                if (observer) try{ observer.disconnect(); }catch(e){}
                // fallback: try to insert before main content or at top of body
                if (!document.getElementById('sb-headline')) {
                    var mainSelectors = ['main', '#content', '.site-content', '.content', '.wrap'];
                    var placed = false;
                    for (var j = 0; j < mainSelectors.length; j++) {
                        var mainEl = document.querySelector(mainSelectors[j]);
                        if (mainEl && mainEl.parentNode) {
                            mainEl.parentNode.insertBefore(el, mainEl);
                            placed = true; break;
                        }
                    }
                    if (!placed && document.body) document.body.insertBefore(el, document.body.firstChild);
                }
            }, 3000); // wait up to 3s for header to appear

            observer = new MutationObserver(function(mutations, obs){
                if (insertAfterHeader()) {
                    obs.disconnect();
                    clearTimeout(timeout);
                }
            });
            try {
                observer.observe(document.documentElement || document.body, { childList: true, subtree: true });
            } catch(e) {
                clearTimeout(timeout);
                // if observer fails, do immediate fallback
                if (!document.getElementById('sb-headline')) {
                    if (!insertAfterHeader()) {
                        if (document.body) document.body.insertBefore(el, document.body.firstChild);
                    }
                }
            }
        });
    })();
    </script>
    <?php
}


// AJAX handler for single post deletion
add_action('wp_ajax_delete_single_post', function() {
    // Check nonce
    check_ajax_referer('delete_post_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
    }

    // Check permissions
    if (!current_user_can('delete_post', $post_id)) {
        wp_send_json_error('Permission denied');
    }

    // Delete the post
    $result = wp_delete_post($post_id, true);

    if ($result) {
        // Store the deletion event for live updates
        set_transient('newsroom_post_deleted_' . $post_id, array(
            'post_id' => $post_id,
            'deleted_by' => get_current_user_id(),
            'timestamp' => current_time('Y-m-d H:i:s')
        ), 300); // Keep for 5 minutes

        wp_send_json_success('Post deleted successfully');
    } else {
        wp_send_json_error('Failed to delete post');
    }
});

// AJAX handler for deleting all posts
add_action('wp_ajax_delete_all_posts', function() {
    // Check nonce
    check_ajax_referer('delete_all_posts_nonce', 'nonce');

    // Check permissions - only administrators can delete all posts
    if (!current_user_can('delete_posts')) {
        wp_send_json_error('Permission denied');
    }

    // Get all news articles and social posts
    $news_posts = get_posts(array(
        'post_type' => 'news_article',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));

    $social_posts = get_posts(array(
        'post_type' => 'social_post',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));

    $all_post_ids = array_merge($news_posts, $social_posts);
    $deleted_count = 0;
    $failed_count = 0;

    foreach ($all_post_ids as $post_id) {
        $result = wp_delete_post($post_id, true); // true = force delete (skip trash)
        if ($result) {
            $deleted_count++;
        } else {
            $failed_count++;
        }
    }

    if ($deleted_count > 0) {
        wp_send_json_success(array(
            'message' => "Successfully deleted {$deleted_count} post(s)",
            'deleted' => $deleted_count,
            'failed' => $failed_count
        ));
    } else {
        wp_send_json_error('No posts were deleted');
    }
});

// AJAX handler for deleting a comment
add_action('wp_ajax_delete_comment', function() {
    $comment_id = intval($_POST['comment_id']);

    if (!$comment_id) {
        wp_send_json_error('Invalid comment ID');
    }

    // Check nonce
    check_ajax_referer('delete_comment_' . $comment_id, 'nonce');

    $comment = get_comment($comment_id);

    if (!$comment) {
        wp_send_json_error('Comment not found');
    }

    // Check permissions - user can delete their own comment or moderators can delete any
    $can_delete = (get_current_user_id() == $comment->user_id) || current_user_can('moderate_comments');

    if (!$can_delete) {
        wp_send_json_error('Permission denied');
    }

    // Delete the comment
    $result = wp_delete_comment($comment_id, true);

    if ($result) {
        // Store the deletion event for live updates
        set_transient('newsroom_comment_deleted_' . $comment_id, array(
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'deleted_by' => get_current_user_id(),
            'timestamp' => current_time('Y-m-d H:i:s')
        ), 300); // Keep for 5 minutes

        wp_send_json_success('Comment deleted successfully');
    } else {
        wp_send_json_error('Failed to delete comment');
    }
});

// AJAX handler for adding comments inline (logged in users)
add_action('wp_ajax_add_comment_inline', function() {
    // Verify nonce - try both nonces for compatibility
    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'add_comment_inline_nonce') ||
                   wp_verify_nonce($_POST['nonce'], 'newsroom_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to comment');
        return;
    }

    // Check permissions
    if (!current_user_can('add_reply') && !current_user_can('edit_posts')) {
        wp_send_json_error('You do not have permission to add comments');
        return;
    }

    $post_id = intval($_POST['post_id']);
    $comment_content = sanitize_textarea_field($_POST['comment_content']);

    if (!$post_id || empty($comment_content)) {
        wp_send_json_error('Invalid post ID or empty comment');
        return;
    }

    // Add the comment
    $comment_data = array(
        'comment_post_ID' => $post_id,
        'comment_content' => $comment_content,
        'comment_author' => wp_get_current_user()->display_name,
        'comment_author_email' => wp_get_current_user()->user_email,
        'user_id' => get_current_user_id(),
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        $comment = get_comment($comment_id);
        $comment_author = get_userdata($comment->user_id);

        // Check if user can delete this comment
        $can_delete = (get_current_user_id() == $comment->user_id && current_user_can('delete_own_reply'))
                   || current_user_can('moderate_comments')
                   || current_user_can('delete_others_posts');

        // Return the new comment HTML
        ob_start();
        ?>
        <div class="comment-item border-bottom pb-3 mb-3 bg-white p-3 rounded" id="comment-<?php echo $comment->comment_ID; ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <strong><?php echo esc_html($comment_author ? $comment_author->display_name : $comment->comment_author); ?></strong>
                    <small class="text-muted ms-2">just now</small>
                    <p class="mb-0 mt-1"><?php echo esc_html($comment->comment_content); ?></p>
                </div>
                <?php if ($can_delete): ?>
                    <button type="button"
                            class="btn btn-sm btn-danger delete-comment-btn"
                            data-comment-id="<?php echo $comment->comment_ID; ?>"
                            data-nonce="<?php echo wp_create_nonce('delete_comment_' . $comment->comment_ID); ?>">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $comment_html = ob_get_clean();

        wp_send_json_success(array(
            'comment_id' => $comment_id,
            'author_name' => $comment_author ? $comment_author->display_name : $comment->comment_author,
            'content' => $comment->comment_content,
            'date' => 'just now',
            'can_delete' => $can_delete,
            'delete_nonce' => $can_delete ? wp_create_nonce('delete_comment_' . $comment_id) : '',
            'comment_html' => $comment_html,
            'message' => 'Comment added successfully'
        ));
    } else {
        wp_send_json_error('Failed to add comment');
    }
});

// AJAX handler for getting new posts (logged in users)
add_action('wp_ajax_get_new_posts', function() {
    // Verify nonce
    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'newsroom_nonce') ||
                   wp_verify_nonce($_POST['nonce'], 'add_comment_inline_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    // Handle special timestamp requests from JavaScript
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    }

    // Get current user ID to exclude their own posts
    $current_user_id = get_current_user_id();

    // Query for new news articles (exclude current user's posts)
    $news_args = array(
        'post_type' => 'news_article',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'date_query' => array(
            array(
                'after' => $since_date,
                'inclusive' => false,
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // Only exclude current user's posts if user is logged in
    if ($current_user_id > 0) {
        $news_args['author__not_in'] = array($current_user_id);
    }

    // Query for new social posts (exclude current user's posts)
    $social_args = array(
        'post_type' => 'social_post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'date_query' => array(
            array(
                'after' => $since_date,
                'inclusive' => false,
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // Only exclude current user's posts if user is logged in
    if ($current_user_id > 0) {
        $social_args['author__not_in'] = array($current_user_id);
    }

    $news_posts = get_posts($news_args);
    $social_posts = get_posts($social_args);
    $all_posts = array_merge($news_posts, $social_posts);

    if (empty($all_posts)) {
        wp_send_json_success(array(
            'posts_count' => 0,
            'posts_html' => '',
            'latest_timestamp' => $since_timestamp
        ));
        return;
    }

    // Sort all posts by date
    usort($all_posts, function($a, $b) {
        return strtotime($b->post_date) - strtotime($a->post_date);
    });

    // Generate HTML for new posts
    ob_start();
    global $post;
    foreach ($all_posts as $current_post) {
        $post = $current_post; // Set global $post
        setup_postdata($post);



        if ($post->post_type === 'news_article') {
            get_template_part('template-parts/content', 'news');
        } else {
            get_template_part('template-parts/content', 'social');
        }
    }
    wp_reset_postdata();

    $posts_html = ob_get_clean();
    $latest_timestamp = date('Y-m-d H:i:s', strtotime($all_posts[0]->post_date));

    wp_send_json_success(array(
        'posts_count' => count($all_posts),
        'posts_html' => $posts_html,
        'latest_timestamp' => $latest_timestamp
    ));
});

// AJAX handler for getting new posts (non-logged in users)
add_action('wp_ajax_nopriv_get_new_posts', function() {
    // Verify nonce
    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'newsroom_nonce') ||
                   wp_verify_nonce($_POST['nonce'], 'add_comment_inline_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    // Handle special timestamp requests from JavaScript
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    }

    // Get current user ID to exclude their own posts (for non-logged-in users, this will be 0)
    $current_user_id = get_current_user_id();

    // Query for new news articles (exclude current user's posts if logged in)
    $news_args = array(
        'post_type' => 'news_article',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'date_query' => array(
            array(
                'after' => $since_date,
                'inclusive' => false,
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // Only exclude current user's posts if user is logged in
    if ($current_user_id > 0) {
        $news_args['author__not_in'] = array($current_user_id);
    }

    // Query for new social posts (exclude current user's posts if logged in)
    $social_args = array(
        'post_type' => 'social_post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'date_query' => array(
            array(
                'after' => $since_date,
                'inclusive' => false,
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );

    // Only exclude current user's posts if user is logged in
    if ($current_user_id > 0) {
        $social_args['author__not_in'] = array($current_user_id);
    }

    $news_posts = get_posts($news_args);
    $social_posts = get_posts($social_args);
    $all_posts = array_merge($news_posts, $social_posts);

    if (empty($all_posts)) {
        wp_send_json_success(array(
            'posts_count' => 0,
            'posts_html' => '',
            'latest_timestamp' => $since_timestamp
        ));
        return;
    }

    // Sort all posts by date
    usort($all_posts, function($a, $b) {
        return strtotime($b->post_date) - strtotime($a->post_date);
    });

    // Generate HTML for new posts
    ob_start();
    global $post;
    foreach ($all_posts as $current_post) {
        $post = $current_post; // Set global $post
        setup_postdata($post);



        if ($post->post_type === 'news_article') {
            get_template_part('template-parts/content', 'news');
        } else {
            get_template_part('template-parts/content', 'social');
        }
    }
    wp_reset_postdata();

    $posts_html = ob_get_clean();
    $latest_timestamp = date('Y-m-d H:i:s', strtotime($all_posts[0]->post_date));

    wp_send_json_success(array(
        'posts_count' => count($all_posts),
        'posts_html' => $posts_html,
        'latest_timestamp' => $latest_timestamp
    ));
});

// AJAX handler for getting new comments (logged in users)
add_action('wp_ajax_get_new_comments', function() {
    // Verify nonce
    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'newsroom_nonce') ||
                   wp_verify_nonce($_POST['nonce'], 'add_comment_inline_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    // Handle special timestamp requests from JavaScript
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    }

    // Get current user ID to exclude their own comments
    $current_user_id = get_current_user_id();

    // Query for new comments (exclude current user's comments)
    $comment_args = array(
        'date_query' => array(
            array(
                'after' => $since_date,
                'inclusive' => false,
            ),
        ),
        'status' => 'approve',
        'orderby' => 'comment_date',
        'order' => 'DESC'
    );

    // Only exclude current user's comments if user is logged in
    if ($current_user_id > 0) {
        $comment_args['user__not_in'] = array($current_user_id);
    }

    $comments = get_comments($comment_args);

    if (empty($comments)) {
        wp_send_json_success(array(
            'comments_count' => 0,
            'comments_by_post' => array(),
            'latest_timestamp' => $since_timestamp
        ));
        return;
    }

    // Group comments by post
    $comments_by_post = array();
    $latest_timestamp = $since_timestamp;

    foreach ($comments as $comment) {
        $post_id = $comment->comment_post_ID;
        $comment_author = get_userdata($comment->user_id);

        // Check if user can delete this comment
        $can_delete = (get_current_user_id() == $comment->user_id && current_user_can('delete_own_reply'))
                   || current_user_can('moderate_comments')
                   || current_user_can('delete_others_posts');

        if (!isset($comments_by_post[$post_id])) {
            $comments_by_post[$post_id] = array();
        }

        $comments_by_post[$post_id][] = array(
            'comment_id' => $comment->comment_ID,
            'author_name' => $comment_author ? $comment_author->display_name : $comment->comment_author,
            'content' => $comment->comment_content,
            'date' => human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ago',
            'can_delete' => $can_delete,
            'delete_nonce' => $can_delete ? wp_create_nonce('delete_comment_' . $comment->comment_ID) : ''
        );

        // Update latest timestamp
        if (strtotime($comment->comment_date) > strtotime($latest_timestamp)) {
            $latest_timestamp = $comment->comment_date;
        }
    }

    wp_send_json_success(array(
        'comments_count' => count($comments),
        'comments_by_post' => $comments_by_post,
        'latest_timestamp' => $latest_timestamp
    ));
});

// AJAX handler for getting new comments (non-logged in users)
add_action('wp_ajax_nopriv_get_new_comments', function() {
    // Verify nonce
    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'newsroom_nonce') ||
                   wp_verify_nonce($_POST['nonce'], 'add_comment_inline_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    // Handle special timestamp requests from JavaScript
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    }

    // Get current user ID to exclude their own comments (for non-logged-in users, this will be 0)
    $current_user_id = get_current_user_id();

    // Query for new comments (exclude current user's comments if logged in)
    $comment_args = array(
        'date_query' => array(
            array(
                'after' => $since_date,
                'inclusive' => false,
            ),
        ),
        'status' => 'approve',
        'orderby' => 'comment_date',
        'order' => 'DESC'
    );

    // Only exclude current user's comments if user is logged in
    if ($current_user_id > 0) {
        $comment_args['user__not_in'] = array($current_user_id);
    }

    $comments = get_comments($comment_args);

    if (empty($comments)) {
        wp_send_json_success(array(
            'comments_count' => 0,
            'comments_by_post' => array(),
            'latest_timestamp' => $since_timestamp
        ));
        return;
    }

    // Group comments by post
    $comments_by_post = array();
    $latest_timestamp = $since_timestamp;

    foreach ($comments as $comment) {
        $post_id = $comment->comment_post_ID;
        $comment_author = get_userdata($comment->user_id);

        if (!isset($comments_by_post[$post_id])) {
            $comments_by_post[$post_id] = array();
        }

        $comments_by_post[$post_id][] = array(
            'comment_id' => $comment->comment_ID,
            'author_name' => $comment_author ? $comment_author->display_name : $comment->comment_author,
            'content' => $comment->comment_content,
            'date' => human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ago',
            'can_delete' => false, // Non-logged in users can't delete
            'delete_nonce' => ''
        );

        // Update latest timestamp
        if (strtotime($comment->comment_date) > strtotime($latest_timestamp)) {
            $latest_timestamp = $comment->comment_date;
        }
    }

    wp_send_json_success(array(
        'comments_count' => count($comments),
        'comments_by_post' => $comments_by_post,
        'latest_timestamp' => $latest_timestamp
    ));
});

// AJAX handler for getting edit operations (logged in users)
add_action('wp_ajax_get_edit_operations', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'newsroom_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    $current_user_id = get_current_user_id();
    $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    $operations = array();

    // Get edit operations from transients
    global $wpdb;
    $transient_keys = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_newsroom_post_edited_%'"
    );

    foreach ($transient_keys as $key) {
        $transient_name = str_replace('_transient_', '', $key->option_name);
        $operation_data = get_transient($transient_name);

        if ($operation_data && isset($operation_data['timestamp'])) {
            // Debug: Log the operation data
            error_log("Edit operation data: " . print_r($operation_data, true));

            // Only include operations after the since timestamp and not by current user
            if (strtotime($operation_data['timestamp']) > strtotime($since_date) &&
                isset($operation_data['edited_by']) && $operation_data['edited_by'] != $current_user_id) {

                $operations[] = array(
                    'type' => 'post_edited',
                    'post_id' => $operation_data['post_id'],
                    'post_type' => $operation_data['post_type'] ?? 'unknown',
                    'timestamp' => $operation_data['timestamp']
                );
            }
        }
    }

    // Sort by timestamp
    usort($operations, function($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    wp_send_json_success(array(
        'operations' => $operations,
        'server_time' => current_time('Y-m-d H:i:s')
    ));
});

// AJAX handler for getting edit operations (non-logged in users)
add_action('wp_ajax_nopriv_get_edit_operations', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'newsroom_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    $operations = array();

    // Get edit operations from transients
    global $wpdb;
    $transient_keys = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_newsroom_post_edited_%'"
    );

    foreach ($transient_keys as $key) {
        $transient_name = str_replace('_transient_', '', $key->option_name);
        $operation_data = get_transient($transient_name);

        if ($operation_data && isset($operation_data['timestamp'])) {
            // Debug: Log the operation data
            error_log("Edit operation data (nopriv): " . print_r($operation_data, true));

            // Include all operations after the since timestamp (no user filtering for non-logged-in)
            if (strtotime($operation_data['timestamp']) > strtotime($since_date)) {

                $operations[] = array(
                    'type' => 'post_edited',
                    'post_id' => $operation_data['post_id'],
                    'post_type' => $operation_data['post_type'] ?? 'unknown',
                    'timestamp' => $operation_data['timestamp']
                );
            }
        }
    }

    // Sort by timestamp
    usort($operations, function($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    wp_send_json_success(array(
        'operations' => $operations,
        'server_time' => current_time('Y-m-d H:i:s')
    ));
});

// AJAX handler for getting delete operations (logged in users)
add_action('wp_ajax_get_delete_operations', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'newsroom_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    // Handle special timestamp requests from JavaScript
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    }

    $current_user_id = get_current_user_id();
    $operations = array();

    // Check for deleted posts and comments only
    global $wpdb;
    $transient_keys = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_newsroom_post_deleted_%'
         OR option_name LIKE '_transient_newsroom_comment_deleted_%'"
    );

    foreach ($transient_keys as $key) {
        $transient_name = str_replace('_transient_', '', $key->option_name);
        $operation_data = get_transient($transient_name);

        if ($operation_data && isset($operation_data['timestamp'])) {
            // Only include operations after the since timestamp
            if (strtotime($operation_data['timestamp']) > strtotime($since_date)) {

                // Check if operation was performed by current user (skip if so)
                $skip_operation = false;
                if (isset($operation_data['deleted_by']) && $operation_data['deleted_by'] == $current_user_id) {
                    $skip_operation = true;
                }

                if (!$skip_operation) {
                    if (strpos($transient_name, 'newsroom_post_deleted_') === 0) {
                        $operations[] = array(
                            'type' => 'post_deleted',
                            'post_id' => $operation_data['post_id'],
                            'timestamp' => $operation_data['timestamp']
                        );
                    } elseif (strpos($transient_name, 'newsroom_comment_deleted_') === 0) {
                        $operations[] = array(
                            'type' => 'comment_deleted',
                            'comment_id' => $operation_data['comment_id'],
                            'post_id' => $operation_data['post_id'],
                            'timestamp' => $operation_data['timestamp']
                        );
                    }
                }
            }
        }
    }

    // Sort by timestamp
    usort($operations, function($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    $latest_timestamp = !empty($operations) ? end($operations)['timestamp'] : $since_timestamp;

    wp_send_json_success(array(
        'operations_count' => count($operations),
        'operations' => $operations,
        'latest_timestamp' => $latest_timestamp
    ));
});

// AJAX handler for getting delete/edit operations (non-logged in users)
add_action('wp_ajax_nopriv_get_delete_operations', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'newsroom_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $since_timestamp = sanitize_text_field($_POST['since_timestamp']);

    if (empty($since_timestamp)) {
        wp_send_json_error('Invalid timestamp');
        return;
    }

    // Handle special timestamp requests from JavaScript
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
    }

    $operations = array();

    // Check for deleted posts and comments only
    global $wpdb;
    $transient_keys = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_newsroom_post_deleted_%'
         OR option_name LIKE '_transient_newsroom_comment_deleted_%'"
    );

    foreach ($transient_keys as $key) {
        $transient_name = str_replace('_transient_', '', $key->option_name);
        $operation_data = get_transient($transient_name);

        if ($operation_data && isset($operation_data['timestamp'])) {
            // Include all operations after the since timestamp (no user filtering for non-logged-in)
            if (strtotime($operation_data['timestamp']) > strtotime($since_date)) {

                if (strpos($transient_name, 'newsroom_post_deleted_') === 0) {
                    $operations[] = array(
                        'type' => 'post_deleted',
                        'post_id' => $operation_data['post_id'],
                        'timestamp' => $operation_data['timestamp']
                    );
                } elseif (strpos($transient_name, 'newsroom_comment_deleted_') === 0) {
                    $operations[] = array(
                        'type' => 'comment_deleted',
                        'comment_id' => $operation_data['comment_id'],
                        'post_id' => $operation_data['post_id'],
                        'timestamp' => $operation_data['timestamp']
                    );
                }
            }
        }
    }

    // Sort by timestamp
    usort($operations, function($a, $b) {
        return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    });

    $latest_timestamp = !empty($operations) ? end($operations)['timestamp'] : $since_timestamp;

    wp_send_json_success(array(
        'operations_count' => count($operations),
        'operations' => $operations,
        'latest_timestamp' => $latest_timestamp
    ));
});

// AJAX handler for getting updated post content (logged in users)
add_action('wp_ajax_get_updated_post', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'newsroom_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }

    // Get the post
    $post = get_post($post_id);

    if (!$post) {
        wp_send_json_error('Post not found');
        return;
    }

    // Debug: Log post type
    $expected_type = sanitize_text_field($_POST['expected_post_type'] ?? 'not provided');
    error_log("get_updated_post: Post ID {$post_id}, Type: {$post->post_type}, Status: {$post->post_status}, Expected: {$expected_type}");

    // Set up post data for template rendering
    global $post;
    $original_post = $post; // Save original post
    $post = get_post($post_id); // Set the post we want to render
    setup_postdata($post);

    // Render the appropriate template based on post type
    ob_start();

    if ($post->post_type === 'social_post') {
        get_template_part('template-parts/content', 'social');
    } elseif ($post->post_type === 'news_article') {
        get_template_part('template-parts/content', 'news');
    } else {
        error_log("get_updated_post: Unsupported post type '{$post->post_type}' for post ID {$post_id}");
        wp_send_json_error("Unsupported post type: {$post->post_type}");
        wp_reset_postdata();
        return;
    }

    $post_html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success(array(
        'post_html' => $post_html,
        'post_id' => $post_id,
        'post_type' => $post->post_type
    ));
});

// AJAX handler for getting updated post content (non-logged in users)
add_action('wp_ajax_nopriv_get_updated_post', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'newsroom_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }

    // Get the post
    $post = get_post($post_id);

    if (!$post) {
        wp_send_json_error('Post not found');
        return;
    }

    // Debug: Log post type
    $expected_type = sanitize_text_field($_POST['expected_post_type'] ?? 'not provided');
    error_log("get_updated_post (nopriv): Post ID {$post_id}, Type: {$post->post_type}, Status: {$post->post_status}, Expected: {$expected_type}");

    // Set up post data for template rendering
    global $post;
    $original_post = $post; // Save original post
    $post = get_post($post_id); // Set the post we want to render
    setup_postdata($post);

    // Render the appropriate template based on post type
    ob_start();

    if ($post->post_type === 'social_post') {
        get_template_part('template-parts/content', 'social');
    } elseif ($post->post_type === 'news_article') {
        get_template_part('template-parts/content', 'news');
    } else {
        error_log("get_updated_post (nopriv): Unsupported post type '{$post->post_type}' for post ID {$post_id}");
        wp_send_json_error("Unsupported post type: {$post->post_type}");
        wp_reset_postdata();
        return;
    }

    $post_html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success(array(
        'post_html' => $post_html,
        'post_id' => $post_id,
        'post_type' => $post->post_type
    ));
});

// AJAX handler for creating test posts (for debugging)
add_action('wp_ajax_create_test_post', function() {
    // Verify nonce
    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'newsroom_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Create a test social post with proper meta data
    $post_data = array(
        'post_title' => 'Test User - Twitter Post',
        'post_content' => 'This is a test post created for real-time update testing at ' . current_time('Y-m-d H:i:s'),
        'post_status' => 'publish',
        'post_type' => 'social_post',
        'post_author' => get_current_user_id(),
        'meta_input' => array(
            '_social_platform' => 'twitter',
            '_social_display_name' => 'Test User',
            '_social_handle' => 'testuser',
            '_social_avatar' => get_template_directory_uri() . '/assets/images/default-avatar.svg'
        )
    );

    $post_id = wp_insert_post($post_data);

    if ($post_id && !is_wp_error($post_id)) {
        wp_send_json_success(array(
            'post_id' => $post_id,
            'message' => 'Test post created successfully'
        ));
    } else {
        wp_send_json_error('Failed to create test post');
    }
});

// AJAX handler for getting comments for a post (logged in users)
add_action('wp_ajax_get_post_comments', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'get_comments_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }

    $post_comments = get_comments(array(
        'post_id' => $post_id,
        'status' => 'approve',
        'order' => 'ASC'
    ));

    $comments_html = '';
    $comments_count = count($post_comments);

    if ($post_comments) {
        ob_start();
        foreach ($post_comments as $comment):
            $comment_author = get_userdata($comment->user_id);
            // Check if user can delete this comment
            $can_delete = (get_current_user_id() == $comment->user_id && current_user_can('delete_own_reply'))
                       || current_user_can('moderate_comments')
                       || current_user_can('delete_others_posts');
        ?>
            <div class="comment-item border-bottom pb-3 mb-3 bg-white p-3 rounded" id="comment-<?php echo $comment->comment_ID; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong><?php echo esc_html($comment_author ? $comment_author->display_name : $comment->comment_author); ?></strong>
                        <small class="text-muted ms-2"><?php echo human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ago'; ?></small>
                        <p class="mb-0 mt-1"><?php echo esc_html($comment->comment_content); ?></p>
                    </div>
                    <?php if ($can_delete): ?>
                        <button type="button"
                                class="btn btn-sm btn-danger delete-comment-btn"
                                data-comment-id="<?php echo $comment->comment_ID; ?>"
                                data-nonce="<?php echo wp_create_nonce('delete_comment_' . $comment->comment_ID); ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php
        endforeach;
        $comments_html = ob_get_clean();
    }

    wp_send_json_success(array(
        'comments_count' => $comments_count,
        'comments_html' => $comments_html
    ));
});

// AJAX handler for getting comments for a post (non-logged in users)
add_action('wp_ajax_nopriv_get_post_comments', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'get_comments_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }

    $post_comments = get_comments(array(
        'post_id' => $post_id,
        'status' => 'approve',
        'order' => 'ASC'
    ));

    $comments_html = '';
    $comments_count = count($post_comments);

    if ($post_comments) {
        ob_start();
        foreach ($post_comments as $comment):
            $comment_author = get_userdata($comment->user_id);
        ?>
            <div class="comment-item border-bottom pb-3 mb-3 bg-white p-3 rounded" id="comment-<?php echo $comment->comment_ID; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong><?php echo esc_html($comment_author ? $comment_author->display_name : $comment->comment_author); ?></strong>
                        <small class="text-muted ms-2"><?php echo human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ago'; ?></small>
                        <p class="mb-0 mt-1"><?php echo esc_html($comment->comment_content); ?></p>
                    </div>
                </div>
            </div>
        <?php
        endforeach;
        $comments_html = ob_get_clean();
    }

    wp_send_json_success(array(
        'comments_count' => $comments_count,
        'comments_html' => $comments_html
    ));
});

// AJAX handler for adding comments to posts (simple form)
add_action('wp_ajax_add_comment_to_post', function() {
    $post_id = intval($_POST['post_id']);
    $author = sanitize_text_field($_POST['author']);
    $comment_content = sanitize_textarea_field($_POST['comment']);

    if (!$post_id || empty($comment_content) || empty($author)) {
        wp_send_json_error('Invalid data provided');
        return;
    }

    // Add the comment
    $comment_data = array(
        'comment_post_ID' => $post_id,
        'comment_content' => $comment_content,
        'comment_author' => $author,
        'comment_author_email' => '', // Optional for guest comments
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        wp_send_json_success(array(
            'comment_id' => $comment_id,
            'message' => 'Comment added successfully'
        ));
    } else {
        wp_send_json_error('Failed to add comment');
    }
});

// AJAX handler for adding comments to posts (simple form) - non-logged in users
add_action('wp_ajax_nopriv_add_comment_to_post', function() {
    $post_id = intval($_POST['post_id']);
    $author = sanitize_text_field($_POST['author']);
    $comment_content = sanitize_textarea_field($_POST['comment']);

    if (!$post_id || empty($comment_content) || empty($author)) {
        wp_send_json_error('Invalid data provided');
        return;
    }

    // Add the comment
    $comment_data = array(
        'comment_post_ID' => $post_id,
        'comment_content' => $comment_content,
        'comment_author' => $author,
        'comment_author_email' => '', // Optional for guest comments
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        wp_send_json_success(array(
            'comment_id' => $comment_id,
            'message' => 'Comment added successfully'
        ));
    } else {
        wp_send_json_error('Failed to add comment');
    }
});

// Handle comment submission (non-AJAX) - Keep for backward compatibility
add_action('template_redirect', function() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment_nonce'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['add_comment_nonce'], 'add_comment_action')) {
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            return;
        }

        $post_id = intval($_POST['post_id']);
        $comment_content = sanitize_textarea_field($_POST['comment_content']);

        if (!$post_id || empty($comment_content)) {
            return;
        }

        // Add the comment
        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_content' => $comment_content,
            'comment_author' => wp_get_current_user()->display_name,
            'comment_author_email' => wp_get_current_user()->user_email,
            'user_id' => get_current_user_id(),
            'comment_approved' => 1,
        );

        $comment_id = wp_insert_comment($comment_data);

        if ($comment_id) {
            // Check if redirect_to is provided, otherwise redirect to post
            $redirect_url = !empty($_POST['redirect_to'])
                ? esc_url_raw($_POST['redirect_to']) . '#comment-' . $comment_id
                : get_permalink($post_id) . '#comment-' . $comment_id;

            wp_safe_redirect($redirect_url);
            exit;
        }
    }
});

add_action('template_redirect', function() {
    if ( isset($_POST['bulk_delete']) && !empty($_POST['delete_ids']) ) {

        // Nonce check
        if ( !isset($_POST['bulk_delete_nonce']) ||
             !wp_verify_nonce($_POST['bulk_delete_nonce'], 'bulk_delete_action') ) {
            return;
        }

        foreach ($_POST['delete_ids'] as $post_id) {
            $post_id = intval($post_id);

            if ( current_user_can('delete_post', $post_id) ) {
                wp_delete_post($post_id, true);
            }
        }

        // Redirect to avoid resubmission
        wp_safe_redirect( esc_url_raw( remove_query_arg([], $_SERVER['REQUEST_URI']) ) );
        exit;
    }
});


add_action('template_redirect', function() {
    // HEAVY DEBUGGING - Log everything
    error_log('═══════════════════════════════════════════════════');
    error_log('🔍 DELETE HANDLER CALLED');
    error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
    error_log('POST data: ' . print_r($_POST, true));

    // Only run for POST requests that include delete_ids (and bulk_delete marker)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('❌ Not a POST request - exiting');
        return;
    }

    if (empty($_POST['delete_ids'])) {
        error_log('❌ delete_ids is empty - exiting');
        return;
    }

    if (!is_array($_POST['delete_ids'])) {
        error_log('❌ delete_ids is not an array - exiting');
        return;
    }

    error_log('✅ delete_ids found: ' . print_r($_POST['delete_ids'], true));

    // Optional: also check for marker (not required since delete_ids exists), but safe:
    if (!empty($_POST['bulk_delete']) || !empty($_POST['delete_ids'])) {
        error_log('✅ bulk_delete marker found or delete_ids exists');

        // Nonce check (if wp_verify_nonce exists)
        if (function_exists('wp_verify_nonce')) {
            error_log('🔐 Checking nonce...');
            error_log('Nonce value: ' . (isset($_POST['bulk_delete_nonce']) ? $_POST['bulk_delete_nonce'] : 'NOT SET'));

            if (!isset($_POST['bulk_delete_nonce']) || !wp_verify_nonce($_POST['bulk_delete_nonce'], 'bulk_delete_action')) {
                error_log('❌ BULK DELETE: nonce check FAILED!');
                return;
            }
            error_log('✅ Nonce check passed');
        }

        // Debug: log request (remove or comment out later)
        error_log('✅ BULK DELETE POST payload: ' . print_r($_POST, true));
        error_log('Current user ID: ' . get_current_user_id());

        error_log('🔄 Starting to process delete_ids...');
        foreach ($_POST['delete_ids'] as $raw_id) {
            error_log('Processing ID: ' . $raw_id);
            $id = intval($raw_id);

            if (!$id) {
                error_log("❌ BULK DELETE: invalid id '{$raw_id}'");
                continue;
            }

            error_log("✅ Valid ID: {$id}");

            // If WP function exists, use it
            if (function_exists('get_post')) {
                $post = get_post($id);
                error_log('Post object: ' . print_r($post, true));

                if (!$post) {
                    error_log("❌ BULK DELETE: post not found id={$id}");
                    continue;
                }

                // permission: admin can delete all; others can delete their own
                $current_user_id = get_current_user_id();
                $is_admin = current_user_can('administrator');
                $is_author = $current_user_id == absint($post->post_author);
                $can_delete = $is_admin || $is_author;

                error_log("👤 Current user ID: {$current_user_id}");
                error_log("👤 Post author ID: {$post->post_author}");
                error_log("🔑 Is admin: " . ($is_admin ? 'YES' : 'NO'));
                error_log("✍️ Is author: " . ($is_author ? 'YES' : 'NO'));
                error_log("✅ Can delete: " . ($can_delete ? 'YES' : 'NO'));

                if ($can_delete) {
                    error_log("🗑️ Attempting to delete post {$id}...");
                    $res = wp_delete_post($id, true); // true = force delete
                    error_log("🗑️ BULK DELETE: wp_delete_post({$id}) => " . var_export($res, true));

                    if ($res) {
                        error_log("✅✅✅ POST {$id} DELETED SUCCESSFULLY!");
                    } else {
                        error_log("❌❌❌ POST {$id} DELETE FAILED!");
                    }
                } else {
                    error_log("❌ BULK DELETE: permission denied for id={$id} user=" . get_current_user_id());
                }
            } else {
                // Standalone remove function (if exists)
                error_log("⚠️ get_post function not found, trying standalone delete");
                if (function_exists('newsroom_delete_content')) {
                    error_log("BULK DELETE standalone: deleting id={$id}");
                    newsroom_delete_content($id);
                } else {
                    error_log("❌ BULK DELETE standalone: no delete function for id={$id}");
                }
            }
        }

        error_log('🔄 Finished processing all delete_ids');
        error_log('🔀 Redirecting to avoid re-submission...');

        // Redirect to avoid re-submission
        if (function_exists('wp_safe_redirect')) {
            error_log('Using wp_safe_redirect');
            wp_safe_redirect( esc_url_raw( $_SERVER['REQUEST_URI'] ) );
        } else {
            error_log('Using header redirect');
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
        error_log('═══════════════════════════════════════════════════');
        exit;
    }
});


