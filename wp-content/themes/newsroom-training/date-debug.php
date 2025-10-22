<?php
/**
 * Date Debug - Check what's wrong with dates
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Date Debug Information</h1>";

echo "<h2>Server Information</h2>";
echo "<p><strong>Server Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server Timezone:</strong> " . date_default_timezone_get() . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

echo "<h2>WordPress Information</h2>";
echo "<p><strong>WordPress current_time('Y-m-d H:i:s'):</strong> " . current_time('Y-m-d H:i:s') . "</p>";
echo "<p><strong>WordPress current_time('mysql'):</strong> " . current_time('mysql') . "</p>";
echo "<p><strong>WordPress current_time('timestamp'):</strong> " . current_time('timestamp') . "</p>";
echo "<p><strong>WordPress Timezone String:</strong> " . get_option('timezone_string') . "</p>";
echo "<p><strong>WordPress GMT Offset:</strong> " . get_option('gmt_offset') . "</p>";

echo "<h2>JavaScript Test</h2>";
?>
<script>
console.log('=== DATE DEBUG ===');
console.log('JavaScript Date:', new Date().toString());
console.log('JavaScript Year:', new Date().getFullYear());
console.log('JavaScript Month:', new Date().getMonth() + 1);
console.log('JavaScript Day:', new Date().getDate());
console.log('JavaScript Hours:', new Date().getHours());
console.log('JavaScript Minutes:', new Date().getMinutes());

const now = new Date();
const jsTimestamp = now.getFullYear() + '-' + 
                   String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(now.getDate()).padStart(2, '0') + ' ' + 
                   String(now.getHours()).padStart(2, '0') + ':' + 
                   String(now.getMinutes()).padStart(2, '0') + ':' + 
                   String(now.getSeconds()).padStart(2, '0');

console.log('JavaScript Generated Timestamp:', jsTimestamp);

document.write('<p><strong>JavaScript Date:</strong> ' + new Date().toString() + '</p>');
document.write('<p><strong>JavaScript Year:</strong> ' + new Date().getFullYear() + '</p>');
document.write('<p><strong>JavaScript Generated Timestamp:</strong> ' + jsTimestamp + '</p>');
</script>

<?php
echo "<h2>Recent Posts Dates</h2>";
$recent_posts = get_posts(array(
    'post_type' => array('news_article', 'social_post'),
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
));

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Title</th><th>post_date</th><th>post_date_gmt</th></tr>";
foreach ($recent_posts as $post) {
    echo "<tr>";
    echo "<td>{$post->ID}</td>";
    echo "<td>" . esc_html(substr($post->post_title, 0, 30)) . "...</td>";
    echo "<td style='background: yellow;'>{$post->post_date}</td>";
    echo "<td>{$post->post_date_gmt}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Test Timestamp Comparison</h2>";
$test_js_timestamp = "2024-10-18 21:17:39"; // What JavaScript is generating
$test_wp_timestamp = current_time('Y-m-d H:i:s'); // What WordPress thinks

echo "<p><strong>JavaScript Timestamp:</strong> {$test_js_timestamp}</p>";
echo "<p><strong>WordPress Timestamp:</strong> {$test_wp_timestamp}</p>";
echo "<p><strong>Comparison:</strong> " . ($test_js_timestamp < $test_wp_timestamp ? "JS is OLDER" : "JS is NEWER or SAME") . "</p>";

// Test query with JS timestamp
$test_posts = get_posts(array(
    'post_type' => array('news_article', 'social_post'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'date_query' => array(
        array(
            'after' => $test_js_timestamp,
            'inclusive' => false,
        ),
    ),
));

echo "<p><strong>Posts found after JS timestamp:</strong> " . count($test_posts) . "</p>";

if (count($test_posts) > 0) {
    echo "<p style='color: green;'>✅ Posts found! The query works.</p>";
} else {
    echo "<p style='color: red;'>❌ No posts found! This is why real-time updates don't work.</p>";
}
?>
