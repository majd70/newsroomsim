<?php
/**
 * The main template file for Newsroom Training Theme
 * Hybrid mode - works in both standalone and WordPress environments
 * 
 * @package Newsroom_Training
 */

// Check if we're in WordPress environment
if (function_exists('get_header')) {
    // WordPress mode
    get_header();

    // Scenario filter only
    $scenario = isset($_GET['scenario']) ? sanitize_text_field($_GET['scenario']) : '';

    // Build query for mixed content types
    $news_args = array(
        'post_type' => 'news_article',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );

    $social_args = array(
        'post_type' => 'social_post',
        'post_status' => 'publish', 
        'posts_per_page' => -1
    );

    // Apply scenario filter if set
    if ($scenario) {
        $news_args['tax_query'] = array(
            array(
                'taxonomy' => 'training_scenario',
                'field' => 'slug',
                'terms' => $scenario
            )
        );
        $social_args['tax_query'] = array(
            array(
                'taxonomy' => 'training_scenario', 
                'field' => 'slug',
                'terms' => $scenario
            )
        );
    }

    // Get all posts (no platform filter anymore)
    $news_posts = get_posts($news_args);
    $social_posts = get_posts($social_args);

    // Combine and sort posts
    $all_posts = array_merge($news_posts, $social_posts);
    usort($all_posts, function($a, $b) {
        $a_pinned = get_post_meta($a->ID, '_pinned', true);
        $b_pinned = get_post_meta($b->ID, '_pinned', true);
        
        if ($a_pinned && !$b_pinned) return -1;
        if ($b_pinned && !$a_pinned) return 1;
        
        return strtotime($b->post_date) - strtotime($a->post_date);
    });
?>

<div class="container mt-4">
    <!-- Content Feed -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if (empty($all_posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No content available</h3>
                    <p class="text-muted">Create some news articles or social media posts to see them here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($all_posts as $post): 
                    setup_postdata($post);
                    
                    if ($post->post_type === 'news_article'):
                        get_template_part('template-parts/content', 'news');
                    else:
                        get_template_part('template-parts/content', 'social'); 
                    endif;
                endforeach;
                wp_reset_postdata(); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
    get_footer();
} else {
    // Standalone mode - use original functionality
    session_start();
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    require_once 'includes/auth.php';
    require_once 'themes/newsroom-training/theme-config.php';

    // Check if user is logged in
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    $user = getCurrentUser();
    $scenario = isset($_GET['scenario']) ? sanitize_text_field($_GET['scenario']) : null;

    // Get all content (ignore platform filter completely)
    $filteredContent = newsroom_get_content('all', $scenario);
    
    get_header(); ?>
    
    <div class="container mt-4">
        <!-- Content Feed -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if (empty($filteredContent)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No content available</h3>
                        <p class="text-muted">Check back later for new posts.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    // Sort content: pinned first, then by date
                    usort($filteredContent, function($a, $b) {
                        if (!empty($a['pinned']) && empty($b['pinned'])) return -1;
                        if (!empty($b['pinned']) && empty($a['pinned'])) return 1;
                        return $b['timestamp'] - $a['timestamp'];
                    });
                    
                    foreach ($filteredContent as $item): 
                        // Set global item for template parts
                        $GLOBALS['current_item'] = $item;
                        
                        if ($item['type'] === 'news'):
                            include 'template-parts/content-news.php';
                        else:
                            include 'template-parts/content-social.php';
                        endif;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php get_footer();
}
