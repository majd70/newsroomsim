
<?php
/* Template Name: Home Feed (Mixed) */
get_header();

$feed = sb_feed_filter_current();

// Build queries for mixed feed
$args = [
    'post_type' => ['news_article','social_post'],
    'posts_per_page' => 12,
];

// Filter by type
if ($feed === 'news') {
    $args['post_type'] = ['news_article'];
} elseif (in_array($feed, ['tweet','facebook','instagram'], true)) {
    $args['post_type'] = ['social_post'];
    $args['meta_query'] = [[
        'key' => '_sb_platform',
        'value' => $feed,
        'compare' => '=',
    ]];
}

// Optional Scenario filter via ?scenario=slug
if ( isset($_GET['scenario']) ) {
    $args['tax_query'] = [[
        'taxonomy' => 'scenario',
        'field' => 'slug',
        'terms' => sanitize_text_field($_GET['scenario'])
    ]];
}

$q = new WP_Query($args);
?>

<div class="sb-filters">
  <a class="<?php echo $feed==='all'?'active':''; ?>" href="<?php echo esc_url( add_query_arg('feed','all', get_permalink()) ); ?>">All</a>
  <a class="<?php echo $feed==='news'?'active':''; ?>" href="<?php echo esc_url( add_query_arg('feed','news', get_permalink()) ); ?>">News</a>
  <a class="<?php echo $feed==='tweet'?'active':''; ?>" href="<?php echo esc_url( add_query_arg('feed','tweet', get_permalink()) ); ?>">Tweets</a>
  <a class="<?php echo $feed==='facebook'?'active':''; ?>" href="<?php echo esc_url( add_query_arg('feed','facebook', get_permalink()) ); ?>">Facebook</a>
  <a class="<?php echo $feed==='instagram'?'active':''; ?>" href="<?php echo esc_url( add_query_arg('feed','instagram', get_permalink()) ); ?>">Instagram</a>
</div>

<div class="sb-grid">
<?php if ( $q->have_posts() ): while ( $q->have_posts() ): $q->the_post();
    if ( get_post_type() === 'news_article' ) {
        get_template_part('partials/card','news');
    } else {
        get_template_part('partials/card','social');
    }
endwhile; wp_reset_postdata(); else: ?>
    <p>No posts yet.</p>
<?php endif; ?>
</div>

<?php get_footer(); ?>
