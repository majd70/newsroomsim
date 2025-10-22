<?php
require_once('wp-load.php');

echo "=== POST TYPES DEBUG ===\n\n";

// Get all posts
$posts = get_posts([
    'post_type' => 'any',
    'post_status' => 'publish',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC'
]);

echo "Recent posts:\n";
foreach ($posts as $post) {
    echo "ID: {$post->ID} | Type: '{$post->post_type}' | Title: {$post->post_title}\n";
}

echo "\n=== REGISTERED POST TYPES ===\n";
$post_types = get_post_types(['public' => true], 'objects');
foreach ($post_types as $post_type) {
    echo "- {$post_type->name}: {$post_type->label}\n";
}

echo "\n=== EDIT TRANSIENTS ===\n";
global $wpdb;
$transients = $wpdb->get_results(
    "SELECT option_name, option_value FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_newsroom_post_edited_%'"
);

foreach ($transients as $transient) {
    $data = maybe_unserialize($transient->option_value);
    echo "Transient: {$transient->option_name}\n";
    echo "Data: " . print_r($data, true) . "\n";
}

if (empty($transients)) {
    echo "No edit transients found.\n";
}
?>
