<?php
/**
 * Debug real-time updates
 * Visit: your-site.com/wp-content/themes/newsroom-training/debug-realtime.php
 */

// Load WordPress
require_once('../../../wp-load.php');

$nonce = wp_create_nonce('newsroom_nonce');
$ajax_url = admin_url('admin-ajax.php');

// Get recent posts for testing
$recent_posts = get_posts(array(
    'post_type' => array('news_article', 'social_post'),
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
));

// Get recent comments for testing
$recent_comments = get_comments(array(
    'status' => 'approve',
    'number' => 5,
    'orderby' => 'comment_date',
    'order' => 'DESC'
));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Real-time Updates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        button { padding: 10px 15px; margin: 5px; }
        #results { background: #f5f5f5; padding: 10px; margin: 10px 0; max-height: 400px; overflow-y: auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #eee; padding: 10px; overflow-x: auto; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Real-time Updates Debug</h1>
    
    <div class="debug-section">
        <h3>Current System Status</h3>
        <p><strong>Current Time:</strong> <?php echo current_time('Y-m-d H:i:s'); ?></p>
        <p><strong>AJAX URL:</strong> <?php echo $ajax_url; ?></p>
        <p><strong>Nonce:</strong> <?php echo $nonce; ?></p>
        <p><strong>User:</strong> <?php echo is_user_logged_in() ? wp_get_current_user()->display_name : 'Not logged in'; ?></p>
        
        <button onclick="checkSystemStatus()">Check JavaScript System</button>
        <button onclick="testWithOldTimestamp()">Test with Old Timestamp</button>
        <button onclick="testWithRecentTimestamp()">Test with Recent Timestamp</button>
    </div>

    <div class="debug-section">
        <h3>Recent Posts (for reference)</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Title</th>
                <th>Date</th>
                <th>Timestamp</th>
            </tr>
            <?php foreach ($recent_posts as $post): ?>
            <tr>
                <td><?php echo $post->ID; ?></td>
                <td><?php echo $post->post_type; ?></td>
                <td><?php echo esc_html($post->post_title); ?></td>
                <td><?php echo $post->post_date; ?></td>
                <td><?php echo strtotime($post->post_date); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="debug-section">
        <h3>Recent Comments (for reference)</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Post ID</th>
                <th>Author</th>
                <th>Content</th>
                <th>Date</th>
                <th>Timestamp</th>
            </tr>
            <?php foreach ($recent_comments as $comment): ?>
            <tr>
                <td><?php echo $comment->comment_ID; ?></td>
                <td><?php echo $comment->comment_post_ID; ?></td>
                <td><?php echo esc_html($comment->comment_author); ?></td>
                <td><?php echo esc_html(substr($comment->comment_content, 0, 50)) . '...'; ?></td>
                <td><?php echo $comment->comment_date; ?></td>
                <td><?php echo strtotime($comment->comment_date); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="results">
        <h4>Debug Results:</h4>
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

        function checkSystemStatus() {
            log('🔍 Checking system status...');
            
            if (window.simpleRealtimeUpdates) {
                log('✅ Real-time system found', 'success');
                const status = window.simpleRealtimeUpdates.getStatus();
                log('📊 System status: ' + JSON.stringify(status));
                
                // Force a check
                log('🔄 Forcing update check...');
                window.simpleRealtimeUpdates.forceCheck();
            } else {
                log('❌ Real-time system not found', 'error');
            }
        }

        async function testWithOldTimestamp() {
            log('🧪 Testing with old timestamp (should find posts)...');
            
            // Use a timestamp from 1 hour ago
            const oldTimestamp = new Date(Date.now() - 60 * 60 * 1000);
            const formattedTimestamp = oldTimestamp.getFullYear() + '-' + 
                                     String(oldTimestamp.getMonth() + 1).padStart(2, '0') + '-' + 
                                     String(oldTimestamp.getDate()).padStart(2, '0') + ' ' + 
                                     String(oldTimestamp.getHours()).padStart(2, '0') + ':' + 
                                     String(oldTimestamp.getMinutes()).padStart(2, '0') + ':' + 
                                     String(oldTimestamp.getSeconds()).padStart(2, '0');
            
            log('📅 Using timestamp: ' + formattedTimestamp);
            
            await testEndpoints(formattedTimestamp);
        }

        async function testWithRecentTimestamp() {
            log('🧪 Testing with recent timestamp (should find nothing)...');
            
            // Use current timestamp
            const now = new Date();
            const formattedTimestamp = now.getFullYear() + '-' + 
                                     String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                                     String(now.getDate()).padStart(2, '0') + ' ' + 
                                     String(now.getHours()).padStart(2, '0') + ':' + 
                                     String(now.getMinutes()).padStart(2, '0') + ':' + 
                                     String(now.getSeconds()).padStart(2, '0');
            
            log('📅 Using timestamp: ' + formattedTimestamp);
            
            await testEndpoints(formattedTimestamp);
        }

        async function testEndpoints(timestamp) {
            // Test posts endpoint
            log('📡 Testing posts endpoint...');
            try {
                const formData = new FormData();
                formData.append('action', 'get_new_posts');
                formData.append('nonce', nonce);
                formData.append('since_timestamp', timestamp);
                
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                log('📡 Posts response: ' + JSON.stringify(data, null, 2));
                
                if (data.success) {
                    log(`📝 Posts found: ${data.data.posts_count}`, 'success');
                    if (data.data.posts_html) {
                        log(`📝 HTML length: ${data.data.posts_html.length} characters`);
                    }
                } else {
                    log('❌ Posts endpoint failed: ' + data.data, 'error');
                }
            } catch (error) {
                log('❌ Posts endpoint error: ' + error, 'error');
            }

            // Test comments endpoint
            log('📡 Testing comments endpoint...');
            try {
                const formData = new FormData();
                formData.append('action', 'get_new_comments');
                formData.append('nonce', nonce);
                formData.append('since_timestamp', timestamp);
                
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                log('📡 Comments response: ' + JSON.stringify(data, null, 2));
                
                if (data.success) {
                    log(`💬 Comments found: ${data.data.comments_count}`, 'success');
                    if (data.data.comments_by_post) {
                        const postIds = Object.keys(data.data.comments_by_post);
                        log(`💬 Comments for posts: ${postIds.join(', ')}`);
                    }
                } else {
                    log('❌ Comments endpoint failed: ' + data.data, 'error');
                }
            } catch (error) {
                log('❌ Comments endpoint error: ' + error, 'error');
            }
        }

        // Auto-run basic checks
        setTimeout(() => {
            log('🚀 Debug page loaded');
            checkSystemStatus();
        }, 1000);
    </script>
</body>
</html>
