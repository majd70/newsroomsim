<?php
/**
 * Template Name: Reset Password
 */

// ✅ Handle password reset BEFORE header output
if (is_user_logged_in()) {
    $user = wp_get_current_user();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
        $new_password = sanitize_text_field($_POST['new_password']);
        wp_set_password($new_password, $user->ID);
        wp_logout();

        // Redirect to login with success flag
        wp_safe_redirect(add_query_arg('reset', 'success', wp_login_url()));
        exit; // 🔴 stop execution here
    }
}

get_header(); // ✅ only called after logic above is done
?>

<div class="reset-password-form" style="max-width:400px; margin:40px auto; border:1px solid #ddd; padding:20px; border-radius:10px;">
    <h2>Reset Your Password</h2>

    <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success') : ?>
        <p style="color:green;">✅ Password successfully reset. Please log in again.</p>
    <?php elseif (is_user_logged_in()) : ?>
        <form method="post">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" required style="width:100%; padding:10px; margin:10px 0;">
            <button type="submit" style="background:#0073aa; color:#fff; padding:10px 20px; border:none; border-radius:5px;">Update Password</button>
        </form>
    <?php else : ?>
        <p>You must be logged in to reset your password.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
