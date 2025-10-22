<?php
/**
 * Debug Posts HTML - Check what HTML is being generated for real-time posts
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Posts HTML Debug</h1>";

// Simulate the same query as the real-time system
$since_timestamp = 'SERVER_TIME_MINUS_10';
$minutes = intval(str_replace('SERVER_TIME_MINUS_', '', $since_timestamp));
$since_date = date('Y-m-d H:i:s', current_time('timestamp') - ($minutes * 60));

echo "<h2>Query Parameters</h2>";
echo "<p><strong>Since timestamp:</strong> {$since_timestamp}</p>";
echo "<p><strong>Since date:</strong> {$since_date}</p>";

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
    'orderby' => 'date',
    'order' => 'DESC'
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
    'orderby' => 'date',
    'order' => 'DESC'
);

$news_posts = get_posts($news_args);
$social_posts = get_posts($social_args);
$all_posts = array_merge($news_posts, $social_posts);

echo "<h2>Posts Found</h2>";
echo "<p><strong>News posts:</strong> " . count($news_posts) . "</p>";
echo "<p><strong>Social posts:</strong> " . count($social_posts) . "</p>";
echo "<p><strong>Total posts:</strong> " . count($all_posts) . "</p>";

if (empty($all_posts)) {
    echo "<p style='color: red;'>❌ No posts found!</p>";
    exit;
}

// Sort all posts by date
usort($all_posts, function($a, $b) {
    return strtotime($b->post_date) - strtotime($a->post_date);
});

echo "<h2>Post Details</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Author</th><th>Date</th></tr>";

foreach ($all_posts as $post) {
    $author = get_userdata($post->post_author);
    echo "<tr>";
    echo "<td>{$post->ID}</td>";
    echo "<td>" . esc_html($post->post_title) . "</td>";
    echo "<td>{$post->post_type}</td>";
    echo "<td>" . ($author ? $author->display_name : 'Unknown') . "</td>";
    echo "<td>{$post->post_date}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Generated HTML</h2>";
echo "<p><strong>This is the exact HTML that would be sent to the JavaScript:</strong></p>";

// Generate HTML for new posts (same as in functions.php)
ob_start();
foreach ($all_posts as $post) {
    setup_postdata($post);
    
    echo "<!-- POST ID: {$post->ID}, TITLE: {$post->post_title}, TYPE: {$post->post_type} -->\n";
    
    if ($post->post_type === 'news_article') {
        get_template_part('template-parts/content', 'news');
    } else {
        get_template_part('template-parts/content', 'social');
    }
    
    echo "<!-- END POST {$post->ID} -->\n\n";
}
wp_reset_postdata();

$posts_html = ob_get_clean();

echo "<h3>Raw HTML Output:</h3>";
echo "<textarea style='width: 100%; height: 300px;'>" . esc_textarea($posts_html) . "</textarea>";

echo "<h3>Rendered HTML Preview:</h3>";
echo "<div style='border: 2px solid red; padding: 10px; background: #f9f9f9;'>";
echo $posts_html;
echo "</div>";

echo "<h2>HTML Analysis</h2>";
echo "<p><strong>HTML Length:</strong> " . strlen($posts_html) . " characters</p>";
echo "<p><strong>Contains 'User':</strong> " . (strpos($posts_html, 'User') !== false ? "YES ❌" : "NO ✅") . "</p>";
echo "<p><strong>Contains '@username':</strong> " . (strpos($posts_html, '@username') !== false ? "YES ❌" : "NO ✅") . "</p>";
echo "<p><strong>Contains post titles:</strong> " . (strpos($posts_html, 'sobhi') !== false || strpos($posts_html, 'dddddsss') !== false ? "YES ✅" : "NO ❌") . "</p>";

// Check if global $post is set correctly
global $post;
echo "<h2>Global Post Check</h2>";
echo "<p><strong>Global \$post ID:</strong> " . ($post ? $post->ID : 'NULL') . "</p>";
echo "<p><strong>Global \$post Title:</strong> " . ($post ? $post->post_title : 'NULL') . "</p>";
?>

<script>
console.log('=== POSTS HTML DEBUG ===');
console.log('HTML Length:', <?php echo strlen($posts_html); ?>);
console.log('Posts found:', <?php echo count($all_posts); ?>);
</script>
