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
            <div class="social-post-single">
                <div class="card">
                    <div class="card-header bg-<?php echo $platform; ?> text-white">
                        <i class="fab fa-<?php echo $platform; ?> me-2"></i>
                        <?php echo ucfirst($platform); ?> Post
                    </div>
                    <div class="card-body">
                        <div class="social-header mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($display_name); ?>" class="rounded-circle me-3" width="60" height="60">
                                <div>
                                    <h5 class="mb-0"><?php echo esc_html($display_name); ?></h5>
                                    <p class="text-muted mb-0">@<?php echo esc_html($handle); ?></p>
                                    <small class="text-muted"><?php echo get_the_date('M j, Y g:i A'); ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="social-content mb-3">
                            <?php the_content(); ?>
                        </div>
                        
                        <?php if ($media): ?>
                            <div class="social-media mb-3">
                                <img src="<?php echo esc_url($media); ?>" alt="Post media" class="img-fluid rounded">
                            </div>
                        <?php endif; ?>
                        
                    </div>

                    <!-- Comments Section -->
                    <div class="card-footer">
                        <h5 class="mb-3"><i class="fas fa-comments me-2"></i>Comments</h5>

                        <?php
                        // Get comments for this post
                        $post_comments = get_comments(array(
                            'post_id' => get_the_ID(),
                            'status' => 'approve',
                            'order' => 'ASC'
                        ));

                        if ($post_comments): ?>
                            <div class="comments-list mb-4">
                                <?php foreach ($post_comments as $comment):
                                    $comment_author = get_userdata($comment->user_id);
                                    $can_delete = (get_current_user_id() == $comment->user_id) || current_user_can('moderate_comments');
                                ?>
                                    <div class="comment-item border-bottom pb-3 mb-3" id="comment-<?php echo $comment->comment_ID; ?>">
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

                        <?php if (is_user_logged_in()): ?>
                            <div class="add-comment-form">
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
                        <?php else: ?>
                            <p class="text-muted">Please <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> to comment.</p>
                        <?php endif; ?>

                        <div class="mt-3 text-center">
                            <a href="<?php echo home_url(); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Feed
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
endwhile;
get_footer();
?>