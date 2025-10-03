<?php
/**
 * Archive template for Social Media Posts
 */

get_header();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title mb-4">
                <i class="fas fa-share-alt me-2"></i>
                Social Media Posts
            </h1>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <?php if (current_user_can('delete_posts')): ?>
        <button type="button" class="btn btn-danger" id="deleteAllPostsBtn" data-nonce="<?php echo wp_create_nonce('delete_all_posts_nonce'); ?>">
            <i class="fas fa-trash-alt me-2"></i>Delete All Posts
        </button>
        <?php endif; ?>

        <?php if (current_user_can('edit_posts')): ?>
        <a href="<?php echo home_url('/'); ?>" class="btn btn-publish-post">
            <i class="fas fa-plus-circle me-2"></i>Publish Post
        </a>
        <?php endif; ?>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if (have_posts()): ?>
                <form method="post" action="">
                    <?php wp_nonce_field('bulk_delete_action', 'bulk_delete_nonce'); ?>

                    <?php while (have_posts()): the_post(); ?>
                        <?php get_template_part('template-parts/content', 'social'); ?>
                    <?php endwhile; ?>
                </form>

                <div class="pagination-wrapper">
                    <?php the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                        'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                    )); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-share-alt fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No social media posts found</h3>
                    <p class="text-muted">Check back later for updates.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>