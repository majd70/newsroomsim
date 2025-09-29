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
                        
                        <div class="social-stats border-top pt-3">
                            <div class="row text-center">
                                <?php if ($platform === 'twitter'): ?>
                                    <div class="col-4">
                                        <i class="fas fa-comment text-primary"></i>
                                        <div><?php echo $comments; ?></div>
                                        <small class="text-muted">Comments</small>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-retweet text-success"></i>
                                        <div><?php echo $retweets; ?></div>
                                        <small class="text-muted">Retweets</small>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-heart text-danger"></i>
                                        <div><?php echo $likes; ?></div>
                                        <small class="text-muted">Likes</small>
                                    </div>
                                <?php elseif ($platform === 'facebook'): ?>
                                    <div class="col-4">
                                        <i class="fas fa-thumbs-up text-primary"></i>
                                        <div><?php echo $likes; ?></div>
                                        <small class="text-muted">Likes</small>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-comment text-success"></i>
                                        <div><?php echo $comments; ?></div>
                                        <small class="text-muted">Comments</small>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-share text-warning"></i>
                                        <div><?php echo $retweets; ?></div>
                                        <small class="text-muted">Shares</small>
                                    </div>
                                <?php elseif ($platform === 'instagram'): ?>
                                    <div class="col-6">
                                        <i class="fas fa-heart text-danger"></i>
                                        <div><?php echo $likes; ?></div>
                                        <small class="text-muted">Likes</small>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-comment text-primary"></i>
                                        <div><?php echo $comments; ?></div>
                                        <small class="text-muted">Comments</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?php echo home_url(); ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Feed
                        </a>
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