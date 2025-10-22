<?php
/**
 * Timestamp Test - Check what the real-time system is actually using
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Real-time System Timestamp Test</h1>";

// Test the special timestamp handling
$test_timestamps = [
    'SERVER_TIME_NOW',
    'SERVER_TIME_MINUS_10',
    'SERVER_TIME_MINUS_60',
    '2025-10-18 13:30:00'
];

echo "<h2>Timestamp Processing Test</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Input</th><th>Processed Output</th><th>Description</th></tr>";

foreach ($test_timestamps as $since_timestamp) {
    // Handle special timestamp requests from JavaScript (same logic as in functions.php)
    if ($since_timestamp === 'SERVER_TIME_NOW') {
        $since_date = current_time('Y-m-d H:i:s');
        $description = "Current WordPress time";
    } elseif (strpos($since_timestamp, 'SERVER_TIME_MINUS_') === 0) {
        $minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
        $since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));
        $description = "WordPress time minus {$minutes} minutes";
    } else {
        // Convert timestamp to MySQL datetime format
        $since_date = date('Y-m-d H:i:s', strtotime($since_timestamp));
        $description = "Regular timestamp conversion";
    }
    
    echo "<tr>";
    echo "<td style='background: yellow;'>{$since_timestamp}</td>";
    echo "<td style='background: lightgreen;'>{$since_date}</td>";
    echo "<td>{$description}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Query Test with SERVER_TIME_MINUS_10</h2>";

// Test the actual query that the real-time system uses
$since_timestamp = 'SERVER_TIME_MINUS_10';
$minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
$since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));

echo "<p><strong>Checking for posts since:</strong> {$since_date}</p>";

// Query for new posts (same as in functions.php)
$news_args = array(
    'post_type' => 'news_article',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'date_query' => array(
        array(
            'after' => $since_date,
            'inclusive' => false,
        ),
    ),
);

$social_args = array(
    'post_type' => 'social_post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'date_query' => array(
        array(
            'after' => $since_date,
            'inclusive' => false,
        ),
    ),
);

$news_posts = get_posts($news_args);
$social_posts = get_posts($social_args);
$all_posts = array_merge($news_posts, $social_posts);

echo "<p><strong>News posts found:</strong> " . count($news_posts) . "</p>";
echo "<p><strong>Social posts found:</strong> " . count($social_posts) . "</p>";
echo "<p><strong>Total posts found:</strong> " . count($all_posts) . "</p>";

if (count($all_posts) > 0) {
    echo "<h3>Found Posts:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>post_date</th><th>Comparison</th></tr>";
    
    foreach ($all_posts as $post) {
        $comparison = ($post->post_date > $since_date) ? "✅ NEWER" : "❌ OLDER";
        echo "<tr>";
        echo "<td>{$post->ID}</td>";
        echo "<td>" . esc_html(substr($post->post_title, 0, 30)) . "...</td>";
        echo "<td>{$post->post_type}</td>";
        echo "<td style='background: yellow;'>{$post->post_date}</td>";
        echo "<td style='background: " . (($post->post_date > $since_date) ? "lightgreen" : "pink") . ";'>{$comparison}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No posts found! This explains why real-time updates aren't working.</p>";
    echo "<p><strong>Possible reasons:</strong></p>";
    echo "<ul>";
    echo "<li>All posts are older than {$since_date}</li>";
    echo "<li>The date query is not working correctly</li>";
    echo "<li>Timezone mismatch between query and post dates</li>";
    echo "</ul>";
}

echo "<h2>Current Time Information</h2>";
echo "<p><strong>current_time('Y-m-d H:i:s'):</strong> " . current_time('Y-m-d H:i:s') . "</p>";
echo "<p><strong>current_time('timestamp'):</strong> " . current_time('timestamp') . "</p>";
echo "<p><strong>date('Y-m-d H:i:s'):</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>gmdate('Y-m-d H:i:s'):</strong> " . gmdate('Y-m-d H:i:s') . "</p>";
?>

<script>
console.log('=== TIMESTAMP TEST ===');
console.log('JavaScript current time:', new Date().toString());

// Test what the real-time system is actually sending
const testTimestamps = ['SERVER_TIME_NOW', 'SERVER_TIME_MINUS_10'];
testTimestamps.forEach(ts => {
    console.log('JavaScript would send:', ts);
});
</script>
