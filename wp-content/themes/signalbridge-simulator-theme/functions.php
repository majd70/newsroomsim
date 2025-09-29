
<?php
/**
 * SignalBridge Simulator Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enqueue styles/scripts
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'signalbridge-style', get_stylesheet_uri(), [], '1.0.0' );
    wp_enqueue_style( 'signalbridge-app', get_template_directory_uri() . '/assets/app.css', [], '1.0.0' );
    wp_enqueue_script( 'signalbridge-app', get_template_directory_uri() . '/assets/app.js', ['jquery'], '1.0.0', true );
});

/**
 * Theme supports & menus
 */
add_action('after_setup_theme', function() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus([
        'primary' => __( 'Primary Menu', 'signalbridge-simulator' ),
    ]);
});

/**
 * Private site redirect (theme-only)
 * Redirect all non-logged-in users to login page except for wp-login.php, lostpassword, and admin-ajax
 */
add_action('template_redirect', function() {
    if ( is_user_logged_in() ) { return; }
    if ( is_admin() ) { return; }
    $login_page = wp_login_url();
    $allowed = [
        'wp-login.php',
        'wp-register.php',
        'wp-signup.php',
    ];
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    foreach ($allowed as $a) {
        if ( strpos($request_uri, $a) !== false ) { return; }
    }
    if ( strpos($request_uri, 'action=lostpassword') !== false ) { return; }
    if ( defined('DOING_AJAX') && DOING_AJAX ) { return; }

    // Allow access to static files like CSS/JS/images
    $ext = pathinfo(parse_url($request_uri, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
    $static_exts = ['css','js','png','jpg','jpeg','gif','svg','webp','woff','woff2','ttf','eot','ico'];
    if ( in_array(strtolower($ext), $static_exts, true) ) { return; }

    wp_redirect( $login_page );
    exit;
});

/**
 * Branded login styles (simple)
 */
add_action('login_enqueue_scripts', function() {
    echo '<style>
        .login h1 a { background-image: none !important; text-indent: 0; font-size: 24px; font-weight:700; }
        .login h1 a:after { content: "SignalBridge Training"; }
    </style>';
});

/**
 * Footer link back to SignalBridge.com
 */
add_action('wp_footer', function() {
    echo '<div class="sb-footer-link"><a href="https://signalbridge.com" target="_blank" rel="noopener">SignalBridge.com</a></div>';
});

/**
 * Register Custom Post Types and Taxonomy
 */
add_action('init', function() {
    // Scenario taxonomy (for both CPTs)
    register_taxonomy('scenario', ['news_article','social_post'], [
        'label' => __('Scenarios', 'signalbridge-simulator'),
        'public' => true,
        'rewrite' => ['slug' => 'scenario'],
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);

    // News Article CPT
    register_post_type('news_article', [
        'label' => __('News Articles', 'signalbridge-simulator'),
        'public' => true,
        'show_ui' => true,
        'menu_icon' => 'dashicons-media-document',
        'supports' => ['title','editor','thumbnail','excerpt','comments'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'news'],
        'show_in_rest' => true,
    ]);

    // Social Post CPT
    register_post_type('social_post', [
        'label' => __('Social Posts', 'signalbridge-simulator'),
        'public' => true,
        'show_ui' => true,
        'menu_icon' => 'dashicons-share',
        'supports' => ['title','editor','thumbnail','comments'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'social'],
        'show_in_rest' => true,
    ]);
});

/**
 * Meta boxes for News Articles
 */
add_action('add_meta_boxes', function() {
    add_meta_box('sb_news_meta', __('News Options', 'signalbridge-simulator'), 'sb_news_meta_box', 'news_article', 'side');
    add_meta_box('sb_social_meta', __('Social Post Options', 'signalbridge-simulator'), 'sb_social_meta_box', 'social_post', 'normal', 'high');
    add_meta_box('sb_common_meta', __('Common Options', 'signalbridge-simulator'), 'sb_common_meta_box', ['news_article','social_post'], 'side');
});

function sb_news_meta_box($post) {
    $breaking = get_post_meta($post->ID, '_sb_breaking', true);
    $embed = esc_url( get_post_meta($post->ID, '_sb_embed_url', true) );
    wp_nonce_field('sb_news_meta','sb_news_meta_nonce');
    ?>
    <p><label><input type="checkbox" name="sb_breaking" <?php checked($breaking, '1'); ?>/> <?php _e('Breaking label', 'signalbridge-simulator'); ?></label></p>
    <p><label><?php _e('YouTube/Vimeo URL (oEmbed):', 'signalbridge-simulator'); ?><br/>
        <input type="url" name="sb_embed_url" value="<?php echo $embed; ?>" style="width:100%;" placeholder="https://www.youtube.com/watch?v=...">
    </label></p>
    <?php
}

function sb_social_meta_box($post) {
    $platform = get_post_meta($post->ID, '_sb_platform', true) ?: 'tweet';
    $avatar = esc_url( get_post_meta($post->ID, '_sb_avatar', true) );
    $display = esc_attr( get_post_meta($post->ID, '_sb_display', true) );
    $handle = esc_attr( get_post_meta($post->ID, '_sb_handle', true) );
    $timestamp = esc_attr( get_post_meta($post->ID, '_sb_timestamp', true) );
    $media = esc_url( get_post_meta($post->ID, '_sb_media', true) );
    $likes = esc_attr( get_post_meta($post->ID, '_sb_likes', true) );
    $comments = esc_attr( get_post_meta($post->ID, '_sb_comments', true) );
    $shares = esc_attr( get_post_meta($post->ID, '_sb_shares', true) );
    wp_nonce_field('sb_social_meta','sb_social_meta_nonce');
    ?>
    <p><label><?php _e('Platform:', 'signalbridge-simulator'); ?></label><br/>
        <select name="sb_platform">
            <option value="tweet" <?php selected($platform,'tweet'); ?>>Tweet</option>
            <option value="facebook" <?php selected($platform,'facebook'); ?>>Facebook</option>
            <option value="instagram" <?php selected($platform,'instagram'); ?>>Instagram</option>
        </select>
    </p>
    <p><label><?php _e('Avatar URL:', 'signalbridge-simulator'); ?><br/><input type="url" name="sb_avatar" value="<?php echo $avatar; ?>" style="width:100%;"></label></p>
    <p><label><?php _e('Display Name:', 'signalbridge-simulator'); ?><br/><input type="text" name="sb_display" value="<?php echo $display; ?>" style="width:100%;"></label></p>
    <p><label><?php _e('Handle (@handle):', 'signalbridge-simulator'); ?><br/><input type="text" name="sb_handle" value="<?php echo $handle; ?>" style="width:100%;"></label></p>
    <p><label><?php _e('Timestamp text (e.g., 5m, 2h):', 'signalbridge-simulator'); ?><br/><input type="text" name="sb_timestamp" value="<?php echo $timestamp; ?>" style="width:100%;"></label></p>
    <p><label><?php _e('Media URL (optional):', 'signalbridge-simulator'); ?><br/><input type="url" name="sb_media" value="<?php echo $media; ?>" style="width:100%;"></label></p>
    <div style="display:flex; gap:8px;">
        <p style="flex:1;"><label><?php _e('Likes:', 'signalbridge-simulator'); ?><br/><input type="number" name="sb_likes" value="<?php echo $likes; ?>" min="0" style="width:100%;"></label></p>
        <p style="flex:1;"><label><?php _e('Comments:', 'signalbridge-simulator'); ?><br/><input type="number" name="sb_comments" value="<?php echo $comments; ?>" min="0" style="width:100%;"></label></p>
        <p style="flex:1;"><label><?php _e('Shares/Retweets:', 'signalbridge-simulator'); ?><br/><input type="number" name="sb_shares" value="<?php echo $shares; ?>" min="0" style="width:100%;"></label></p>
    </div>
    <p><em><?php _e('Tip: put the post text in the main Editor field.', 'signalbridge-simulator'); ?></em></p>
    <?php
}

function sb_common_meta_box($post) {
    $alias = esc_attr( get_post_meta($post->ID, '_sb_alias', true) );
    $pinned = get_post_meta($post->ID, '_sb_pinned', true);
    $disable_comments = get_post_meta($post->ID, '_sb_disable_comments', true);
    wp_nonce_field('sb_common_meta','sb_common_meta_nonce');
    ?>
    <p><label><?php _e('Author Alias (byline):', 'signalbridge-simulator'); ?><br/><input type="text" name="sb_alias" value="<?php echo $alias; ?>" style="width:100%;" placeholder="e.g., Bob Smith"></label></p>
    <p><label><input type="checkbox" name="sb_pinned" <?php checked($pinned, '1'); ?>/> <?php _e('Pin to top of feed', 'signalbridge-simulator'); ?></label></p>
    <p><label><input type="checkbox" name="sb_disable_comments" <?php checked($disable_comments, '1'); ?>/> <?php _e('Disable comments for this post', 'signalbridge-simulator'); ?></label></p>
    <?php
}

/**
 * Save meta box values
 */
add_action('save_post', function($post_id) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( isset($_POST['post_type']) && ! current_user_can('edit_post', $post_id) ) return;

    // News
    if ( isset($_POST['sb_news_meta_nonce']) && wp_verify_nonce($_POST['sb_news_meta_nonce'], 'sb_news_meta') ) {
        update_post_meta($post_id, '_sb_breaking', isset($_POST['sb_breaking']) ? '1' : '0');
        if ( isset($_POST['sb_embed_url']) ) update_post_meta($post_id, '_sb_embed_url', esc_url_raw($_POST['sb_embed_url']) );
    }
    // Social
    if ( isset($_POST['sb_social_meta_nonce']) && wp_verify_nonce($_POST['sb_social_meta_nonce'], 'sb_social_meta') ) {
        $fields = [
            '_sb_platform' => 'sb_platform',
            '_sb_avatar' => 'sb_avatar',
            '_sb_display' => 'sb_display',
            '_sb_handle' => 'sb_handle',
            '_sb_timestamp' => 'sb_timestamp',
            '_sb_media' => 'sb_media',
            '_sb_likes' => 'sb_likes',
            '_sb_comments' => 'sb_comments',
            '_sb_shares' => 'sb_shares',
        ];
        foreach ($fields as $meta => $post_key) {
            if ( isset($_POST[$post_key]) ) {
                $val = $_POST[$post_key];
                if (in_array($post_key, ['sb_likes','sb_comments','sb_shares'], true)) $val = (int)$val;
                update_post_meta($post_id, $meta, sanitize_text_field($val) );
            }
        }
    }
    // Common
    if ( isset($_POST['sb_common_meta_nonce']) && wp_verify_nonce($_POST['sb_common_meta_nonce'], 'sb_common_meta') ) {
        if ( isset($_POST['sb_alias']) ) update_post_meta($post_id, '_sb_alias', sanitize_text_field($_POST['sb_alias']) );
        update_post_meta($post_id, '_sb_pinned', isset($_POST['sb_pinned']) ? '1' : '0');
        update_post_meta($post_id, '_sb_disable_comments', isset($_POST['sb_disable_comments']) ? '1' : '0');
    }
});

/**
 * Override comments open if disabled per post
 */
add_filter('comments_open', function($open, $post_id) {
    $disable = get_post_meta($post_id, '_sb_disable_comments', true);
    if ( $disable === '1' ) return false;
    return $open;
}, 10, 2);

/**
 * Helper: get byline
 */
function sb_get_byline($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    $alias = get_post_meta($post_id, '_sb_alias', true);
    if ( $alias ) return esc_html($alias);
    return get_the_author();
}

/**
 * Pre-get-posts to order pinned first on main queries
 */
add_action('pre_get_posts', function($q) {
    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( $q->is_home() || $q->is_post_type_archive(['news_article','social_post']) || $q->is_tax('scenario') ) {
        // Order by pinned (meta) then date
        $q->set('meta_key', '_sb_pinned');
        $q->set('orderby', ['meta_value_num' => 'DESC', 'date' => 'DESC']);
    }
});

/**
 * Short helper to embed video (news)
 */
function sb_news_embed_html($post_id) {
    $url = get_post_meta($post_id, '_sb_embed_url', true);
    if ( ! $url ) return '';
    $html = wp_oembed_get($url);
    if ( ! $html ) return '';
    return '<div class="sb-embed">'.$html.'</div>';
}

/**
 * Simple filter tabs query var: ?feed=all|news|tweet|facebook|instagram
 */
function sb_feed_filter_current() {
    $allowed = ['all','news','tweet','facebook','instagram'];
    $f = isset($_GET['feed']) ? sanitize_text_field($_GET['feed']) : 'all';
    return in_array($f, $allowed, true) ? $f : 'all';
}

