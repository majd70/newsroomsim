<?php
/**
 * Template part for displaying social media posts
 * Hybrid mode - works in standalone PHP and WordPress
 */

if (function_exists('get_post_meta')) {
    // WordPress mode
    $platform = get_post_meta(get_the_ID(), '_social_platform', true) ?: 'twitter';
    $display_name = get_post_meta(get_the_ID(), '_social_display_name', true) ?: 'User';
    $handle = get_post_meta(get_the_ID(), '_social_handle', true) ?: 'username';
    $avatar = get_post_meta(get_the_ID(), '_social_avatar', true) ?: get_template_directory_uri() . '/assets/images/default-avatar.svg';
    $media = get_post_meta(get_the_ID(), '_social_media', true);

    // Get all media images
    $all_media_json = get_post_meta(get_the_ID(), '_social_all_media', true);
    $all_media = !empty($all_media_json) ? json_decode($all_media_json, true) : array();
    if (empty($all_media) && $media) {
        $all_media = array($media); // Fallback to single media
    }

    // ✅ Use random counts instead of static ones
    $likes    = intval(get_post_meta(get_the_ID(), '_random_like_count', true));
    $comments = intval(get_post_meta(get_the_ID(), '_random_comment_count', true));
    $retweets = intval(get_post_meta(get_the_ID(), '_random_tweet_count', true));

    $text = function_exists('get_the_content') ? get_the_content() : '';
    $pinned = get_post_meta(get_the_ID(), '_pinned', true);
    $timestamp = function_exists('get_the_date') ? get_the_date('M j, Y g:i A') : '';
} else {
    // Standalone mode - use global item
    $item = $GLOBALS['current_item'];
    $platform = $item['type'];
    $display_name = $item['display_name'] ?? 'User';
    $handle = $item['handle'] ?? 'username';
    $avatar = $item['avatar'] ?? (defined('ASSETS_URL') ? ASSETS_URL . '/images/default-avatar.svg' : '/themes/newsroom-training/assets/images/default-avatar.svg');
    $media = $item['media'] ?? '';
    $likes = $item['likes'] ?? 0;
    $comments = $item['comments'] ?? 0;
    $retweets = $item['retweets'] ?? 0;
    $text = $item['text'] ?? '';
    $pinned = !empty($item['pinned']);
    $timestamp = formatSocialTimestamp($item['timestamp'] ?? time());
}

?>

<!-- Social Media Post Card -->
<article class="content-card social-card <?php echo esc_attr($platform); ?>-card mb-4 <?php echo $pinned ? 'pinned' : ''; ?>">

    <div class="social-header">

   <div class="social-avatar" style="position: relative; display: inline-block;">

    <?php
    // Only show checkbox for users who can delete others' posts (Newsroom Operator and Administrator)
    if ( current_user_can('delete_others_posts') ):
    ?>
        <?php
        $bulk_value = (function_exists('get_the_ID') && get_the_ID()) ? get_the_ID() : (isset($item['id']) ? $item['id'] : '');
        ?>
        <div class="bulk-select">
            <div>
                <!-- Hover Checkbox -->
                <input type="checkbox"
                       name="delete_ids[]"
                       value="<?php echo (function_exists('get_the_ID') ? get_the_ID() : ($item['id'] ?? '')); ?>"
                       class="delete-checkbox">
            </div>
        </div>
    <?php endif; ?>


    <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($display_name); ?> avatar">
    
    <?php if ($platform === 'facebook'): ?>
        <span class="social-badge facebook-badge">
            <i class="fab fa-facebook-f"></i>
        </span>
    <?php elseif ($platform === 'twitter'): ?>     
<span class="social-badge x-badge">
  <i class="fa-brands fa-x-twitter"></i>
</span>

    <?php elseif ($platform === 'instagram'): ?>
        <span class="social-badge instagram-badge">
            <i class="fab fa-instagram"></i>
        </span>
    <?php elseif ($platform === 'truth'): ?>
        <!-- Truth Social logo will be shown beside the name instead -->
    <?php endif; ?>
