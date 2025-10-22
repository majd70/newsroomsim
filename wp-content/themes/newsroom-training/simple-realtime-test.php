<!DOCTYPE html>
<html>
<head>
    <title>Simple Real-time Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .log { border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #f9f9f9; }
        .error { border-color: red; background: #ffe8e8; }
        .success { border-color: green; background: #e8f5e8; }
    </style>
</head>
<body>
    <h1>🧪 Simple Real-time Test</h1>
    <p>This page tests if the real-time system is working without any WordPress dependencies.</p>
    
    <div id="logs"></div>
    
    <script>
        function addLog(message, type = 'info') {
            const logsDiv = document.getElementById('logs');
            const logDiv = document.createElement('div');
            logDiv.className = `log ${type}`;
            logDiv.innerHTML = `<strong>${new Date().toLocaleTimeString()}</strong>: ${message}`;
            logsDiv.insertBefore(logDiv, logsDiv.firstChild);
            
            // Keep only last 20 logs
            while (logsDiv.children.length > 20) {
                logsDiv.removeChild(logsDiv.lastChild);
            }
        }
        
        addLog('🚀 Test started');
        
        // Test AJAX endpoint directly
        function testAjax() {
            addLog('📡 Testing AJAX endpoint...');
            
            const formData = new FormData();
            formData.append('action', 'get_new_posts');
            formData.append('nonce', 'test'); // This will fail, but we can see if endpoint exists
            formData.append('since_timestamp', 'SERVER_TIME_MINUS_10');
            
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                addLog(`📡 Response status: ${response.status}`, response.ok ? 'success' : 'error');
                return response.text();
            })
            .then(data => {
                addLog(`📡 Response data: ${data.substring(0, 100)}...`);
                
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.success === false && jsonData.data === 'Security check failed') {
                        addLog('✅ AJAX endpoint exists and security is working', 'success');
                    } else {
                        addLog('📡 Unexpected response format', 'error');
                    }
                } catch (e) {
                    addLog('❌ Response is not JSON', 'error');
                }
            })
            .catch(error => {
                addLog(`❌ AJAX error: ${error.message}`, 'error');
            });
        }
        
        // Test if newsroom_ajax is available
        function testNewsroomAjax() {
            addLog('🔍 Testing newsroom_ajax availability...');
            
            if (typeof window.newsroom_ajax !== 'undefined') {
                addLog('✅ newsroom_ajax is available', 'success');
                addLog(`📡 AJAX URL: ${window.newsroom_ajax.ajax_url}`);
                addLog(`🔑 Nonce available: ${!!window.newsroom_ajax.nonce}`);
                addLog(`🕐 WP Time: ${window.newsroom_ajax.wp_current_time}`);
                addLog(`🌍 Timezone: ${window.newsroom_ajax.wp_timezone_offset}`);
            } else {
                addLog('❌ newsroom_ajax is NOT available', 'error');
            }
        }
        
        // Test if real-time class is available
        function testRealtimeClass() {
            addLog('🔍 Testing real-time class availability...');
            
            if (typeof window.SimpleRealtimeUpdates !== 'undefined') {
                addLog('✅ SimpleRealtimeUpdates class is available', 'success');
            } else {
                addLog('❌ SimpleRealtimeUpdates class is NOT available', 'error');
            }
            
            if (typeof window.simpleRealtimeUpdates !== 'undefined') {
                addLog('✅ simpleRealtimeUpdates instance is available', 'success');
                addLog(`🔄 Is polling: ${window.simpleRealtimeUpdates.isPolling}`);
            } else {
                addLog('❌ simpleRealtimeUpdates instance is NOT available', 'error');
            }
        }
        
        // Test container detection
        function testContainer() {
            addLog('🔍 Testing container detection...');
            
            const selectors = [
                '.col-lg-8.mx-auto',
                '.col-lg-8',
                '.newsroom-frontend',
                '.container'
            ];
            
            let found = false;
            selectors.forEach(selector => {
                const element = document.querySelector(selector);
                if (element) {
                    addLog(`✅ Found container: ${selector}`, 'success');
                    found = true;
                } else {
                    addLog(`❌ Not found: ${selector}`);
                }
            });
            
            if (!found) {
                addLog('❌ No suitable container found', 'error');
            }
        }
        
        // Run tests
        setTimeout(() => {
            testNewsroomAjax();
            testRealtimeClass();
            testContainer();
            testAjax();
        }, 1000);
        
        // Monitor console for real-time messages
        const originalLog = console.log;
        console.log = function(...args) {
            originalLog.apply(console, args);
            
            const message = args.join(' ');
            if (message.includes('🚀') || message.includes('📡') || message.includes('🔍') || message.includes('✅') || message.includes('❌')) {
                addLog(`Console: ${message}`);
            }
        };
        
        addLog('✅ Test setup complete');
    </script>
</body>
</html>
