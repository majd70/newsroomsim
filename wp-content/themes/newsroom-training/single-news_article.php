<?php
/**
 * Single News Article Template
 */

get_header();

while (have_posts()) : the_post();
    $breaking = get_post_meta(get_the_ID(), '_news_breaking', true);
    $author_name = get_post_meta(get_the_ID(), '_news_author', true) ?: get_the_author();
    $featured_image = get_post_meta(get_the_ID(), '_news_featured_image', true) ?: get_the_post_thumbnail_url(get_the_ID(), 'large');
    $video_embed = get_post_meta(get_the_ID(), '_news_video_embed', true);
    $categories = get_the_terms(get_the_ID(), 'news_category');
    $category_name = $categories && !is_wp_error($categories) ? $categories[0]->name : 'News';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <article class="news-article">
                <?php if ($breaking): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>BREAKING NEWS</strong>
                    </div>
                <?php endif; ?>
                
                <div class="article-meta mb-3">
                    <span class="badge bg-primary"><?php echo esc_html($category_name); ?></span>
                    <span class="text-muted ms-2">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo get_the_date('F j, Y g:i A'); ?>
                    </span>
                </div>
                
                <h1 class="article-title mb-4"><?php the_title(); ?></h1>
                
                <div class="article-author mb-4">
                    <i class="fas fa-user me-1"></i>
                    By <strong><?php echo esc_html($author_name); ?></strong>
                </div>
                
                <?php if ($featured_image): ?>
                    <div class="article-image mb-4">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid rounded">
                    </div>
                <?php endif; ?>
                
                <?php if ($video_embed): ?>
                    <div class="article-video mb-4">
                        <?php echo $video_embed; ?>
                    </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <?php the_content(); ?>
                </div>
                
                <div class="article-footer mt-5 pt-4 border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">Published: <?php echo get_the_date('F j, Y g:i A'); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="<?php echo home_url(); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Feed
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>

<?php
endwhile;
get_footer();
?>