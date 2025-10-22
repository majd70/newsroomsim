<?php
/**
 * Test AJAX endpoints directly
 * Visit: your-site.com/wp-content/themes/newsroom-training/test-endpoints.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Force user to be logged in for testing
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$nonce = wp_create_nonce('newsroom_nonce');
$ajax_url = admin_url('admin-ajax.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX Endpoints</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        button { padding: 10px 15px; margin: 5px; }
        #results { background: #f5f5f5; padding: 10px; margin: 10px 0; max-height: 400px; overflow-y: auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #eee; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>AJAX Endpoints Test</h1>
    
    <div class="test-section">
        <h3>1. Test Comment Submission</h3>
        <button onclick="testCommentSubmission()">Test Add Comment</button>
        <p><strong>Expected:</strong> Should return success with comment data</p>
    </div>

    <div class="test-section">
        <h3>2. Test Get New Posts</h3>
        <button onclick="testGetNewPosts()">Test Get New Posts</button>
        <p><strong>Expected:</strong> Should return posts data (may be empty)</p>
    </div>

    <div class="test-section">
        <h3>3. Test Get New Comments</h3>
        <button onclick="testGetNewComments()">Test Get New Comments</button>
        <p><strong>Expected:</strong> Should return comments data (may be empty)</p>
    </div>

    <div id="results">
        <h4>Test Results:</h4>
    </div>

    <script>
        const ajaxUrl = '<?php echo $ajax_url; ?>';
        const nonce = '<?php echo $nonce; ?>';
        
        function log(message, type = 'info') {
            const results = document.getElementById('results');
            const color = type === 'success' ? 'green' : type === 'error' ? 'red' : 'black';
            results.innerHTML += `<div style="color: ${color};">${new Date().toLocaleTimeString()}: ${message}</div>`;
            console.log(message);
        }

        async function testCommentSubmission() {
            log('🧪 Testing comment submission...');
            
            const formData = new FormData();
            formData.append('action', 'add_comment_inline');
            formData.append('nonce', nonce);
            formData.append('post_id', '1');
            formData.append('comment_content', 'Test comment from endpoint test');
            
            try {
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                log('📡 Response status: ' + response.status);
                
                const data = await response.json();
                log('📡 Response data:');
                log('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
                
                if (data.success) {
                    log('✅ Comment submission test PASSED', 'success');
                    
                    // Check if we have the expected data structure
                    if (data.data.author_name && data.data.content && data.data.comment_id) {
                        log('✅ Comment data structure is correct', 'success');
                    } else {
                        log('❌ Comment data structure is incomplete', 'error');
                        log('Missing fields: ' + JSON.stringify({
                            has_author_name: !!data.data.author_name,
                            has_content: !!data.data.content,
                            has_comment_id: !!data.data.comment_id
                        }));
                    }
                } else {
                    log('❌ Comment submission test FAILED: ' + data.data, 'error');
                }
            } catch (error) {
                log('❌ Comment submission test ERROR: ' + error, 'error');
            }
        }

        async function testGetNewPosts() {
            log('🧪 Testing get new posts...');
            
            const testTimestamp = '2024-10-18 19:00:00';
            
            const formData = new FormData();
            formData.append('action', 'get_new_posts');
            formData.append('nonce', nonce);
            formData.append('since_timestamp', testTimestamp);
            
            try {
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                log('📡 Response status: ' + response.status);
                
                const data = await response.json();
                log('📡 Response data:');
                log('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
                
                if (data.success) {
                    log('✅ Get new posts test PASSED', 'success');
                    log(`📝 Found ${data.data.posts_count} posts`);
                    
                    if (data.data.posts_html) {
                        log(`📝 Posts HTML length: ${data.data.posts_html.length} characters`);
                    }
                } else {
                    log('❌ Get new posts test FAILED: ' + data.data, 'error');
                }
            } catch (error) {
                log('❌ Get new posts test ERROR: ' + error, 'error');
            }
        }

        async function testGetNewComments() {
            log('🧪 Testing get new comments...');
            
            const testTimestamp = '2024-10-18 19:00:00';
            
            const formData = new FormData();
            formData.append('action', 'get_new_comments');
            formData.append('nonce', nonce);
            formData.append('since_timestamp', testTimestamp);
            
            try {
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                log('📡 Response status: ' + response.status);
                
                const data = await response.json();
                log('📡 Response data:');
                log('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
                
                if (data.success) {
                    log('✅ Get new comments test PASSED', 'success');
                    log(`💬 Found ${data.data.comments_count} comments`);
                    
                    if (data.data.comments_by_post) {
                        const postIds = Object.keys(data.data.comments_by_post);
                        log(`💬 Comments found for posts: ${postIds.join(', ')}`);
                    }
                } else {
                    log('❌ Get new comments test FAILED: ' + data.data, 'error');
                }
            } catch (error) {
                log('❌ Get new comments test ERROR: ' + error, 'error');
            }
        }

        // Auto-run basic checks
        setTimeout(() => {
            log('🚀 Starting endpoint tests...');
            log('📡 AJAX URL: ' + ajaxUrl);
            log('🔑 Nonce: ' + nonce);
            log('👤 User: <?php echo wp_get_current_user()->display_name; ?>');
        }, 500);
    </script>
</body>
</html>
