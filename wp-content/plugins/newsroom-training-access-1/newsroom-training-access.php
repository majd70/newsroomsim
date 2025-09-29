<?php
/*
Plugin Name: Newsroom Training Access
Description: Branded login/registration, trainee approvals, operator aliasing, and logout tools for the Newsroom Training site.
Version: 1.0.0
Author: Mahfuz
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NR_Access_Plugin {
    const VERSION = '1.0.0';
    const TABLE_REG = 'nr_registration_meta'; // stores org & scenario for trainees
    const TAX_ALIAS = 'nr_alias';
    const ROLE_TRAINEE = 'trainee';
    const ROLE_TRAINEE_PENDING = 'trainee_pending';
    const ROLE_OPERATOR = 'operator';

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'on_activate'));
        register_deactivation_hook(__FILE__, array($this, 'on_deactivate'));

        add_action('init', array($this, 'register_alias_taxonomy'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('init', array($this, 'maybe_block_pending_login'));

        add_action('admin_menu', array($this, 'register_admin_pages'));
        add_action('admin_post_nr_approve_user', array($this, 'handle_approve_user'));
        add_action('admin_post_nr_deny_user', array($this, 'handle_deny_user'));

        add_action('add_meta_boxes', array($this, 'add_alias_metabox'));
        add_action('save_post', array($this, 'save_alias_metabox'));

        add_filter('the_author', array($this, 'maybe_show_alias'), 20);
        add_filter('get_the_author_display_name', array($this, 'maybe_show_alias'), 20);

        // enqueue minimal styles for forms
        add_action('wp_enqueue_scripts', function() {
            wp_register_style('nr-access-styles', plugins_url('assets/style.css', __FILE__), array(), self::VERSION);
        });
    }

    public function on_activate() {
        // roles & caps
        add_role(self::ROLE_TRAINEE_PENDING, 'Trainee (Pending)', array('read' => true));
        add_role(self::ROLE_TRAINEE, 'Trainee', array('read' => true));
        add_role(self::ROLE_OPERATOR, 'Operator', array(
            'read' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
        ));

        // DB table for registration meta
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_REG;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            organization VARCHAR(191) DEFAULT '' NOT NULL,
            scenario_code VARCHAR(64) DEFAULT '' NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // register taxonomy on activation too
        $this->register_alias_taxonomy();
        flush_rewrite_rules();
    }

    public function on_deactivate() {
        flush_rewrite_rules();
    }

    public function register_alias_taxonomy() {
        $labels = array(
            'name' => 'Author Aliases',
            'singular_name' => 'Author Alias',
            'add_new_item' => 'Add New Alias',
            'search_items' => 'Search Aliases',
        );
        register_taxonomy(self::TAX_ALIAS, array('post','news_article','social_post'), array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => false,
        ));
    }

    public function register_shortcodes() {
        add_shortcode('newsroom_login', array($this, 'shortcode_login'));
        add_shortcode('newsroom_register', array($this, 'shortcode_register'));
        add_shortcode('newsroom_logout_link', array($this, 'shortcode_logout_link'));
    }

    // ---------- Auth flow ----------
    public function shortcode_login($atts = array(), $content = '') {
        if (is_user_logged_in()) {
            return '<div class="nr-card"><p>You are already logged in.</p></div>';
        }
        wp_enqueue_style('nr-access-styles');
        ob_start();
        include __DIR__ . '/templates/login-form.php';
        return ob_get_clean();
    }

    public function shortcode_register($atts = array(), $content = '') {
        if (is_user_logged_in()) {
            return '<div class="nr-card"><p>You are logged in.</p></div>';
        }
        $errors = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nr_register_nonce']) && wp_verify_nonce($_POST['nr_register_nonce'], 'nr_register')) {
            $name  = sanitize_text_field($_POST['full_name'] ?? '');
            $email = sanitize_email($_POST['email'] ?? '');
            $org   = sanitize_text_field($_POST['organization'] ?? '');
            $sc    = sanitize_text_field($_POST['scenario_code'] ?? '');
            $pass  = $_POST['password'] ?? '';
            $pass2 = $_POST['password_confirm'] ?? '';

            if (!$name) $errors[] = 'Full name is required.';
            if (!$email || !is_email($email)) $errors[] = 'Valid email is required.';
            if (!$org) $errors[] = 'Organization is required.';
            if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
            if ($pass !== $pass2) $errors[] = 'Passwords do not match.';
            if (email_exists($email)) $errors[] = 'An account already exists for that email.';

            if (empty($errors)) {
                $username = sanitize_user(current(explode('@', $email)));
                $user_id = wp_insert_user(array(
                    'user_login' => $username . wp_rand(10,99),
                    'user_email' => $email,
                    'display_name' => $name,
                    'user_pass' => $pass,
                    'role' => self::ROLE_TRAINEE_PENDING,
                ));
                if (is_wp_error($user_id)) {
                    $errors[] = $user_id->get_error_message();
                } else {
                    // save meta
                    update_user_meta($user_id, 'nr_full_name', $name);
                    update_user_meta($user_id, 'nr_organization', $org);

                    global $wpdb;
                    $wpdb->insert($wpdb->prefix . self::TABLE_REG, array(
                        'user_id' => $user_id,
                        'organization' => $org,
                        'scenario_code' => $sc,
                    ));

                    // email admin for approval
                    $admin_email = get_option('admin_email');
                    $approve_url = admin_url('admin.php?page=nr-approvals');
                    $subject = sprintf('[Newsroom] New trainee registration: %s', $name);
                    $message = sprintf("A new trainee has registered.\n\nName: %s\nEmail: %s\nOrganization: %s\nScenario: %s\n\nApprove here: %s",
                        $name, $email, $org, $sc, $approve_url);
                    wp_mail($admin_email, $subject, $message);

                    wp_redirect(add_query_arg('registered', '1', wp_get_referer() ?: home_url('/')));
                    exit;
                }
            }
        }

        wp_enqueue_style('nr-access-styles');
        ob_start();
        $register_errors = $errors;
        include __DIR__ . '/templates/register-form.php';
        return ob_get_clean();
    }

    public function shortcode_logout_link($atts = array()) {
        $redirect = isset($atts['redirect']) ? esc_url($atts['redirect']) : home_url('/');
        return sprintf('<a class="button" href="%s">Log out</a>', esc_url(wp_logout_url($redirect)));
    }

    // public function maybe_block_pending_login() {
    //     add_filter('authenticate', function($user, $username, $password) {
    //         if ($user instanceof WP_User) {
    //             if (in_array(self::ROLE_TRAINEE_PENDING, (array) $user->roles, true)) {
    //                 return new WP_Error('nr_pending', __('Your account is pending approval by an admin.'));
    //             }
    //         }
    //         return $user;
    //     }, 30, 3);
    // }

public function maybe_block_pending_login() {
    add_filter('authenticate', function($user, $username, $password) {
        // Always allow wp-admin login
        if (is_admin()) return $user;
        if ($user instanceof WP_User) {
            if (in_array(self::ROLE_TRAINEE_PENDING, (array) $user->roles, true)) {
                return new WP_Error('nr_pending', __('Your account is pending approval by an admin.'));
            }
        }
        return $user;
    }, 30, 3);
}


    // ---------- Admin: approvals ----------
    public function register_admin_pages() {
        add_menu_page(
            'Newsroom Admin',
            'Newsroom Admin',
            'list_users',
            'nr-admin',
            array($this, 'render_admin_dashboard'),
            'dashicons-groups',
            3
        );

        add_submenu_page(
            'nr-admin',
            'Trainee Approvals',
            'Trainee Approvals',
            'list_users',
            'nr-approvals',
            array($this, 'render_approvals_page')
        );
    }

    public function render_admin_dashboard() {
        echo '<div class="wrap"><h1>Newsroom Admin</h1>';
        echo '<p><a class="button button-primary" href="'.esc_url(admin_url('admin.php?page=nr-approvals')).'">Go to Trainee Approvals</a> ';
        echo '<a class="button" href="'.esc_url(wp_logout_url(home_url('/'))).'">Log out</a></p>';
        echo '</div>';
    }

    public function render_approvals_page() {
        if (!current_user_can('list_users')) { wp_die('Insufficient permissions'); }
        $pending = get_users(array('role' => self::ROLE_TRAINEE_PENDING, 'fields' => 'all_with_meta'));
        echo '<div class="wrap"><h1>Trainee Approvals</h1>';
        if (empty($pending)) {
            echo '<p>No pending trainees.</p></div>';
            return;
        }
        echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Email</th><th>Organization</th><th>Registered</th><th>Actions</th></tr></thead><tbody>';
        foreach ($pending as $u) {
            $org = get_user_meta($u->ID, 'nr_organization', true);
            $created = esc_html($u->user_registered);
            $approve = wp_nonce_url(admin_url('admin-post.php?action=nr_approve_user&user_id='.$u->ID), 'nr_approve_'.$u->ID);
            $deny = wp_nonce_url(admin_url('admin-post.php?action=nr_deny_user&user_id='.$u->ID), 'nr_deny_'.$u->ID);
            echo '<tr><td>'.esc_html($u->display_name).'</td><td>'.esc_html($u->user_email).'</td><td>'.esc_html($org).'</td><td>'.$created.'</td><td>';
            echo '<a class="button button-primary" href="'.esc_url($approve).'">Approve</a> ';
            echo '<a class="button" href="'.esc_url($deny).'" onclick="return confirm(\'Deny and delete this user?\')">Deny</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function handle_approve_user() {
        if (!current_user_can('promote_users')) { wp_die('Insufficient permissions'); }
        $user_id = absint($_GET['user_id'] ?? 0);
        check_admin_referer('nr_approve_'.$user_id);
        $user = get_user_by('id', $user_id);
        if ($user) {
            $user->set_role(self::ROLE_TRAINEE);
            // email user
            wp_mail($user->user_email, '[Newsroom] Account approved', "Hi {$user->display_name},\n\nYour account has been approved. You can now log in.\n\n".wp_login_url());
        }
        wp_redirect(admin_url('admin.php?page=nr-approvals&approved=1'));
        exit;
    }

    public function handle_deny_user() {
        if (!current_user_can('delete_users')) { wp_die('Insufficient permissions'); }
        $user_id = absint($_GET['user_id'] ?? 0);
        check_admin_referer('nr_deny_'.$user_id);
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($user_id);
        wp_redirect(admin_url('admin.php?page=nr-approvals&denied=1'));
        exit;
    }

    // ---------- Alias meta box ----------
    public function add_alias_metabox() {
        $screens = array('post', 'news_article', 'social_post');
        foreach ($screens as $s) {
            add_meta_box('nr_alias_box', 'Publish As (Alias)', array($this, 'render_alias_metabox'), $s, 'side', 'high');
        }
    }

    public function render_alias_metabox($post) {
        wp_nonce_field('nr_alias_box', 'nr_alias_nonce');
        $current = get_post_meta($post->ID, 'nr_alias_term_id', true);
        $terms = get_terms(array('taxonomy' => self::TAX_ALIAS, 'hide_empty' => false));
        echo '<p>Select an alias to publish under:</p>';
        echo '<select name="nr_alias_term_id" style="width:100%"><option value="">— Show real author —</option>';
        foreach ($terms as $t) {
            printf('<option value="%d" %s>%s</option>', $t->term_id, selected($current, $t->term_id, false), esc_html($t->name));
        }
        echo '</select>';
        echo '<p><em>Create/manage aliases in Posts → Author Aliases.</em></p>';
    }

    public function save_alias_metabox($post_id) {
        if (!isset($_POST['nr_alias_nonce']) || !wp_verify_nonce($_POST['nr_alias_nonce'], 'nr_alias_box')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        $term_id = isset($_POST['nr_alias_term_id']) ? absint($_POST['nr_alias_term_id']) : 0;
        if ($term_id) {
            update_post_meta($post_id, 'nr_alias_term_id', $term_id);
            wp_set_post_terms($post_id, array($term_id), self::TAX_ALIAS, false);
        } else {
            delete_post_meta($post_id, 'nr_alias_term_id');
        }
    }

    public function maybe_show_alias($display) {
        if (is_admin()) return $display;
        global $post;
        if ($post) {
            $term_id = get_post_meta($post->ID, 'nr_alias_term_id', true);
            if ($term_id) {
                $t = get_term($term_id, self::TAX_ALIAS);
                if ($t && !is_wp_error($t)) {
                    return $t->name;
                }
            }
        }
        return $display;
    }
}

new NR_Access_Plugin();



// Redirect only if not already on our custom pages

// --- Replaced aggressive login_init redirect with safe filters ---
// We avoid interfering with wp-login.php when WordPress core, SSO, or admin flows are occurring.
// Instead we filter login_url and register_url so front-end links point to /login and /register,
// but allow wp-login.php and admin/SSO flows to continue untouched.
// add_filter('login_url', function($login_url, $redirect, $force_reauth) {
//     // If redirect aims at wp-admin (SSO/admin flows), keep original
//     if (!empty($redirect) && strpos($redirect, '/wp-admin') !== false) {
//         return $login_url;
//     }

//     // If host looks like GoDaddy/Installatron proxy, keep original
//     if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'secureserver.net') !== false) {
//         return $login_url;
//     }

//     // If code is running during wp-login.php execution, do not rewrite — avoids loops during logout/SSO
//     if (!empty($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
//         return $login_url;
//     }

//     // Otherwise point standard login links to our frontend /login page
//     return home_url('/login');
// }, 10, 3);

// // Same approach for register links
// add_filter('register_url', function($register_url) {
//     if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'secureserver.net') !== false) {
//         return $register_url;
//     }
//     if (!empty($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
//         return $register_url;
//     }
//     return home_url('/register');
// }, 10, 1);


add_filter('login_url', function($login_url, $redirect, $force_reauth) {
    if (is_admin()) return $login_url;
    return home_url('/login');
}, 10, 3);

add_filter('register_url', function($register_url) {
    if (is_admin()) return $register_url;
    return home_url('/register');
}, 10, 1);




// Register custom endpoints for login/register
add_action('init', function () {
    add_rewrite_rule('^login/?$', 'index.php?newsroom_page=login', 'top');
    add_rewrite_rule('^register/?$', 'index.php?newsroom_page=register', 'top');
});

// Add query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'newsroom_page';
    return $vars;
});

// Handle custom login and register routes
// add_action('template_redirect', function() {
//     if (is_admin()) return; // skip admin requests
//     global $wp;
//     $path = trim($wp->request, '/');

//     // --- LOGIN ---
//     if ($path === 'login') {
//         // If form is posted
//         if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'], $_POST['pwd'])) {
//             $creds = array(
//                 'user_login'    => sanitize_user($_POST['log']),
//                 'user_password' => $_POST['pwd'],
//                 'remember'      => !empty($_POST['rememberme']),
//             );

//                 $user = wp_signon($creds, false);

//                 if (is_wp_error($user)) {
//                     // Failed login → back to login page with error
//                     wp_safe_redirect(home_url('/login?login=failed'));
//                     exit;
//                 } else {
//                     // Success → always redirect to your front-end theme page
//                     $redirect_to = home_url('/'); // Change to your desired front-end page
//                     // Force HTTPS if needed
//                     if (is_ssl()) {
//                         $redirect_to = str_replace('http://', 'https://', $redirect_to);
//                     }
//                     wp_safe_redirect($redirect_to);
//                     exit;
//                 }

//         }

//         // Otherwise just display the login form
//         include plugin_dir_path(__FILE__) . 'templates/login-template.php';
//         exit;
//     }

//     // --- REGISTER ---
//     if ($path === 'register') {
//         include plugin_dir_path(__FILE__) . 'templates/register-template.php';
//         exit;
//     }
// });


// add_action('template_redirect', function() {
//     if (is_admin()) return; // skip admin entirely

//     global $wp;
//     $path = trim($wp->request, '/');

//     // --- LOGIN ---
//     if ($path === 'login') {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'], $_POST['pwd'])) {
//             $creds = array(
//                 'user_login'    => sanitize_text_field($_POST['log']),
//                 'user_password' => $_POST['pwd'],
//                 'remember'      => !empty($_POST['rememberme']),
//             );

//             $user = wp_signon($creds, false);

//             if (is_wp_error($user)) {
//                 wp_safe_redirect(home_url('/login?login=failed'));
//                 exit;
//             } else {
//                 // Redirect to front-end page
//                 $redirect_to = home_url('/'); // change to any page you want
//                 if (is_ssl()) $redirect_to = str_replace('http://', 'https://', $redirect_to);
//                 wp_safe_redirect($redirect_to);
//                 exit;
//             }
//         }

//         // Show login template
//         include plugin_dir_path(__FILE__) . 'templates/login-template.php';
//         exit;
//     }

//     // --- REGISTER ---
//     if ($path === 'register') {
//         include plugin_dir_path(__FILE__) . 'templates/register-template.php';
//         exit;
//     }
// });



add_action('template_redirect', function() {
    if (is_admin()) return; // don't touch wp-admin direct requests

    global $wp;
    $path = trim($wp->request, '/');

    // --- LOGIN PAGE ---
    if ($path === 'login') {
        $login_error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'], $_POST['pwd'])) {
            $creds = array(
                'user_login'    => sanitize_text_field($_POST['log']),
                'user_password' => $_POST['pwd'],
                'remember'      => !empty($_POST['rememberme']),
            );

            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                $login_error = $user->get_error_message();
            } else {
                // ⏳ Pending trainees blocked
                if (in_array('trainee', (array) $user->roles, true) && get_user_meta($user->ID, 'is_approved', true) !== 'yes') {
                    wp_logout();
                    $login_error = "⏳ Your account is waiting for admin approval. Please try again later.";
                } else {
                    // 👑 Admins → auto-login backend + redirect to home
                    if (user_can($user, 'administrator')) {
                        wp_set_current_user($user->ID);
                        wp_set_auth_cookie($user->ID, true); // persist session
                        wp_safe_redirect(home_url('/'));
                        exit;
                    }

                    // 👤 Normal users → redirect to homepage
                    wp_safe_redirect(home_url('/'));
                    exit;
                }
            }
        }

        // Render login template with error if any
        include plugin_dir_path(__FILE__) . 'templates/login-template.php';
        exit;
    }

    // --- REGISTER PAGE ---
    if ($path === 'register') {
        include plugin_dir_path(__FILE__) . 'templates/register-template.php';
        exit;
    }
});





// -----------------------------
// Trainee notifier: settings + mail + debug/test
// -----------------------------

// Register setting (if not already registered)
function trn_register_settings() {
    add_option('trn_notification_email', get_option('admin_email'));
    register_setting('trn_options_group', 'trn_notification_email', 'sanitize_email');
}
add_action('admin_init', 'trn_register_settings');

// Add settings page under Settings menu (if not already added)
function trn_register_options_page() {
    add_options_page('Trainee Notifier Settings', 'Trainee Notifier', 'manage_options', 'trn', 'trn_options_page');
}
add_action('admin_menu', 'trn_register_options_page');

// Settings page content (also a Test Email button)
function trn_options_page() {
    if ( ! current_user_can('manage_options') ) {
        return;
    }

    // Handle Test Email submit securely
    if ( isset($_POST['trn_send_test']) && check_admin_referer('trn_send_test_action', 'trn_send_test_nonce') ) {
        $test_to = get_option('trn_notification_email', get_option('admin_email'));
        $subject = 'Trainee Notifier - Test Email';
        $message = '<p>This is a <strong>test</strong> email from the Trainee Notifier plugin.</p>';
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Newsroom Training <no-reply@yourdomain.com>'  // <-- change this to a valid domain email
        );

        $sent = wp_mail($test_to, $subject, $message, $headers);

        if ( $sent ) {
            echo '<div class="notice notice-success"><p>Test email successfully sent to '.esc_html($test_to).'</p></div>';
            error_log('Trainee notifier: Test email sent to '.$test_to);
        } else {
            echo '<div class="notice notice-error"><p>Test email FAILED. Check debug.log and server SMTP settings.</p></div>';
            error_log('Trainee notifier: Test email FAILED to '.$test_to);
        }
    }

    // Settings form
    ?>
    <div class="wrap">
        <h1>Trainee Registration Notifier</h1>

        <form method="post" action="options.php">
            <?php settings_fields('trn_options_group'); ?>
            <?php do_settings_sections('trn_options_group'); ?>

            <table class="form-table">
                <tr valign="top">
                <th scope="row"><label for="trn_notification_email">Notification Email</label></th>
                <td>
                    <input type="email" id="trn_notification_email" name="trn_notification_email"
                        value="<?php echo esc_attr( get_option('trn_notification_email') ); ?>" style="width:300px;" />
                    <p class="description">Where registration notifications should be sent.</p>
                </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr/>

        <h2>Send a test email</h2>
        <form method="post" style="margin-top:10px;">
            <?php wp_nonce_field('trn_send_test_action', 'trn_send_test_nonce'); ?>
            <input type="submit" name="trn_send_test" class="button button-primary" value="Send test email to configured address" />
        </form>
        <p class="description">Use this to verify whether WordPress can send mail from this server.</p>
    </div>
    <?php
}


// Send notification on registration
function trn_send_email_on_registration($user_id) {
    $user = get_userdata($user_id);
    if ( ! $user ) {
        error_log('Trainee notifier: user_id invalid in send_email_on_registration: '.$user_id);
        return;
    }

    $to = get_option('trn_notification_email', get_option('admin_email'));
    if ( empty($to) ) {
        $to = get_option('admin_email'); // fallback
    }

    $subject = 'New Trainee Registered';
    $message = '<p>Hello,</p>';
    $message .= '<p>A new trainee has registered with the following details:</p>';
    $message .= '<ul>';
    $message .= '<li><strong>Full name:</strong> ' . esc_html( $user->display_name ) . '</li>';
    $message .= '<li><strong>Username:</strong> ' . esc_html( $user->user_login ) . '</li>';
    $message .= '<li><strong>Email:</strong> ' . esc_html( $user->user_email ) . '</li>';
    $message .= '</ul>';
    $message .= '<p>Regards,<br/>Your Website</p>';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Newsroom Training <no-reply@yourdomain.com>' // <-- change to a valid domain email
    );

    $sent = wp_mail($to, $subject, $message, $headers);

    if ( $sent ) {
        error_log('Trainee notifier: Registration email SENT to '.$to.' for user_id '.$user_id);
    } else {
        error_log('Trainee notifier: Registration email FAILED to '.$to.' for user_id '.$user_id);
        // more info: the wp_mail_failed action (below) will capture the WP_Error object and log it.
    }
}
add_action('user_register', 'trn_send_email_on_registration');


// Log WP mail failures (WP will pass a WP_Error object here)
function trn_mail_failed( $wp_error ) {
    if ( is_wp_error( $wp_error ) ) {
        error_log('Trainee notifier: wp_mail_failed: '. print_r( $wp_error->get_error_messages(), true ));
    } else {
        error_log('Trainee notifier: wp_mail_failed: ' . print_r( $wp_error, true ));
    }
}
add_action('wp_mail_failed', 'trn_mail_failed', 10, 1);


// OPTIONAL: force From name/email via filters (useful if your host ignores custom headers)
// Replace no-reply@yourdomain.com with a valid sender on your domain
function trn_sender_email($original_email_address) {
    return 'no-reply@yourdomain.com';
}
function trn_sender_name($original_email_from) {
    return 'Newsroom Training';
}
add_filter('wp_mail_from', 'trn_sender_email');
add_filter('wp_mail_from_name', 'trn_sender_name');


// // Fix infinite redirect loop on /login and /register
// add_filter('login_url', function($login_url, $redirect, $force_reauth) {
//     // If current page is already our custom login page → don’t rewrite
//     if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/login') !== false) {
//         return $login_url;
//     }

//     // If redirect aims at /wp-admin → keep original
//     if (!empty($redirect) && strpos($redirect, '/wp-admin') !== false) {
//         return $login_url;
//     }

//     // If GoDaddy SSO host → keep original
//     if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'secureserver.net') !== false) {
//         return $login_url;
//     }

//     // If running inside wp-login.php itself → keep original
//     if (!empty($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
//         return $login_url;
//     }

//     // Otherwise rewrite normal links to our custom page
//     return home_url('/login');
// }, 10, 3);

// add_filter('register_url', function($register_url) {
//     // If already on custom register page → don’t rewrite
//     if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/register') !== false) {
//         return $register_url;
//     }

//     if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'secureserver.net') !== false) {
//         return $register_url;
//     }
//     if (!empty($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'wp-login.php') !== false) {
//         return $register_url;
//     }

//     return home_url('/register');
// }, 10, 1);

add_action('wp_logout', function() {
    wp_safe_redirect(home_url('/login?loggedout=true'));
    exit;
});

