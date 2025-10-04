<?php
/**
 * Single News Article Template
 */

get_header();

while (have_posts()) : the_post();
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
    $pinned = get_post_meta(get_the_ID(), '_news_pinned', true);
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <!-- Single News Article Card - Matching Main Feed Style -->
            <article class="content-card news-card single-news-article mb-4 <?php echo $pinned ? 'pinned' : ''; ?>">

                <!-- Breaking News Banner -->
                <?php if ($breaking): ?>
                    <div class="breaking-banner">
                        <i class="fas fa-exclamation-triangle me-2"></i>BREAKING NEWS
                    </div>
                <?php endif; ?>

                <!-- Media Section - Matching Main Feed Style -->
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
                                    <div class="media-content">
                                        <img src="<?php echo esc_url($all_images[0]); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid">
                                    </div>
                                <?php else: ?>
                                    <!-- Multiple images carousel -->
                                    <div id="newsCarousel-<?php echo get_the_ID(); ?>" class="carousel slide media-content" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php foreach ($all_images as $index => $image): ?>
                                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                    <img src="<?php echo esc_url($image); ?>" class="d-block w-100" alt="News Image <?php echo $index + 1; ?>">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (count($all_images) > 1): ?>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel-<?php echo get_the_ID(); ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon"></span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel-<?php echo get_the_ID(); ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon"></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Card Content Section - Matching Main Feed Style -->
                <div class="card-content">
                    <div class="card-meta">
                        <span class="category"><?php echo esc_html($category_name); ?></span>
                        <span class="timestamp">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo get_the_date('M j, Y g:i A'); ?>
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
                                $clean_body = wp_strip_all_tags(get_the_content());
                                $clean_body = html_entity_decode($clean_body, ENT_QUOTES, 'UTF-8');
                                ?>
                                <button type="button"
                                        class="action-btn action-btn-edit edit-post-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editNewsModal"
                                        data-post-id="<?php echo esc_attr(get_the_ID()); ?>"
                                        data-headline="<?php echo esc_attr(get_the_title()); ?>"
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

                    <h1 class="card-title single-title">
                        <?php the_title(); ?>
                    </h1>

                    <!-- Full Content for Single Page -->
                    <div class="card-content-full">
                        <?php the_content(); ?>
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

                <!-- Comments Section - Matching Main Feed Style -->
                <div class="inline-comments-section" id="comments-section-<?php echo get_the_ID(); ?>">
                    <div class="comments-container">
                        <?php
                        // Get existing comments for this post
                        $post_comments = get_comments(array(
                            'post_id' => get_the_ID(),
                            'status' => 'approve',
                            'order' => 'ASC'
                        ));
                        $comments_count = count($post_comments);
                        ?>

                        <div class="comments-header">
                            <h5 class="mb-3"><i class="fas fa-comments me-2"></i>Comments (<?php echo $comments_count; ?>)</h5>
                        </div>

                        <?php if ($post_comments): ?>
                            <div class="comments-list mb-4" id="comments-list-<?php echo get_the_ID(); ?>">
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
                            <div class="comments-list mb-4" id="comments-list-<?php echo get_the_ID(); ?>">
                                <p class="text-muted">No comments yet. Be the first to comment!</p>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Only Trainee and above can add comments (not Viewer)
                        if (is_user_logged_in() && (current_user_can('add_reply') || current_user_can('edit_posts'))):
                        ?>
                            <div class="add-comment-form bg-white p-3 rounded">
                                <h6 class="mb-2">Add a Comment</h6>
                                <form method="post">
                                    <?php wp_nonce_field('add_comment_action', 'add_comment_nonce'); ?>
                                    <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
                                    <input type="hidden" name="redirect_to" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                                    <div class="mb-3">
                                        <textarea name="comment_content"
                                                  class="form-control"
                                                  rows="3"
                                                  placeholder="Write your comment here..."
                                                  required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Post Comment
                                    </button>
                                </form>
                            </div>
                        <?php elseif (!is_user_logged_in()): ?>
                            <p class="text-muted">Please <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> to comment.</p>
                        <?php else: ?>
                            <p class="text-muted">You don't have permission to add comments.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back to Feed Button -->
                <div class="news-footer mt-4 pt-3 border-top text-center">
                    <a href="<?php echo home_url(); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Feed
                    </a>
                </div>

                <?php if ($pinned): ?>
                    <div class="pinned-badge">
                        <i class="fas fa-thumbtack me-1"></i>Pinned
                    </div>
                <?php endif; ?>
            </article>
        </div>
    </div>
</div>

<?php
endwhile;
get_footer();
?>