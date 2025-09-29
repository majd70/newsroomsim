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
            <?php echo $timestamp; ?>
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
        <?php if ($media): ?>
            <div class="social-media">
                <img src="<?php echo esc_url($media); ?>" alt="Post media" class="img-fluid rounded">
            </div>
        <?php endif; ?>
    </div>
    <div class="social-actions">
        <?php if ($platform === 'twitter'): ?>
            <span class="social-action"><i class="fas fa-comment"></i> <?php echo $comments; ?></span>
            <span class="social-action"><i class="fas fa-retweet"></i> <?php echo $retweets; ?></span>
            <span class="social-action"><i class="fas fa-heart"></i> <?php echo $likes; ?></span>
        <?php elseif ($platform === 'facebook'): ?>
            <span class="social-action"><i class="fas fa-thumbs-up"></i> <?php echo $likes; ?> Likes</span>
            <span class="social-action"><i class="fas fa-comment"></i> <?php echo $comments; ?> Comments</span>
            <span class="social-action"><i class="fas fa-share"></i> <?php echo $retweets; ?> Shares</span>
        <?php elseif ($platform === 'instagram'): ?>
            <span class="social-action"><i class="fas fa-heart"></i> <?php echo $likes; ?></span>
            <span class="social-action"><i class="fas fa-comment"></i> <?php echo $comments; ?></span>
            <span class="social-action"><i class="fas fa-paper-plane"></i></span>
        <?php endif; ?>
    </div>
    
    <?php if ($pinned): ?>
        <div class="pinned-badge">
            <i class="fas fa-thumbtack me-1"></i>Pinned
        </div>
    <?php endif; ?>
</article>

