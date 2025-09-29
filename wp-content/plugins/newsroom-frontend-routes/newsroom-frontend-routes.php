<?php
/*
Plugin Name: Newsroom Frontend Routes
Description: Adds frontend routes (/admin-panel/ and /create-content/) for the Newsroom training site without creating WP Pages.
Version: 1.0
Author: Mahfuz
*/

if (!defined('ABSPATH')) {
    exit;
}

/* ---------------------------
 * Rewrite rules & query var
 * --------------------------- */
function nfr_add_rewrite_rules() {
    add_rewrite_rule('^admin-panel/?$', 'index.php?newsroom_page=admin_panel', 'top');
    add_rewrite_rule('^create-content/?$', 'index.php?newsroom_page=create_content', 'top');
}
add_action('init', 'nfr_add_rewrite_rules');

function nfr_query_vars($vars) {
    $vars[] = 'newsroom_page';
    return $vars;
}
add_filter('query_vars', 'nfr_query_vars');

/* ---------------------------
 * Capability helper
 * --------------------------- */
function nfr_user_can_access() {
    // allow users with any of these capabilities: adjust if you prefer stricter access
    if ( current_user_can('manage_training_content') ) return true; // custom cap you gave operators
    if ( current_user_can('edit_posts') ) return true;
    if ( current_user_can('manage_options') ) return true; // admins
    return false;
}

/* ---------------------------
 * Frontend renderers (do NOT call admin-only functions)
 * --------------------------- */
function nfr_render_admin_panel_content() {
    // A lightweight frontend admin dashboard (safe)
    $news_count = 0;
    $social_count = 0;
    $user_count = 0;

    if ( function_exists('wp_count_posts') ) {
        $nc = wp_count_posts('news_article');
        if ($nc && isset($nc->publish)) $news_count = (int) $nc->publish;
        $sc = wp_count_posts('social_post');
        if ($sc && isset($sc->publish)) $social_count = (int) $sc->publish;
    }

    $users = count_users();
    if (is_array($users) && isset($users['total_users'])) $user_count = (int) $users['total_users'];

    ob_start();
    ?>
    <div class="wrap newsroom-frontend-wrap">
        <h1>Newsroom Admin Panel (Frontend)</h1>
        <p>This is a frontend view for operators — your wp-admin screens remain unchanged.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:12px;">
            <div style="border:1px solid #e2e2e2;border-radius:8px;padding:14px;">
                <h3 style="margin:0 0 8px;">Published News</h3>
                <div style="font-size:26px;"><?php echo esc_html($news_count); ?></div>
            </div>
            <div style="border:1px solid #e2e2e2;border-radius:8px;padding:14px;">
                <h3 style="margin:0 0 8px;">Published Social</h3>
                <div style="font-size:26px;"><?php echo esc_html($social_count); ?></div>
            </div>
            <div style="border:1px solid #e2e2e2;border-radius:8px;padding:14px;">
                <h3 style="margin:0 0 8px;">Users</h3>
                <div style="font-size:26px;"><?php echo esc_html($user_count); ?></div>
            </div>
        </div>

        <div style="margin-top:18px;">
            <a href="<?php echo esc_url( home_url('/create-content/') ); ?>">Create Content →</a>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}

function nfr_render_create_content_content() {
    // Process POST for quick article creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nfr_nonce']) && wp_verify_nonce($_POST['nfr_nonce'], 'nfr_create') && current_user_can('edit_posts')) {
        $headline = isset($_POST['headline']) ? sanitize_text_field(wp_unslash($_POST['headline'])) : '';
        $body     = isset($_POST['body']) ? wp_kses_post(wp_unslash($_POST['body'])) : '';

        $postarr = array(
            'post_title'   => $headline,
            'post_content' => $body,
            'post_status'  => 'publish',
            'post_type'    => 'news_article', // matches your CPT; adjust if different
        );

        $post_id = wp_insert_post($postarr);

        if (!is_wp_error($post_id) && $post_id) {
            echo '<div style="margin:12px 0;padding:10px;border-left:4px solid #46b450;background:#f1fff4;">
                    <strong>Success:</strong> Article created. <a href="'.esc_url(get_permalink($post_id)).'">View</a>
                  </div>';
        } else {
            echo '<div style="margin:12px 0;padding:10px;border-left:4px solid #dc3232;background:#fff6f6;">
                    <strong>Error:</strong> Could not create the article.
                  </div>';
        }
    }

    // Show form
    ob_start(); ?>
    <div class="wrap newsroom-frontend-wrap">
        <h1>Create Content (Frontend)</h1>
        <form method="post" style="max-width:900px;display:block;">
            <?php wp_nonce_field('nfr_create', 'nfr_nonce'); ?>

            <p>
                <label><strong>Headline</strong><br>
                <input type="text" name="headline" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;"></label>
            </p>

            <p>
                <label><strong>Article Body</strong><br>
                <textarea name="body" rows="10" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;"></textarea></label>
            </p>

            <p><button type="submit" class="button button-primary">Publish Article</button></p>
        </form>

        <p><a href="<?php echo esc_url( home_url('/admin-panel/') ); ?>">← Back to Admin Panel</a></p>
    </div>
    <?php
    echo ob_get_clean();
}

/* ---------------------------
 * Router: render page (wrap with header/footer)
 * --------------------------- */
function nfr_template_router() {
    $page = get_query_var('newsroom_page');

    // Fallback: also accept ?newsroom_page=... in GET if rewrite not yet flushed.
    if ( empty($page) && ! empty($_GET['newsroom_page']) ) {
        $page = sanitize_text_field(wp_unslash($_GET['newsroom_page']));
    }

    if ( empty($page) ) {
        return; // not our route
    }

    // require login
    if ( ! is_user_logged_in() ) {
        auth_redirect();
    }

    if ( ! nfr_user_can_access() ) {
        wp_die(__('You do not have permission to view this page.', 'nfr'));
    }

    // Render inside theme header/footer
    get_header();
    echo '<main style="margin:28px auto;max-width:1100px;padding:0 16px;">';

    if ( $page === 'admin_panel' ) {
        nfr_render_admin_panel_content();
    } elseif ( $page === 'create_content' ) {
        nfr_render_create_content_content();
    } else {
        echo '<div class="wrap"><h1>Not Found</h1><p>Unknown newsroom route.</p></div>';
    }

    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'nfr_template_router', 0);

/* ---------------------------
 * Activation / Deactivation
 * --------------------------- */
function nfr_activate() {
    nfr_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'nfr_activate');

function nfr_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'nfr_deactivate');
