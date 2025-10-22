<?php
/**
 * Simple Real-time Test
 * Visit: your-site.com/wp-content/themes/newsroom-training/test-realtime-simple.php
 */

// Load WordPress
require_once('../../../wp-load.php');

$nonce = wp_create_nonce('newsroom_nonce');
$ajax_url = admin_url('admin-ajax.php');

// Get the most recent post for testing
$recent_post = get_posts(array(
    'post_type' => array('news_article', 'social_post'),
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC'
));

$test_post_id = $recent_post ? $recent_post[0]->ID : 1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Real-time Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
        button { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        #console { background: #000; color: #0f0; padding: 10px; margin: 10px 0; height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0ff; }
    </style>
</head>
<body>
    <h1>🧪 Simple Real-time Test</h1>
    
    <div class="test-section">
        <h3>Test Controls</h3>
        <button onclick="startTest()">🚀 Start Real-time Test</button>
        <button onclick="stopTest()">⏹️ Stop Test</button>
        <button onclick="refreshTimestamps()">🔄 Refresh Timestamps</button>
        <button onclick="createTestPost()">📝 Create Test Post</button>
        <button onclick="createTestComment()">💬 Create Test Comment</button>
        <button onclick="clearConsole()">🧹 Clear Console</button>
    </div>

    <div class="test-section">
        <h3>System Status</h3>
        <p><strong>Test Post ID:</strong> <?php echo $test_post_id; ?></p>
        <p><strong>Current Time:</strong> <span id="current-time"><?php echo current_time('Y-m-d H:i:s'); ?></span></p>
        <p><strong>Real-time System:</strong> <span id="system-status">Checking...</span></p>
        <p><strong>Polling Status:</strong> <span id="polling-status">Stopped</span></p>
    </div>

    <div class="test-section">
        <h3>Console Output</h3>
        <div id="console"></div>
    </div>

    <script>
        const ajaxUrl = '<?php echo $ajax_url; ?>';
        const nonce = '<?php echo $nonce; ?>';
        const testPostId = <?php echo $test_post_id; ?>;
        
        let testInterval = null;
        let testCounter = 0;
        
        function log(message, type = 'info') {
            const console = document.getElementById('console');
            const timestamp = new Date().toLocaleTimeString();
            const colorClass = type;
            console.innerHTML += `<div class="${colorClass}">[${timestamp}] ${message}</div>`;
            console.scrollTop = console.scrollHeight;
            
            // Also log to browser console
            window.console.log(`[${timestamp}] ${message}`);
        }

        function clearConsole() {
            document.getElementById('console').innerHTML = '';
        }

        function updateStatus() {
            // Update current time
            document.getElementById('current-time').textContent = new Date().toLocaleString();
            
            // Check system status
            if (window.simpleRealtimeUpdates) {
                document.getElementById('system-status').textContent = '✅ Active';
                document.getElementById('system-status').style.color = 'green';
                
                const isPolling = window.simpleRealtimeUpdates.isPolling;
                document.getElementById('polling-status').textContent = isPolling ? '🔄 Polling' : '⏸️ Paused';
                document.getElementById('polling-status').style.color = isPolling ? 'green' : 'orange';
            } else {
                document.getElementById('system-status').textContent = '❌ Not Found';
                document.getElementById('system-status').style.color = 'red';
                document.getElementById('polling-status').textContent = '❌ Unavailable';
                document.getElementById('polling-status').style.color = 'red';
            }
        }

        function startTest() {
            log('🚀 Starting real-time test...', 'info');
            
            if (!window.simpleRealtimeUpdates) {
                log('❌ Real-time system not found!', 'error');
                return;
            }
            
            // Force start polling
            window.simpleRealtimeUpdates.startPolling();
            log('✅ Polling started', 'success');
            
            // Start test monitoring
            testCounter = 0;
            testInterval = setInterval(() => {
                testCounter++;
                log(`🔍 Test cycle ${testCounter} - Monitoring for updates...`, 'info');
                
                // Force a check
                window.simpleRealtimeUpdates.checkForNewPosts();
                window.simpleRealtimeUpdates.checkForNewComments();
                
                // Show current timestamps
                const status = window.simpleRealtimeUpdates.getStatus();
                log(`📊 Last post check: ${status.lastPostCheck}`, 'info');
                log(`📊 Last comment check: ${status.lastCommentCheck}`, 'info');
                
            }, 10000); // Every 10 seconds
            
            log('✅ Test monitoring started (every 10 seconds)', 'success');
        }

        function stopTest() {
            log('⏹️ Stopping test...', 'warning');

            if (testInterval) {
                clearInterval(testInterval);
                testInterval = null;
                log('✅ Test monitoring stopped', 'success');
            }

            if (window.simpleRealtimeUpdates) {
                window.simpleRealtimeUpdates.stopPolling();
                log('✅ Polling stopped', 'success');
            }
        }

        function refreshTimestamps() {
            log('🔄 Refreshing timestamps...', 'info');

            if (window.simpleRealtimeUpdates) {
                window.simpleRealtimeUpdates.refreshTimestamps();
                log('✅ Timestamps refreshed', 'success');

                const status = window.simpleRealtimeUpdates.getStatus();
                log(`📊 New post check timestamp: ${status.lastPostCheck}`, 'info');
                log(`📊 New comment check timestamp: ${status.lastCommentCheck}`, 'info');
            } else {
                log('❌ Real-time system not available', 'error');
            }
        }

        async function createTestPost() {
            log('📝 Creating test post...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'create_test_post');
                formData.append('nonce', nonce);
                
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Test post created with ID: ${data.data.post_id}`, 'success');
                    log('🔍 Watch for it to appear in real-time...', 'info');
                } else {
                    log(`❌ Failed to create test post: ${data.data}`, 'error');
                }
                
            } catch (error) {
                log(`❌ Error creating test post: ${error}`, 'error');
            }
        }

        async function createTestComment() {
            log('💬 Creating test comment...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'add_comment_inline');
                formData.append('nonce', nonce);
                formData.append('post_id', testPostId);
                formData.append('comment_content', `Test comment created at ${new Date().toLocaleTimeString()}`);
                
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Test comment created with ID: ${data.data.comment_id}`, 'success');
                    log('🔍 Watch for it to appear in real-time...', 'info');
                } else {
                    log(`❌ Failed to create test comment: ${data.data}`, 'error');
                }
                
            } catch (error) {
                log(`❌ Error creating test comment: ${error}`, 'error');
            }
        }

        // Initialize
        setTimeout(() => {
            log('🎯 Simple Real-time Test initialized', 'success');
            log(`📡 AJAX URL: ${ajaxUrl}`, 'info');
            log(`🔑 Nonce: ${nonce}`, 'info');
            log(`📝 Test Post ID: ${testPostId}`, 'info');
            
            updateStatus();
            
            // Update status every 2 seconds
            setInterval(updateStatus, 2000);
            
        }, 1000);
    </script>
</body>
</html>
