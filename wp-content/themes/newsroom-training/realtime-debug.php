<!DOCTYPE html>
<html>
<head>
    <title>Real-time Debug Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-box { border: 2px solid #333; padding: 15px; margin: 10px 0; background: #f9f9f9; }
        .success { border-color: green; background: #e8f5e8; }
        .error { border-color: red; background: #ffe8e8; }
        .info { border-color: blue; background: #e8f0ff; }
        .timestamp { color: #666; font-size: 12px; }
        .html-preview { max-height: 200px; overflow-y: auto; background: white; padding: 10px; border: 1px solid #ccc; }
        .clear-btn { background: red; color: white; padding: 10px; border: none; cursor: pointer; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔍 Real-time System Debug Monitor</h1>
    <p>This page monitors exactly what the real-time system is doing...</p>
    
    <button class="clear-btn" onclick="clearLogs()">🗑️ Clear Logs</button>
    
    <div id="debug-logs"></div>

    <script>
        let logCount = 0;
        
        function addLog(type, title, content) {
            logCount++;
            const timestamp = new Date().toLocaleTimeString();
            const logsDiv = document.getElementById('debug-logs');
            
            const logDiv = document.createElement('div');
            logDiv.className = `debug-box ${type}`;
            logDiv.innerHTML = `
                <div class="timestamp">#${logCount} - ${timestamp}</div>
                <h3>${title}</h3>
                <div>${content}</div>
            `;
            
            logsDiv.insertBefore(logDiv, logsDiv.firstChild);
            
            // Keep only last 10 logs
            while (logsDiv.children.length > 10) {
                logsDiv.removeChild(logsDiv.lastChild);
            }
        }
        
        function clearLogs() {
            document.getElementById('debug-logs').innerHTML = '';
            logCount = 0;
        }
        
        // Override console.log to capture real-time system logs
        const originalLog = console.log;
        console.log = function(...args) {
            originalLog.apply(console, args);
            
            const message = args.join(' ');
            
            // Capture specific real-time messages
            if (message.includes('📡 Checking posts since:')) {
                addLog('info', '📡 Checking for Posts', `<strong>Timestamp:</strong> ${args[args.length - 1]}`);
            }
            else if (message.includes('📝 Found') && message.includes('new posts')) {
                const count = message.match(/\d+/)[0];
                addLog('success', '📝 Posts Found', `<strong>Count:</strong> ${count} new posts`);
            }
            else if (message.includes('📝 Posts HTML length:')) {
                const length = args[args.length - 1];
                addLog('info', '📝 HTML Length', `<strong>Length:</strong> ${length} characters`);
            }
            else if (message.includes('📝 Number of new posts to add:')) {
                const count = args[args.length - 1];
                addLog('info', '📝 Adding Posts', `<strong>Adding:</strong> ${count} posts to feed`);
            }
            else if (message.includes('📝 No new posts found')) {
                addLog('info', '📝 No Posts', 'No new posts found');
            }
            else if (message.includes('❌')) {
                addLog('error', '❌ Error', message);
            }
        };
        
        // Monitor fetch requests to capture AJAX responses
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).then(response => {
                if (args[0].includes('admin-ajax.php')) {
                    response.clone().json().then(data => {
                        if (data.success && data.data && data.data.posts_html !== undefined) {
                            const htmlLength = data.data.posts_html.length;
                            const postsCount = data.data.posts_count || 0;
                            
                            let htmlPreview = '';
                            if (htmlLength > 0) {
                                // Extract first 500 characters for preview
                                const preview = data.data.posts_html.substring(0, 500);
                                htmlPreview = `
                                    <div class="html-preview">
                                        <strong>HTML Preview (first 500 chars):</strong><br>
                                        <code>${preview.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code>
                                        ${htmlLength > 500 ? '...' : ''}
                                    </div>
                                `;
                            }
                            
                            addLog('success', '📡 AJAX Response', `
                                <strong>Posts Count:</strong> ${postsCount}<br>
                                <strong>HTML Length:</strong> ${htmlLength} characters<br>
                                <strong>Latest Timestamp:</strong> ${data.data.latest_timestamp || 'N/A'}
                                ${htmlPreview}
                            `);
                            
                            // Check for specific content
                            if (htmlLength > 0) {
                                const html = data.data.posts_html;
                                const hasUser = html.includes('User');
                                const hasUsername = html.includes('@username');
                                const hasSobhi = html.includes('sobhi');
                                const hasArabic = html.includes('هاي');
                                
                                addLog('info', '🔍 Content Analysis', `
                                    <strong>Contains "User":</strong> ${hasUser ? '❌ YES' : '✅ NO'}<br>
                                    <strong>Contains "@username":</strong> ${hasUsername ? '❌ YES' : '✅ NO'}<br>
                                    <strong>Contains "sobhi":</strong> ${hasSobhi ? '✅ YES' : '❌ NO'}<br>
                                    <strong>Contains Arabic "هاي":</strong> ${hasArabic ? '✅ YES' : '❌ NO'}
                                `);
                            }
                        }
                    }).catch(e => {
                        addLog('error', '❌ AJAX Parse Error', e.message);
                    });
                }
                return response;
            });
        };
        
        addLog('info', '🚀 Debug Monitor Started', 'Monitoring real-time system activity...');
    </script>
</body>
</html>
