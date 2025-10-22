<?php
/**
 * Test Edit Live Updates
 * Visit: your-site.com/wp-content/themes/newsroom-training/test-edit-live-updates.php
 */

// Load WordPress
require_once('../../../wp-load.php');

$nonce = wp_create_nonce('newsroom_nonce');
$nr_nonce = wp_create_nonce('nr_ajax_nonce');
$ajax_url = admin_url('admin-ajax.php');

// Get a recent post for testing
$test_post = get_posts(array(
    'post_type' => array('news_article', 'social_post'),
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC'
))[0] ?? null;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Edit Live Updates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        button { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        #results { background: #f5f5f5; padding: 10px; margin: 10px 0; max-height: 400px; overflow-y: auto; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #eee; padding: 10px; overflow-x: auto; font-size: 12px; }
        .step { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #007cba; }
    </style>
</head>
<body>
    <h1>Test Edit Live Updates</h1>
    
    <?php if ($test_post): ?>
    <div class="test-section">
        <h2>Test Post Information</h2>
        <p><strong>Post ID:</strong> <?php echo $test_post->ID; ?></p>
        <p><strong>Title:</strong> <?php echo esc_html($test_post->post_title); ?></p>
        <p><strong>Type:</strong> <?php echo $test_post->post_type; ?></p>
        <p><strong>Content Preview:</strong> <?php echo esc_html(substr(strip_tags($test_post->post_content), 0, 100)) . '...'; ?></p>
    </div>

    <div class="test-section">
        <h2>Live Update Test Steps</h2>
        
        <div class="step">
            <h3>Step 1: Check Current Edit Operations</h3>
            <button onclick="checkEditOperations()">Check for Edit Operations</button>
            <p>This will check if there are any pending edit operations in the system.</p>
        </div>

        <div class="step">
            <h3>Step 2: Simulate Post Edit</h3>
            <button onclick="simulateEdit()">Simulate Edit</button>
            <p>This will update the test post and create an edit transient.</p>
        </div>

        <div class="step">
            <h3>Step 3: Check Edit Operations Again</h3>
            <button onclick="checkEditOperations()">Check for Edit Operations</button>
            <p>This should now show the edit operation we just created.</p>
        </div>

        <div class="step">
            <h3>Step 4: Test Get Updated Post</h3>
            <button onclick="getUpdatedPost()">Get Updated Post HTML</button>
            <p>This will fetch the updated post HTML that would be used to refresh the post.</p>
        </div>
    </div>
    <?php else: ?>
    <div class="test-section">
        <h2>No Test Post Available</h2>
        <p>Please create at least one news article or social post to test with.</p>
    </div>
    <?php endif; ?>

    <div class="test-section">
        <h2>Test Results</h2>
        <div id="results"></div>
    </div>

    <script>
        const ajaxUrl = '<?php echo $ajax_url; ?>';
        const nonce = '<?php echo $nonce; ?>';
        const nrNonce = '<?php echo $nr_nonce; ?>';
        const testPostId = <?php echo $test_post ? $test_post->ID : 'null'; ?>;

        function log(message, type = 'info') {
            const results = document.getElementById('results');
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
            results.innerHTML += `<div class="${className}">[${timestamp}] ${message}</div>`;
            results.scrollTop = results.scrollHeight;
        }

        async function checkEditOperations() {
            log('🔍 Checking for edit operations...');

            try {
                const formData = new FormData();
                formData.append('action', 'get_edit_operations');
                formData.append('nonce', nonce);
                formData.append('since_timestamp', 'SERVER_TIME_MINUS_60'); // Check last hour

                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                log('📡 Edit operations response: ' + JSON.stringify(data, null, 2));

                if (data.success) {
                    const editOps = data.data.operations || [];
                    log(`✅ Found ${editOps.length} edit operations`, 'success');
                    if (editOps.length > 0) {
                        editOps.forEach(op => {
                            log(`📝 Edit: Post ${op.post_id} (${op.post_type}) at ${op.timestamp}`, 'info');
                        });
                    }
                } else {
                    log('❌ Failed to check edit operations: ' + data.data, 'error');
                }
            } catch (error) {
                log('❌ Error checking edit operations: ' + error, 'error');
            }
        }

        async function simulateEdit() {
            if (!testPostId) {
                log('❌ No test post available', 'error');
                return;
            }

            log('🔄 Simulating post edit...');
            
            try {
                const formData = new FormData();
                formData.append('action', 'nr_edit_post');
                formData.append('_wpnonce', nrNonce);
                formData.append('id', testPostId);
                formData.append('title', 'Updated Title - ' + new Date().toLocaleTimeString());
                formData.append('content', 'Updated content at ' + new Date().toLocaleString());

                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                log('📡 Edit response: ' + JSON.stringify(data, null, 2));

                if (data.success) {
                    log('✅ Post edited successfully!', 'success');
                    log('💡 Now check for edit operations to see if the transient was created.', 'info');
                } else {
                    log('❌ Failed to edit post: ' + (data.data || 'Unknown error'), 'error');
                }
            } catch (error) {
                log('❌ Error simulating edit: ' + error, 'error');
            }
        }

        async function getUpdatedPost() {
            if (!testPostId) {
                log('❌ No test post available', 'error');
                return;
            }

            log('🔄 Getting updated post HTML...');
            
            try {
                const formData = new FormData();
                formData.append('action', 'get_updated_post');
                formData.append('nonce', nonce);
                formData.append('post_id', testPostId);

                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                log('📡 Updated post response: ' + JSON.stringify({...data, data: {...data.data, post_html: data.data?.post_html ? '[HTML CONTENT]' : null}}, null, 2));

                if (data.success && data.data.post_html) {
                    log('✅ Successfully fetched updated post HTML!', 'success');
                    log(`📝 HTML length: ${data.data.post_html.length} characters`, 'info');
                } else {
                    log('❌ Failed to get updated post: ' + (data.data || 'Unknown error'), 'error');
                }
            } catch (error) {
                log('❌ Error getting updated post: ' + error, 'error');
            }
        }

        // Initial check
        log('🚀 Edit Live Updates Test Ready');
        if (testPostId) {
            log(`📝 Test post ID: ${testPostId}`, 'info');
        }
    </script>
</body>
</html>
