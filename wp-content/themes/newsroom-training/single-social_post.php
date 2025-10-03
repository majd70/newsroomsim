<?php
/**
 * Single Social Media Post Template
 */

get_header();

while (have_posts()) : the_post();
    $platform = get_post_meta(get_the_ID(), '_social_platform', true) ?: 'twitter';
    $display_name = get_post_meta(get_the_ID(), '_social_display_name', true) ?: 'User';
    $handle = get_post_meta(get_the_ID(), '_social_handle', true) ?: 'username';
    $avatar = get_post_meta(get_the_ID(), '_social_avatar', true) ?: get_template_directory_uri() . '/assets/images/default-avatar.svg';
    $media = get_post_meta(get_the_ID(), '_social_media', true);
    $likes = intval(get_post_meta(get_the_ID(), '_social_likes', true));
    $comments = intval(get_post_meta(get_the_ID(), '_social_comments', true));
    $retweets = intval(get_post_meta(get_the_ID(), '_social_retweets', true));
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <!-- Social Media Post Card -->
            <article class="content-card social-card <?php echo esc_attr($platform); ?>-card mb-4">
                <div class="social-header">
                    <div class="social-avatar" style="position: relative; display: inline-block;">
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
                        <?php endif; ?>
                    </div>

                    <div class="social-info">
                        <div class="social-name"><?php echo esc_html($display_name); ?></div>
                        <div class="social-handle">@<?php echo esc_html($handle); ?></div>
                    </div>
                    <div class="social-timestamp">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo get_the_date('M j, Y g:i A'); ?>
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

                            // Get the text content
                            $text = get_the_content();
                            $clean_text = wp_strip_all_tags($text);
                            $clean_text = html_entity_decode($clean_text, ENT_QUOTES, 'UTF-8');
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
                    <div class="social-text"><?php echo wpautop(get_the_content()); ?></div>
                    <?php if ($media): ?>
                        <div class="social-media">
                            <img src="<?php echo esc_url($media); ?>" alt="Post media" class="img-fluid rounded" style="width: 100%; max-height: 500px; object-fit: contain; display: block;">
                        </div>
                    <?php endif; ?>
                </div>


                <!-- Comments Section -->
                <div class="social-comments mt-4 p-3 bg-light rounded">
                    <?php
                    // Get comments for this post
                    $post_comments = get_comments(array(
                        'post_id' => get_the_ID(),
                        'status' => 'approve',
                        'order' => 'ASC'
                    ));
                    $comments_count = count($post_comments);
                    ?>

                    <h5 class="mb-3"><i class="fas fa-comments me-2"></i>Comments (<?php echo $comments_count; ?>)</h5>

                    <?php if ($post_comments): ?>
                        <div class="comments-list mb-4">
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
                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                    <?php endif; ?>

                    <?php
                    // Only Trainee and above can add comments (not Viewer)
                    if (is_user_logged_in() && (current_user_can('add_reply') || current_user_can('edit_posts'))):
                    ?>
                        <div class="add-comment-form bg-white p-3 rounded">
                            <h6 class="mb-2">Add a Comment</h6>
                            <form id="commentForm" method="post">
                                <?php wp_nonce_field('add_comment_action', 'add_comment_nonce'); ?>
                                <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
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

                    <div class="mt-3 text-center">
                        <a href="<?php echo home_url(); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Feed
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>

<?php
endwhile;

// Include the edit modals
get_template_part('template-parts/edit-modals');

get_footer();
?>