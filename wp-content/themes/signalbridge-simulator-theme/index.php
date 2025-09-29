
<?php
// Fallback index: show latest mixed items similar to front page
get_header();
if ( have_posts() ) {
    echo '<div class="sb-grid">';
    while ( have_posts() ) { the_post();
        if ( get_post_type() === 'news_article' ) {
            get_template_part('partials/card','news');
        } else {
            get_template_part('partials/card','social');
        }
    }
    echo '</div>';
} else {
    echo '<p>No content found.</p>';
}
get_footer();
