<?php
/**
 * Simple test page for the new real-time system
 * Visit: your-site.com/wp-content/themes/newsroom-training/test-simple.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Force user to be logged in for testing
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="alert alert-success">
                <h4>✅ New Simple Real-time System Test</h4>
                <p><strong>Changes made:</strong></p>
                <ul>
                    <li>❌ Removed problematic forms that caused page reloads</li>
                    <li>✅ Replaced with simple divs and buttons</li>
                    <li>✅ Clean AJAX-only approach</li>
                    <li>✅ No form submission conflicts</li>
                </ul>
            </div>

            <!-- Test the new comment system -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Test New Comment System</h5>
                </div>
                <div class="card-body">
                    <!-- This mimics a post with the new comment form -->
                    <div class="post-content mb-3">
                        <h6>Sample Post</h6>
                        <p>This is a test post to try the new comment system.</p>
                    </div>

                    <!-- Comments section -->
                    <div id="comments-list-1" class="comments-list mb-3">
                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                    </div>

                    <!-- New comment form (no form tag!) -->
                    <div class="add-comment-form bg-white p-3 rounded">
                        <h6 class="mb-2">Add a Comment</h6>
                        <div class="realtime-comment-form" data-post-id="1">
                            <div class="mb-3">
                                <textarea class="form-control comment-textarea"
                                          rows="3"
                                          placeholder="Write your comment here..."
                                          required></textarea>
                            </div>
                            <button type="button" class="btn btn-primary realtime-comment-btn">
                                <i class="fas fa-paper-plane me-1"></i> Post Comment
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test feed container for new posts -->
            <div class="card">
                <div class="card-header">
                    <h5>Feed Container (New posts will appear here)</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="alert alert-info">
                            <p><strong>Instructions:</strong></p>
                            <ol>
                                <li>Open browser console (F12)</li>
                                <li>Try posting a comment above</li>
                                <li>Open this page in another browser</li>
                                <li>Create a post in the admin panel</li>
                                <li>Watch for real-time updates</li>
                            </ol>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Debug info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Debug Information</h5>
                </div>
                <div class="card-body">
                    <div id="debug-info">
                        <p><strong>User:</strong> <?php echo wp_get_current_user()->display_name; ?></p>
                        <p><strong>AJAX URL:</strong> <?php echo admin_url('admin-ajax.php'); ?></p>
                        <p><strong>Nonce:</strong> <?php echo wp_create_nonce('newsroom_nonce'); ?></p>
                    </div>
                    
                    <button onclick="checkSystem()" class="btn btn-info">Check System Status</button>
                    <button onclick="forceUpdate()" class="btn btn-warning">Force Update Check</button>
                    
                    <div id="status-results" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkSystem() {
    const results = document.getElementById('status-results');
    results.innerHTML = '<h6>System Status:</h6>';
    
    // Check if new system is loaded
    if (window.simpleRealtimeUpdates) {
        results.innerHTML += '<p class="text-success">✅ Simple Real-time system loaded</p>';
        
        const status = window.simpleRealtimeUpdates.getStatus();
        results.innerHTML += '<pre>' + JSON.stringify(status, null, 2) + '</pre>';
    } else {
        results.innerHTML += '<p class="text-danger">❌ Simple Real-time system not loaded</p>';
    }
    
    // Check AJAX config
    if (window.newsroom_ajax) {
        results.innerHTML += '<p class="text-success">✅ AJAX configuration available</p>';
    } else {
        results.innerHTML += '<p class="text-danger">❌ AJAX configuration missing</p>';
    }
    
    // Check for comment forms
    const commentForms = document.querySelectorAll('.realtime-comment-form');
    results.innerHTML += `<p>📝 Found ${commentForms.length} comment forms</p>`;
    
    // Check for comment buttons
    const commentButtons = document.querySelectorAll('.realtime-comment-btn');
    results.innerHTML += `<p>🔘 Found ${commentButtons.length} comment buttons</p>`;
}

function forceUpdate() {
    if (window.simpleRealtimeUpdates) {
        window.simpleRealtimeUpdates.forceCheck();
        document.getElementById('status-results').innerHTML += '<p class="text-info">🔄 Force update triggered</p>';
    } else {
        document.getElementById('status-results').innerHTML += '<p class="text-danger">❌ System not available</p>';
    }
}

// Auto-check on load
setTimeout(checkSystem, 1000);

// Monitor console for messages
console.log('🧪 Simple test page loaded');
console.log('📝 Watch console for real-time system messages');
</script>

<?php get_footer(); ?>
