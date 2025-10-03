<?php
/**
 * Simple Debug Log Viewer
 * Access this file directly to see the WordPress debug log
 */

// Try to load WordPress config to check debug mode
if (file_exists(__DIR__ . '/wp-config.php')) {
    // Don't load full WordPress, just get the constants
    $config_content = file_get_contents(__DIR__ . '/wp-config.php');
    if (strpos($config_content, "define( 'WP_DEBUG', true )") === false &&
        strpos($config_content, "define('WP_DEBUG', true)") === false) {
        // Debug is not enabled, but we'll show the log anyway for troubleshooting
        echo '<div style="background: #fff3cd; color: #856404; padding: 15px; margin: 20px; border-radius: 5px;">';
        echo '<strong>⚠️ Warning:</strong> WP_DEBUG is not enabled in wp-config.php. ';
        echo 'Some logs may not be written. Consider enabling it for better debugging.';
        echo '</div>';
    }
}

$log_file = __DIR__ . '/wp-content/debug.log';

if (!file_exists($log_file)) {
    die('Debug log file not found at: ' . $log_file);
}

// Get the last 200 lines of the log
$lines = file($log_file);
$total_lines = count($lines);
$show_lines = 200;
$start = max(0, $total_lines - $show_lines);
$recent_lines = array_slice($lines, $start);

?>
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Debug Log</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        .info {
            background: #2d2d30;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4ec9b0;
        }
        .log-container {
            background: #252526;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 80vh;
            overflow-y: auto;
        }
        .log-line {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid transparent;
        }
        .log-line:hover {
            background: #2d2d30;
        }
        .error {
            color: #f48771;
            border-left-color: #f48771;
        }
        .success {
            color: #4ec9b0;
            border-left-color: #4ec9b0;
        }
        .warning {
            color: #dcdcaa;
            border-left-color: #dcdcaa;
        }
        .separator {
            color: #569cd6;
            border-left-color: #569cd6;
            font-weight: bold;
        }
        .refresh-btn {
            background: #0e639c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .refresh-btn:hover {
            background: #1177bb;
        }
        .clear-btn {
            background: #c5c5c5;
            color: #1e1e1e;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            margin-left: 10px;
        }
        .clear-btn:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <h1>🐛 WordPress Debug Log Viewer</h1>
    
    <div class="info">
        <strong>Log File:</strong> <?php echo $log_file; ?><br>
        <strong>Total Lines:</strong> <?php echo number_format($total_lines); ?><br>
        <strong>Showing:</strong> Last <?php echo count($recent_lines); ?> lines<br>
        <strong>Last Modified:</strong> <?php echo date('Y-m-d H:i:s', filemtime($log_file)); ?>
    </div>
    
    <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Log</button>
    <button class="clear-btn" onclick="if(confirm('Clear the debug log?')) { window.location.href='?clear=1'; }">🗑️ Clear Log</button>
    
    <div class="log-container">
        <?php
        if (isset($_GET['clear'])) {
            file_put_contents($log_file, '');
            echo '<div class="success">✅ Log cleared!</div>';
            echo '<script>setTimeout(function(){ location.href="view-debug-log.php"; }, 1000);</script>';
        } else {
            foreach ($recent_lines as $line) {
                $class = '';
                
                if (strpos($line, '❌') !== false || strpos($line, 'ERROR') !== false || strpos($line, 'FAILED') !== false) {
                    $class = 'error';
                } elseif (strpos($line, '✅') !== false || strpos($line, 'SUCCESS') !== false) {
                    $class = 'success';
                } elseif (strpos($line, '⚠️') !== false || strpos($line, 'WARNING') !== false) {
                    $class = 'warning';
                } elseif (strpos($line, '═══') !== false) {
                    $class = 'separator';
                }
                
                echo '<div class="log-line ' . $class . '">' . htmlspecialchars($line) . '</div>';
            }
        }
        ?>
    </div>
    
    <script>
        // Auto-scroll to bottom
        window.onload = function() {
            var container = document.querySelector('.log-container');
            container.scrollTop = container.scrollHeight;
        };
    </script>
</body>
</html>

