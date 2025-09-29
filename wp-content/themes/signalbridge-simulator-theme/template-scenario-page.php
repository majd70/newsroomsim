
<?php
/* Template Name: Scenario Page (by slug) */
get_header();
$slug = basename( get_permalink() ); // use page slug as scenario slug
?>
<h1 class="sb-title">Exercise: <?php echo esc_html( $slug ); ?></h1>
<?php
$args = [
  'post_type' => ['news_article','social_post'],
  'posts_per_page' => 12,
  'tax_query' => [[
    'taxonomy' => 'scenario',
    'field' => 'slug',
    'terms' => $slug
  ]]
];
$q = new WP_Query($args);
echo '<div class="sb-grid">';
if ($q->have_posts()) {
  while ($q->have_posts()) { $q->the_post();
    if ( get_post_type() === 'news_article' ) {
        get_template_part('partials/card','news');
    } else {
        get_template_part('partials/card','social');
    }
  }
  wp_reset_postdata();
} else {
  echo '<p>No posts tied to this exercise yet.</p>';
}
echo '</div>';
get_footer();
