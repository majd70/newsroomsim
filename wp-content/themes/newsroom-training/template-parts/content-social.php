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

    <?php if ( current_user_can('delete_post', get_the_ID()) ): ?>

<?php
$bulk_value = (function_exists('get_the_ID') && get_the_ID()) ? get_the_ID() : (isset($item['id']) ? $item['id'] : '');
?>
<div class="bulk-select">
<div >
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
                // Determine which modal to open based on platform
                $modalTarget = '#edit' . ucfirst($platform) . 'Modal';

                // Clean the text content - remove HTML tags and decode entities
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
    <div class="social-actions">
        <a href="<?php echo get_permalink(); ?>" class="social-action-btn">
            <i class="fas fa-reply me-1"></i> Reply
        </a>
    </div>
    
    <?php if ($pinned): ?>
        <div class="pinned-badge">
            <i class="fas fa-thumbtack me-1"></i>Pinned
        </div>
    <?php endif; ?>
</article>

