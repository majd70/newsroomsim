<?php
/**
 * Template part for displaying news articles
 * Hybrid mode - works in standalone PHP and WordPress
 */

if (function_exists('get_post_meta')) {
    // WordPress mode
    $breaking = get_post_meta(get_the_ID(), '_news_breaking', true);
    $author_name = get_post_meta(get_the_ID(), '_news_author', true) ?: get_the_author();
    $featured_image = get_post_meta(get_the_ID(), '_news_featured_image', true) ?: get_the_post_thumbnail_url(get_the_ID(), 'large');
    $video_embed = get_post_meta(get_the_ID(), '_news_video_embed', true);
    $categories = get_the_terms(get_the_ID(), 'news_category');
    $category_name = $categories && !is_wp_error($categories) ? $categories[0]->name : 'News';
    $pinned = get_post_meta(get_the_ID(), '_pinned', true);
    $headline = function_exists('get_the_title') ? get_the_title() : '';
    $body = function_exists('get_the_content') ? get_the_content() : '';
    $timestamp = function_exists('get_the_date') ? get_the_date('M j, Y g:i A') : '';
    $permalink = function_exists('get_the_permalink') ? get_the_permalink() : '#';
} else {
    // Standalone mode - use global item
    $item = $GLOBALS['current_item'];
    $breaking = !empty($item['breaking']);
    $author_name = $item['author'] ?? 'Unknown';
    $featured_image = $item['featured_image'] ?? '';
    $video_embed = $item['video_embed'] ?? '';
    $category_name = $item['category'] ?? 'General';
    $pinned = !empty($item['pinned']);
    $headline = $item['headline'] ?? '';
    $body = $item['body'] ?? '';
    $timestamp = formatTimestamp($item['timestamp'] ?? time());
    $permalink = '#';
}
?>

<!-- News Article Card -->
<article class="content-card news-card mb-4 <?php echo $pinned ? 'pinned' : ''; ?>">
    <?php if ($breaking): ?>
        <div class="breaking-banner">
            <i class="fas fa-exclamation-triangle me-2"></i>BREAKING NEWS
        </div>
    <?php endif; ?>
    
    <!-- <?php if ($featured_image): ?>
        <div class="card-image">
            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($headline); ?>">
            <?php if ($video_embed): ?>
                <div class="video-badge">
                    <i class="fas fa-play"></i> Video
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?> -->


<?php if ($video_embed): ?>
    <div class="video-wrapper">
        <?php 
        // Check if it's a YouTube link
        if (strpos($video_embed, 'youtube.com/watch') !== false || strpos($video_embed, 'youtu.be') !== false) {
            $video_id = '';
            if (preg_match('/v=([a-zA-Z0-9_-]+)/', $video_embed, $matches)) {
                $video_id = $matches[1];
            } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $video_embed, $matches)) {
                $video_id = $matches[1];
            }

            if ($video_id) {
                echo '<iframe src="https://www.youtube.com/embed/' . esc_attr($video_id) . '" frameborder="0" allowfullscreen></iframe>';
            }
        } else {
            // If already iframe embed, just print it
            echo $video_embed;
        }
        ?>
    </div>
<?php elseif ($featured_image): ?>
    <div class="card-image">
        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($headline); ?>">
    </div>
<?php endif; ?>





            
    <div class="card-content">
        <div class="card-meta">
            <span class="category"><?php echo esc_html($category_name); ?></span>
            <span class="timestamp">
                <i class="fas fa-clock me-1"></i>
                <?php echo $timestamp; ?>
            </span>
        </div>
        
        <h2 class="card-title">
            <?php if (function_exists('the_permalink')): ?>
                <a href="<?php the_permalink(); ?>"><?php echo esc_html($headline); ?></a>
            <?php else: ?>
                <?php echo esc_html($headline); ?>
            <?php endif; ?>
        </h2>
        
        <div class="card-excerpt">
            <?php 
            if (function_exists('get_the_excerpt')) {
                $excerpt = get_the_excerpt();
                echo $excerpt ? $excerpt : wp_trim_words($body, 25);
            } else {
                echo esc_html(substr($body, 0, 200)) . '...';
            }
            ?>
        </div>
        
        <div class="card-author">
            <i class="fas fa-user me-1"></i>
            By <?php echo esc_html($author_name); ?>
        </div>
        
        <?php if ($pinned): ?>
            <div class="pinned-badge">
                <i class="fas fa-thumbtack me-1"></i>Pinned
            </div>
        <?php endif; ?>
    </div>
</article>