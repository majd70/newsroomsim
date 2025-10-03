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

    // Get all images
    $all_images_json = get_post_meta(get_the_ID(), '_news_all_images', true);
    $all_images = !empty($all_images_json) ? json_decode($all_images_json, true) : array();
    if (empty($all_images) && $featured_image) {
        $all_images = array($featured_image); // Fallback to single image
    }

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

<?php if ( current_user_can('delete_post', get_the_ID()) ): ?>
    <?php
    $bulk_value = (function_exists('get_the_ID') && get_the_ID()) 
        ? get_the_ID() 
        : (isset($item['id']) ? $item['id'] : '');
    ?>

    <div class="news-media-wrapper position-relative">
        <!-- Existing news image or video -->
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
        <?php elseif (!empty($item['video_url'])): ?>
            <video class="img-fluid" controls>
                <source src="<?php echo esc_url($item['video_url']); ?>" type="video/mp4">
            </video>
        <?php endif; ?>

        <!-- Hover Checkbox -->
        <input type="checkbox" 
               name="delete_ids[]" 
               value="<?php echo esc_attr($bulk_value); ?>" 
               class="delete-checkbox">
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


      <div class="media-wrapper">
    <div class="media-inner">
        <?php if ($video_embed): ?>
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
                    echo '<iframe class="media-content" src="https://www.youtube.com/embed/' . esc_attr($video_id) . '" frameborder="0" allowfullscreen></iframe>';
                }
            } else {
                // If already iframe embed, wrap it inside
                echo '<div class="media-content">' . $video_embed . '</div>';
            }
            ?>
        <?php elseif (!empty($all_images)): ?>
            <?php if (count($all_images) === 1): ?>
                <!-- Single image -->
                <img src="<?php echo esc_url($all_images[0]); ?>"
                     alt="<?php echo esc_attr($headline); ?>"
                     class="media-content">
            <?php else: ?>
                <!-- Multiple images grid -->
                <div class="row g-2 p-2">
                    <?php foreach ($all_images as $index => $image_url): ?>
                        <div class="col-<?php echo count($all_images) === 2 ? '6' : (count($all_images) === 3 ? '4' : '6'); ?>">
                            <img src="<?php echo esc_url($image_url); ?>"
                                 alt="<?php echo esc_attr($headline); ?> - Image <?php echo $index + 1; ?>"
                                 class="img-fluid rounded"
                                 style="width: 100%; height: 250px; object-fit: cover;">
                        </div>
                        <?php if (count($all_images) > 4 && $index === 3): break; endif; ?>
                    <?php endforeach; ?>
                    <?php if (count($all_images) > 4): ?>
                        <div class="col-6 position-relative">
                            <img src="<?php echo esc_url($all_images[3]); ?>"
                                 alt="<?php echo esc_attr($headline); ?>"
                                 class="img-fluid rounded"
                                 style="width: 100%; height: 250px; object-fit: cover; filter: brightness(0.5);">
                            <div class="position-absolute top-50 start-50 translate-middle text-white fs-3 fw-bold">
                                +<?php echo count($all_images) - 4; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Checkbox overlay -->
    <?php if ( current_user_can('delete_post', get_the_ID()) ): ?>
        <input type="checkbox" 
               name="delete_ids[]" 
               value="<?php echo esc_attr(get_the_ID()); ?>" 
               class="delete-checkbox">
    <?php endif; ?>
</div>
            
    <div class="card-content">
        <div class="card-meta">
            <span class="category"><?php echo esc_html($category_name); ?></span>
            <span class="timestamp">
                <i class="fas fa-clock me-1"></i>
                <?php echo $timestamp; ?>
            </span>

            <!-- Post Action Buttons -->
            <div class="post-actions ms-auto">
                <button type="button"
                        class="action-btn action-btn-copy copy-link-btn"
                        data-post-id="<?php echo get_the_ID(); ?>"
                        title="Copy Link">
                    <i class="fas fa-link"></i>
                </button>

                <?php if (current_user_can('edit_post', get_the_ID())): ?>
                    <?php
                    // Clean the body content - remove HTML tags and decode entities
                    $clean_body = wp_strip_all_tags($body);
                    $clean_body = html_entity_decode($clean_body, ENT_QUOTES, 'UTF-8');
                    ?>
                    <button type="button"
                            class="action-btn action-btn-edit edit-post-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editNewsModal"
                            data-post-id="<?php echo esc_attr(get_the_ID()); ?>"
                            data-headline="<?php echo esc_attr($headline); ?>"
                            data-author="<?php echo esc_attr($author_name); ?>"
                            data-body="<?php echo esc_attr($clean_body); ?>"
                            data-category="<?php echo esc_attr($category_name); ?>"
                            data-breaking="<?php echo $breaking ? '1' : '0'; ?>"
                            data-video="<?php echo esc_attr($video_embed); ?>"
                            title="Edit Post">
                        <i class="fas fa-edit"></i>
                    </button>

                    <button type="button"
                            class="action-btn action-btn-delete delete-single-post-ajax"
                            data-post-id="<?php echo get_the_ID(); ?>"
                            data-nonce="<?php echo wp_create_nonce('delete_post_nonce'); ?>"
                            title="Delete Post">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                <?php endif; ?>
            </div>
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