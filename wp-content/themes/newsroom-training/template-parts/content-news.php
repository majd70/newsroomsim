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
<article class="content-card news-card mb-4 <?php echo $pinned ? 'pinned' : ''; ?>" data-post-id="<?php echo get_the_ID(); ?>">


    <?php if ($breaking): ?>
        <div class="breaking-banner">
            <i class="fas fa-exclamation-triangle me-2"></i>BREAKING NEWS
        </div>
    <?php endif; ?>   

<?php
// Only show checkbox for users who can delete others' posts (Newsroom Operator and Administrator)
if ( current_user_can('delete_others_posts') ):
?>
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


    <?php if ($video_embed || !empty($all_images)): ?>
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

        <!-- Checkbox overlay - Only for Newsroom Operator and Administrator -->
        <?php if ( current_user_can('delete_others_posts') ): ?>
            <input type="checkbox"
                   name="delete_ids[]"
                   value="<?php echo esc_attr(get_the_ID()); ?>"
                   class="delete-checkbox">
        <?php endif; ?>
      </div>
    <?php endif; ?>
            
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
                        data-permalink="<?php echo esc_url(get_permalink()); ?>"
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
            <?php echo esc_html($headline); ?>
        </h2>
        
        <div class="card-content">
            <?php
            // Show full content instead of excerpt
            if (function_exists('get_the_content')) {
                echo apply_filters('the_content', get_the_content());
            } else {
                echo wp_kses_post($body);
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

    <!-- Comments Section - Always Visible like Social Media Posts -->
    <div class="inline-comments-section" id="comments-section-<?php echo function_exists('get_the_ID') ? get_the_ID() : ($item['id'] ?? 0); ?>">
        <div class="comments-container">
            <?php
            $post_id = function_exists('get_the_ID') ? get_the_ID() : ($item['id'] ?? 0);
            // Get existing comments for this post
            $post_comments = get_comments(array(
                'post_id' => $post_id,
                'status' => 'approve',
                'order' => 'ASC'
            ));
            $comments_count = count($post_comments);
            ?>

            <div class="comments-header">
                <h5 class="mb-3"><i class="fas fa-comments me-2"></i>Comments (<?php echo $comments_count; ?>)</h5>
            </div>

            <?php if ($post_comments): ?>
                <div class="comments-list mb-4" id="comments-list-<?php echo $post_id; ?>">
                    <?php foreach ($post_comments as $comment):
                        $comment_author = get_userdata($comment->user_id);
                        // Trainee can delete their own comments, Newsroom Operator and Admin can delete any comment
                        $can_delete = (get_current_user_id() == $comment->user_id && current_user_can('delete_own_reply'))
                                   || current_user_can('moderate_comments')
                                   || current_user_can('delete_others_posts');
                    ?>
                        <div class="comment-item border-bottom pb-3 mb-3 bg-white p-3 rounded" id="comment-<?php echo $comment->comment_ID; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong><?php echo esc_html($comment_author ? $comment_author->display_name : $comment->comment_author); ?></strong>
                                    <small class="text-muted ms-2"><?php echo human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ago'; ?></small>
                                    <p class="mb-0 mt-1"><?php echo esc_html($comment->comment_content); ?></p>
                                </div>
                                <?php if ($can_delete): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-danger delete-comment-btn"
                                            data-comment-id="<?php echo $comment->comment_ID; ?>"
                                            data-nonce="<?php echo wp_create_nonce('delete_comment_' . $comment->comment_ID); ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="comments-list mb-4" id="comments-list-<?php echo $post_id; ?>">
                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                </div>
            <?php endif; ?>

            <?php
            // Only Trainee and above can add comments (not Viewer)
            if (is_user_logged_in() && (current_user_can('add_reply') || current_user_can('edit_posts'))):
            ?>
                <div class="add-comment-form bg-white p-3 rounded">
                    <h6 class="mb-2">Add a Comment</h6>
                    <div class="realtime-comment-form" data-post-id="<?php echo $post_id; ?>">
                        <div class="mb-3">
                            <textarea class="form-control comment-textarea"
                                      rows="3"
                                      placeholder="Write your comment here..."
                                      required></textarea>
                        </div>
                        <button type="button" class="btn btn-primary realtime-comment-btn">
                            <i class="fas fa-paper-plane me-1"></i> Post Comment
                        </button>
                    </div>
                </div>
            <?php elseif (!is_user_logged_in()): ?>
                <p class="text-muted">Please <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> to comment.</p>
            <?php else: ?>
                <p class="text-muted">You don't have permission to add comments.</p>
            <?php endif; ?>
        </div>
    </div>
</article>