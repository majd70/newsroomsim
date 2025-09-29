<?php
/* Template Name: Custom Register Page */

if (is_user_logged_in()) {
  wp_redirect(site_url('/login?registered=1'));
    exit;
}

$reg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $fullname   = sanitize_text_field($_POST['fullname']);
    $email      = sanitize_email($_POST['email']);
    $org        = sanitize_text_field($_POST['organization']);
    $scenario   = sanitize_text_field($_POST['scenario']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    if (username_exists($email) || email_exists($email)) {
        $reg_error = 'Email already registered.';
    } elseif ($password !== $confirm) {
        $reg_error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $reg_error = 'Password must be at least 6 characters.';
    } else {
        $user_id = wp_create_user($email, $password, $email);
        if (!is_wp_error($user_id)) {
            update_user_meta($user_id, 'full_name', $fullname);
            update_user_meta($user_id, 'organization', $org);
            update_user_meta($user_id, 'scenario_code', $scenario);

            // 🔑 Mark user as pending
            update_user_meta($user_id, 'newsroom_status', 'pending');

            // 🔑 Remove role so they can’t login until approved
            $user = new WP_User($user_id);
            $user->set_role('');

            // ❌ REMOVE auto login lines:
            // wp_set_current_user($user_id);
            // wp_set_auth_cookie($user_id);

            // ✅ Redirect to login with message
            wp_redirect(site_url('/login?registered=1'));
            exit;
        } else {
            $reg_error = $user_id->get_error_message();
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
    background: #f0f2f5; /* same as login page */
}

/* Center container */
.register-wrapper {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Card */
.register-card {
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.12);
    width: 500px;
}

/* Title */
.register-card h2 {
    margin-bottom: 10px;
    font-size: 24px;
    font-weight: 600;
    text-align: center;
    color: #222;
}
.register-card p.sub {
    text-align: center;
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
}

/* Grid layout for inputs */
.register-card .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Full width row */
.register-card .form-row {
    grid-column: span 2;
}

/* Inputs */
.register-card input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

/* Labels */
.register-card label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

/* Button */
.register-card button {
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
.register-card button:hover {
    background: #1669c1;
}

/* Links */
.register-card .links {
    margin-top: 16px;
    text-align: center;
    font-size: 14px;
}
.register-card .links a {
    color: #1a73e8;
    text-decoration: none;
    font-weight: 500;
}
.register-card .links a:hover {
    text-decoration: underline;
}

/* Error message */
.register-card .error {
    background: #ffe6e6;
    color: #cc0000;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 13px;
}
</style>

<div class="register-wrapper">
    <div class="register-card">
        <h2>Register for Training</h2>
        <p class="sub">Request access to the newsroom training platform</p>
        
        <?php if ($reg_error): ?>
            <div class="error"><?php echo $reg_error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-grid">
                <div>
                    <label>Full Name *</label>
                    <input type="text" name="fullname" required>
                </div>
                <div>
                    <label>Email Address *</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Organization *</label>
                    <input type="text" name="organization" required>
                </div>
                <div>
                    <label>Scenario Code</label>
                    <input type="text" name="scenario" placeholder="e.g. APCex, GOA">
                </div>
                <div>
                    <label>Password *</label>
                    <input type="password" name="password" required>
                    <small>Minimum 6 characters</small>
                </div>
                <div>
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            
            <button type="submit" name="register_submit">Submit Registration</button>
        </form>
        
        <div class="links">
            Already have an account? <a href="<?php echo site_url('/login'); ?>">Sign in here</a>
        </div>
    </div>
</div>


