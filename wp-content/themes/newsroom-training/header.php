<!DOCTYPE html>
<html <?php if (function_exists('language_attributes')) language_attributes(); ?>>
<head>
    <meta charset="<?php if (function_exists('bloginfo')) bloginfo('charset'); else echo 'UTF-8'; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        if (function_exists('wp_title') && function_exists('bloginfo')) {
            wp_title('|', true, 'right'); 
            bloginfo('name');
        } else {
            echo defined('THEME_NAME') ? THEME_NAME : 'Newsroom Training Platform';
        }
    ?></title>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
    if (function_exists('wp_head')) {
        wp_head();
    } else {
        // Standalone mode - include CSS manually
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' . "\n";
        echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">' . "\n";
        echo '<link href="' . (defined('ASSETS_URL') ? ASSETS_URL : '/themes/newsroom-training/assets') . '/css/style.css" rel="stylesheet">' . "\n";
    }
    ?>
    <style>
        /* Ensure dropdown menu works properly */
        .navbar-nav .dropdown-menu {
            position: absolute !important;
            z-index: 1050 !important;
        }

        /* Fix dropdown positioning */
        .navbar-nav .dropdown {
            position: relative;
        }

        /* Ensure dropdown shows on click */
        .navbar-nav .dropdown-menu.show {
            display: block !important;
        }
    </style>
</head>
<body <?php if (function_exists('body_class')) body_class(); ?>>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php 
       if (function_exists('the_custom_logo') && has_custom_logo()) {
    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
    echo '<img src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '" width="200" height="30">';
}
            ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo function_exists('home_url') ? home_url() : '/'; ?>">Feed</a>
                </li>

                <?php
                // Check user capabilities
                $is_admin = false;
                $can_create_content = false;

                if (function_exists('current_user_can')) {
                    // Administrator - Full access
                    $is_admin = current_user_can('manage_options');

                    // Newsroom Operator OR Administrator - Can create content
                    $can_create_content = current_user_can('edit_posts') || current_user_can('publish_posts');
                } elseif (isset($GLOBALS['user']) && $GLOBALS['user']) {
                    $is_admin = ($GLOBALS['user']['role'] === 'administrator');
                    $can_create_content = in_array($GLOBALS['user']['role'], ['administrator', 'newsroom_operator']);
                }

                // Show "Create Content" for Administrator and Newsroom Operator only
                if ($can_create_content):
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo function_exists('admin_url') ? admin_url('admin.php?page=newsroom-create-content') : 'create-content.php'; ?>">Create Content</a>
                </li>
                <?php endif; ?>

                <?php
                // Show "Admin" for Administrator only
                if ($is_admin):
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo function_exists('admin_url') ? admin_url('admin.php?page=newsroom-training') : 'admin.php'; ?>">Admin</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo function_exists('home_url') ? home_url() : '/'; ?>">Blog</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php 
                $logged_in = false;
                $user_name = '';
                $logout_url = '';
                $login_url = '';
                
                if (function_exists('is_user_logged_in')) {
                    // WordPress mode
                    $logged_in = is_user_logged_in();
                    if ($logged_in) {
                        $user_name = wp_get_current_user()->display_name;
                        $logout_url = wp_logout_url(home_url());
                    } else {
                        $login_url = wp_login_url();
                    }
                } else {
                    // Standalone mode
                    $logged_in = isset($GLOBALS['user']) && $GLOBALS['user'];
                    if ($logged_in) {
                        $user_name = $GLOBALS['user']['name'];
                        $logout_url = 'logout.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
                    } else {
                        $login_url = 'login.php';
                    }
                }
                
                if ($logged_in): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i><?php echo esc_html($user_name); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <?php if (function_exists('admin_url')): ?>
                        <!-- <li><a class="dropdown-item" href="<?php echo admin_url(); ?>">Dashboard</a></li> -->
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?php echo site_url('/reset-password'); ?>">Reset Password</a></li>
                        <li><a class="dropdown-item" href="<?php echo $logout_url; ?>">Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $login_url; ?>">Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>