</div>

        <div class="social-info">
            <?php if ($platform === 'facebook'): ?>
                <!-- Facebook: Name only, no handle -->
                <div class="social-name"><?php echo esc_html($display_name); ?></div>
            <?php elseif ($platform === 'instagram'): ?>
                <!-- Instagram: Handle only, no @ symbol -->
                <div class="social-handle"><?php echo esc_html($handle); ?></div>
            <?php elseif ($platform === 'twitter'): ?>
                <!-- Twitter/X: Name AND handle -->
                <div class="social-name"><?php echo esc_html($display_name); ?></div>
                <div class="social-handle">@<?php echo esc_html($handle); ?></div>
            <?php elseif ($platform === 'truth'): ?>
                <!-- Truth Social: Name AND handle -->
                <div class="social-name">
                    <?php echo esc_html($display_name); ?>
                    <img src="<?php echo home_url('/Red_Truth.PNG'); ?>" alt="Truth Social" class="truth-logo-badge" style="width: 24px; height: 24px; margin-left: 8px; vertical-align: middle;">
                </div>
                <div class="social-handle">@<?php echo esc_html($handle); ?></div>
            <?php else: ?>
                <!-- Default fallback -->
                <div class="social-name"><?php echo esc_html($display_name); ?></div>
                <div class="social-handle">@<?php echo esc_html($handle); ?></div>
            <?php endif; ?>
        </div>
        <div class="social-timestamp">
            <i class="fas fa-clock me-1"></i>
            <?php echo $timestamp; ?>
        </div>

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
                // Determine which modal to open based on platform
                $modalTarget = '#edit' . ucfirst($platform) . 'Modal';

                // Clean the text content - remove HTML tags and decode entities
                $clean_text = wp_strip_all_tags($text);
                $clean_text = html_entity_decode($clean_text, ENT_QUOTES, 'UTF-8');

                // Debug: Log the values for Truth Social posts
                if ($platform === 'truth') {
                    error_log('🔍 Truth Social Post Debug - Post ID: ' . get_the_ID());
                    error_log('  - Platform: ' . $platform);
                    error_log('  - Display Name: ' . $display_name);
                    error_log('  - Handle: ' . $handle);
                    error_log('  - Text Length: ' . strlen($text));
                    error_log('  - Clean Text Length: ' . strlen($clean_text));
                    error_log('  - Modal Target: ' . $modalTarget);
                    error_log('  - Raw post content: ' . get_the_content());
                    error_log('  - Raw meta _social_display_name: ' . get_post_meta(get_the_ID(), '_social_display_name', true));
                    error_log('  - Raw meta _social_handle: ' . get_post_meta(get_the_ID(), '_social_handle', true));

                    // Also log what will be in the button attributes
                    error_log('  - Button data-display-name will be: "' . esc_attr($display_name) . '"');
                    error_log('  - Button data-handle will be: "' . esc_attr($handle) . '"');
                    error_log('  - Button data-text will be: "' . esc_attr($clean_text) . '"');
                }

                ?>
                <button type="button"
                        class="action-btn action-btn-edit edit-post-btn"
                        data-bs-toggle="modal"
                        data-bs-target="<?php echo $modalTarget; ?>"
                        data-post-id="<?php echo esc_attr(get_the_ID()); ?>"
                        data-platform="<?php echo esc_attr($platform); ?>"
                        data-display-name="<?php echo esc_attr($display_name); ?>"
                        data-handle="<?php echo esc_attr($handle); ?>"
                        data-text="<?php echo esc_attr($clean_text); ?>"
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
    <div class="social-content">
        <div class="social-text"><?php
        if (function_exists('wpautop')) {
            echo wpautop($text);
        } else {
            echo nl2br(esc_html($text));
        }
        ?></div>
        <?php if (!empty($all_media)): ?>
            <div class="social-media">
                <?php if (count($all_media) === 1): ?>
                    <!-- Single image -->
                    <img src="<?php echo esc_url($all_media[0]); ?>" alt="Post media" class="img-fluid rounded" style="width: 100%; max-height: 500px; object-fit: contain; display: block;">
                <?php else: ?>
                    <!-- Multiple images grid -->
                    <div class="row g-2">
                        <?php foreach ($all_media as $index => $image_url): ?>
                            <div class="col-<?php echo count($all_media) === 2 ? '6' : (count($all_media) === 3 ? '4' : '6'); ?>">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Post media <?php echo $index + 1; ?>" class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover;">
                            </div>
                            <?php if (count($all_media) > 4 && $index === 3): break; endif; ?>
                        <?php endforeach; ?>
                        <?php if (count($all_media) > 4): ?>
                            <div class="col-6 position-relative">
                                <img src="<?php echo esc_url($all_media[3]); ?>" alt="Post media" class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover; filter: brightness(0.5);">
                                <div class="position-absolute top-50 start-50 translate-middle text-white fs-3 fw-bold">
                                    +<?php echo count($all_media) - 4; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Inline Comments Section - Always Visible -->
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
    
    <?php if ($pinned): ?>
        <div class="pinned-badge">
            <i class="fas fa-thumbtack me-1"></i>Pinned
        </div>
    <?php endif; ?>
</article>

