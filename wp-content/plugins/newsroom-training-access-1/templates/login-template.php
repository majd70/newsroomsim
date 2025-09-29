<?php
/* Template Name: Custom Login Page */
if (is_user_logged_in()) {
    wp_safe_redirect(home_url('/')); // default theme homepage
    exit;
}

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $creds = array();
    $creds['user_login']    = sanitize_text_field($_POST['log']);
    $creds['user_password'] = $_POST['pwd'];
    $creds['remember']      = true;

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        // ✅ Check if trainee & not approved
        if (in_array('trainee', (array) $user->roles) && get_user_meta($user->ID, 'is_approved', true) !== 'yes') {
            wp_logout(); // force logout
            $login_error = "⏳ Your account is waiting for admin approval. Please try again later.";
        } else {
            wp_safe_redirect(home_url('/')); // redirect approved users
            exit;
        }
    }
}
?>




<style>
/* Full height fix */
html, body {
    height: 100%;
    margin: 0;
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background: #f0f2f5;
}

/* Center container */
.login-wrapper {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Card */
.login-card {
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.12);
    width: 360px;
    text-align: center;
}

/* Title */
.login-card h2 {
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: 600;
    color: #222;
}

/* Inputs */
.login-card input {
    width: 100%;
    padding: 12px;
    margin-bottom: 16px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

/* Button */
.login-card button {
    width: 100%;
    padding: 12px;
    background: #1a73e8;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    font-weight: 500;
}
.login-card button:hover {
    background: #1669c1;
}

/* Links */
.login-card .links {
    margin-top: 16px;
    font-size: 14px;
}
.login-card .links a {
    color: #1a73e8;
    text-decoration: none;
    font-weight: 500;
}
.login-card .links a:hover {
    text-decoration: underline;
}

/* Error message */
.login-card .error {
    background: #ffe6e6;
    color: #cc0000;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 13px;
}
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Sign In</h2>

        <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
            <div class="notice success" style="background:#e6ffe6; padding:10px; margin-bottom:15px; border-radius:6px; color:#006600;">
                ✅ Registration successful! Your account is pending approval by an admin.
            </div>
        <?php endif; ?>

        <?php if ($login_error): ?>
            <div class="error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
      <form method="post" action="<?php echo esc_url( home_url('/login') ); ?>">
            <input type="text" name="log" placeholder="Email Address" required>
            <input type="password" name="pwd" placeholder="Password" required>
            <button type="submit" name="login_submit">Sign In</button>
        </form>


        
        <div class="links">
            <a href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a>
        </div>
        
        <div class="links">
            Need an account? <a href="<?php echo site_url('/register'); ?>">Register here</a>
        </div>
    </div>
</div>


