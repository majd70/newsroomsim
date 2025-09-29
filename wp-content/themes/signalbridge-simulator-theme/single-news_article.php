
<?php get_header(); ?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<article class="sb-article">
  <header class="sb-article-header">
    <div class="sb-meta">
      <span class="sb-cat"><?php echo esc_html( join(', ', wp_get_post_terms(get_the_ID(),'category', ['fields'=>'names'])) ); ?></span>
      <span class="sb-time"><?php echo esc_html( get_the_time() ); ?></span>
      <span class="sb-byline">By <?php echo sb_get_byline(); ?></span>
      <?php if ( get_post_meta(get_the_ID(),'_sb_breaking',true) === '1' ): ?>
        <span class="sb-breaking">BREAKING</span>
      <?php endif; ?>
    </div>
    <h1><?php the_title(); ?></h1>
  </header>
  <?php if ( has_post_thumbnail() ) : ?>
    <div class="sb-featured"><?php the_post_thumbnail('large'); ?></div>
  <?php endif; ?>

  <div class="sb-embed-wrap">
    <?php echo sb_news_embed_html(get_the_ID()); ?>
  </div>

  <div class="sb-content">
    <?php the_content(); ?>
  </div>

  <?php comments_template(); ?>
</article>
<?php endwhile; endif; ?>
<?php get_footer(); ?>
