
<?php
get_header();
$term = get_queried_object();
echo '<h1 class="sb-title">Scenario: ' . esc_html($term->name) . '</h1>';

$args = [
  'post_type' => ['news_article','social_post'],
  'posts_per_page' => 12,
  'tax_query' => [[
    'taxonomy' => 'scenario',
    'field' => 'slug',
    'terms' => $term->slug
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
  echo '<p>No posts for this scenario.</p>';
}
echo '</div>';
get_footer();
