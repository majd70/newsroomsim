<?php
/**
 * Complete Debug Information
 * Visit: your-site.com/wp-content/themes/newsroom-training/full-debug.php
 */

// Load WordPress
require_once('../../../wp-load.php');

$nonce = wp_create_nonce('newsroom_nonce');
$ajax_url = admin_url('admin-ajax.php');

// Get current time info
$current_wp_time = current_time('Y-m-d H:i:s');
$current_mysql_time = current_time('mysql');
$current_timestamp = current_time('timestamp');
$current_utc = gmdate('Y-m-d H:i:s');

// Get recent posts
$recent_posts = get_posts(array(
    'post_type' => array('news_article', 'social_post'),
    'post_status' => 'publish',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC'
));

// Get recent comments
$recent_comments = get_comments(array(
    'status' => 'approve',
    'number' => 10,
    'orderby' => 'comment_date',
    'order' => 'DESC'
));

// Test timestamp from 1 hour ago
$test_timestamp = date('Y-m-d H:i:s', strtotime('-1 hour'));

// Test the actual AJAX endpoints
function test_ajax_endpoint($action, $timestamp, $nonce) {
    $_POST['action'] = $action;
    $_POST['nonce'] = $nonce;
    $_POST['since_timestamp'] = $timestamp;
    
    ob_start();
    
    if ($action === 'get_new_posts') {
        do_action('wp_ajax_get_new_posts');
    } elseif ($action === 'get_new_comments') {
        do_action('wp_ajax_get_new_comments');
    }
    
    $output = ob_get_clean();
    return $output;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Debug Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
        .debug-section h3 { margin-top: 0; color: #333; }
        button { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        #results { background: #f5f5f5; padding: 10px; margin: 10px 0; max-height: 500px; overflow-y: auto; border: 1px solid #ddd; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #eee; padding: 10px; overflow-x: auto; font-size: 11px; border: 1px solid #ccc; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .copy-btn { background: #28a745; font-size: 12px; padding: 5px 10px; }
        .highlight { background-color: #ffff99; }
    </style>
</head>
<body>
    <h1>🔍 Complete Debug Information</h1>
    
    <button onclick="copyAllDebugInfo()" class="copy-btn">📋 Copy All Debug Info</button>
    <button onclick="runLiveTests()">🧪 Run Live Tests</button>
    <button onclick="testTimestampFormats()">⏰ Test Timestamp Formats</button>
    
    <div id="debug-output">
        
        <div class="debug-section">
            <h3>🕐 Time & Timestamp Information</h3>
            <table>
                <tr><th>Type</th><th>Value</th><th>Format</th></tr>
                <tr><td>WordPress current_time('Y-m-d H:i:s')</td><td><?php echo $current_wp_time; ?></td><td>MySQL DateTime</td></tr>
                <tr><td>WordPress current_time('mysql')</td><td><?php echo $current_mysql_time; ?></td><td>MySQL DateTime</td></tr>
                <tr><td>WordPress current_time('timestamp')</td><td><?php echo $current_timestamp; ?></td><td>Unix Timestamp</td></tr>
                <tr><td>UTC Time</td><td><?php echo $current_utc; ?></td><td>UTC DateTime</td></tr>
                <tr><td>PHP date('Y-m-d H:i:s')</td><td><?php echo date('Y-m-d H:i:s'); ?></td><td>Server DateTime</td></tr>
                <tr><td>Test Timestamp (1 hour ago)</td><td><?php echo $test_timestamp; ?></td><td>MySQL DateTime</td></tr>
            </table>
        </div>

        <div class="debug-section">
            <h3>⚙️ System Configuration</h3>
            <table>
                <tr><th>Setting</th><th>Value</th></tr>
                <tr><td>AJAX URL</td><td><?php echo $ajax_url; ?></td></tr>
                <tr><td>Nonce</td><td><?php echo $nonce; ?></td></tr>
                <tr><td>Current User</td><td><?php echo is_user_logged_in() ? wp_get_current_user()->display_name . ' (ID: ' . get_current_user_id() . ')' : 'Not logged in'; ?></td></tr>
                <tr><td>WordPress Timezone</td><td><?php echo get_option('timezone_string') ?: get_option('gmt_offset'); ?></td></tr>
                <tr><td>Server Timezone</td><td><?php echo date_default_timezone_get(); ?></td></tr>
                <tr><td>WordPress Version</td><td><?php echo get_bloginfo('version'); ?></td></tr>
            </table>
        </div>

        <div class="debug-section">
            <h3>📝 Recent Posts (Last 10)</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Date (post_date)</th>
                    <th>Date GMT (post_date_gmt)</th>
                    <th>Status</th>
                    <th>Author</th>
                </tr>
                <?php foreach ($recent_posts as $post): ?>
                <tr>
                    <td><?php echo $post->ID; ?></td>
                    <td><?php echo $post->post_type; ?></td>
                    <td><?php echo esc_html(substr($post->post_title, 0, 30)) . '...'; ?></td>
                    <td class="highlight"><?php echo $post->post_date; ?></td>
                    <td><?php echo $post->post_date_gmt; ?></td>
                    <td><?php echo $post->post_status; ?></td>
                    <td><?php echo get_userdata($post->post_author)->display_name ?? 'Unknown'; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="debug-section">
            <h3>💬 Recent Comments (Last 10)</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Post ID</th>
                    <th>Author</th>
                    <th>Content</th>
                    <th>Date (comment_date)</th>
                    <th>Date GMT (comment_date_gmt)</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($recent_comments as $comment): ?>
                <tr>
                    <td><?php echo $comment->comment_ID; ?></td>
                    <td><?php echo $comment->comment_post_ID; ?></td>
                    <td><?php echo esc_html($comment->comment_author); ?></td>
                    <td><?php echo esc_html(substr($comment->comment_content, 0, 30)) . '...'; ?></td>
                    <td class="highlight"><?php echo $comment->comment_date; ?></td>
                    <td><?php echo $comment->comment_date_gmt; ?></td>
                    <td><?php echo $comment->comment_approved; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="debug-section">
            <h3>🧪 AJAX Endpoint Tests</h3>
            <h4>Test 1: Get New Posts (1 hour ago timestamp)</h4>
            <pre><?php 
                $posts_result = test_ajax_endpoint('get_new_posts', $test_timestamp, $nonce);
                echo esc_html($posts_result);
            ?></pre>
            
            <h4>Test 2: Get New Comments (1 hour ago timestamp)</h4>
            <pre><?php 
                $comments_result = test_ajax_endpoint('get_new_comments', $test_timestamp, $nonce);
                echo esc_html($comments_result);
            ?></pre>
            
            <h4>Test 3: Get New Posts (current timestamp - should find nothing)</h4>
            <pre><?php 
                $posts_result_current = test_ajax_endpoint('get_new_posts', $current_wp_time, $nonce);
                echo esc_html($posts_result_current);
            ?></pre>
        </div>

        <div class="debug-section">
            <h3>🔍 JavaScript System Check</h3>
            <button onclick="checkJavaScriptSystem()">Check JS System</button>
            <button onclick="forceUpdateCheck()">Force Update Check</button>
            <button onclick="showCurrentTimestamps()">Show Current JS Timestamps</button>
        </div>

    </div>

    <div id="results">
        <h4>Live Test Results:</h4>
    </div>

    <script>
        const ajaxUrl = '<?php echo $ajax_url; ?>';
        const nonce = '<?php echo $nonce; ?>';
        
        function log(message, type = 'info') {
            const results = document.getElementById('results');
            const color = type === 'success' ? 'green' : type === 'error' ? 'red' : type === 'warning' ? 'orange' : 'black';
            results.innerHTML += `<div style="color: ${color};">${new Date().toLocaleTimeString()}: ${message}</div>`;
            console.log(message);
        }

        function copyAllDebugInfo() {
            const debugOutput = document.getElementById('debug-output').innerText;
            const resultsOutput = document.getElementById('results').innerText;
            const fullDebug = `=== FULL DEBUG INFORMATION ===\n\n${debugOutput}\n\n=== LIVE TEST RESULTS ===\n\n${resultsOutput}`;
            
            navigator.clipboard.writeText(fullDebug).then(() => {
                alert('✅ Debug information copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = fullDebug;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('✅ Debug information copied to clipboard!');
            });
        }

        function checkJavaScriptSystem() {
            log('🔍 Checking JavaScript system...');
            
            if (window.simpleRealtimeUpdates) {
                log('✅ Real-time system found', 'success');
                
                // Get system status
                try {
                    const lastPostCheck = window.simpleRealtimeUpdates.lastPostCheck;
                    const lastCommentCheck = window.simpleRealtimeUpdates.lastCommentCheck;
                    const isPolling = window.simpleRealtimeUpdates.isPolling;
                    
                    log(`📊 Last post check: ${lastPostCheck}`);
                    log(`📊 Last comment check: ${lastCommentCheck}`);
                    log(`📊 Is polling: ${isPolling}`);
                    
                } catch (error) {
                    log('❌ Error getting system status: ' + error, 'error');
                }
            } else {
                log('❌ Real-time system not found', 'error');
                log('🔍 Available window objects: ' + Object.keys(window).filter(k => k.includes('realtime') || k.includes('update')).join(', '));
            }
        }

        function forceUpdateCheck() {
            log('🔄 Forcing update check...');
            
            if (window.simpleRealtimeUpdates) {
                try {
                    window.simpleRealtimeUpdates.checkForNewPosts();
                    window.simpleRealtimeUpdates.checkForNewComments();
                    log('✅ Update check triggered', 'success');
                } catch (error) {
                    log('❌ Error forcing update: ' + error, 'error');
                }
            } else {
                log('❌ Real-time system not available', 'error');
            }
        }

        function showCurrentTimestamps() {
            log('⏰ Current JavaScript timestamps...');
            
            const now = new Date();
            const jsTimestamp = now.getFullYear() + '-' + 
                               String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                               String(now.getDate()).padStart(2, '0') + ' ' + 
                               String(now.getHours()).padStart(2, '0') + ':' + 
                               String(now.getMinutes()).padStart(2, '0') + ':' + 
                               String(now.getSeconds()).padStart(2, '0');
            
            log(`📅 JavaScript formatted timestamp: ${jsTimestamp}`);
            log(`📅 JavaScript Date object: ${now.toString()}`);
            log(`📅 JavaScript ISO string: ${now.toISOString()}`);
            log(`📅 JavaScript timestamp: ${now.getTime()}`);
            
            if (window.simpleRealtimeUpdates) {
                log(`📅 System last post check: ${window.simpleRealtimeUpdates.lastPostCheck}`);
                log(`📅 System last comment check: ${window.simpleRealtimeUpdates.lastCommentCheck}`);
            }
        }

        async function runLiveTests() {
            log('🧪 Running live AJAX tests...');
            
            // Test with old timestamp
            const oldTimestamp = '<?php echo $test_timestamp; ?>';
            log(`📅 Testing with timestamp: ${oldTimestamp}`);
            
            await testEndpoint('get_new_posts', oldTimestamp);
            await testEndpoint('get_new_comments', oldTimestamp);
        }

        async function testEndpoint(action, timestamp) {
            log(`📡 Testing ${action} endpoint...`);
            
            try {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('nonce', nonce);
                formData.append('since_timestamp', timestamp);
                
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                log(`📡 Response status: ${response.status}`);
                
                const data = await response.json();
                log(`📡 Response data: ${JSON.stringify(data, null, 2)}`);
                
                if (data.success) {
                    if (action === 'get_new_posts') {
                        log(`📝 Posts found: ${data.data.posts_count}`, data.data.posts_count > 0 ? 'success' : 'warning');
                        if (data.data.posts_html) {
                            log(`📝 HTML length: ${data.data.posts_html.length} characters`);
                        }
                    } else {
                        log(`💬 Comments found: ${data.data.comments_count}`, data.data.comments_count > 0 ? 'success' : 'warning');
                    }
                } else {
                    log(`❌ ${action} failed: ${data.data}`, 'error');
                }
                
            } catch (error) {
                log(`❌ ${action} error: ${error}`, 'error');
            }
        }

        function testTimestampFormats() {
            log('⏰ Testing different timestamp formats...');
            
            const now = new Date();
            
            // Test different formats
            const formats = [
                now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0') + ' ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0'),
                now.toISOString().slice(0, 19).replace('T', ' '),
                '<?php echo $current_wp_time; ?>',
                '<?php echo $test_timestamp; ?>'
            ];
            
            formats.forEach((format, index) => {
                log(`📅 Format ${index + 1}: ${format}`);
            });
        }

        // Auto-run basic checks
        setTimeout(() => {
            log('🚀 Debug page loaded');
            checkJavaScriptSystem();
            showCurrentTimestamps();
        }, 1000);
    </script>
</body>
</html>
