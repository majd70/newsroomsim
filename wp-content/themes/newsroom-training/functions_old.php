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
    
    // Localize script for AJAX
    wp_localize_script('newsroom-main', 'newsroom_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('newsroom_nonce')
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
                    '_social_likes' => intval($_POST['likes']),
                    '_social_comments' => intval($_POST['comments']),
                    '_social_retweets' => intval($_POST['retweets'])
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
            <a href="#twitter-tab" class="nav-tab" id="twitter-tab-btn"><?php _e('X', 'newsroom-training'); ?></a>
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
                    <tr>
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
                    </tr>
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

