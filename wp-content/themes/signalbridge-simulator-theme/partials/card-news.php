
<article class="sb-card sb-news">
  <a href="<?php the_permalink(); ?>" class="sb-card-media">
    <?php if ( has_post_thumbnail() ) { the_post_thumbnail('medium'); } ?>
    <?php if ( get_post_meta(get_the_ID(), '_sb_breaking', true) === '1' ) : ?>
      <span class="sb-badge">BREAKING</span>
    <?php endif; ?>
  </a>
  <div class="sb-card-body">
    <div class="sb-card-meta">
      <span class="sb-card-cat"><?php echo esc_html( join(', ', wp_list_pluck( get_the_terms(get_the_ID(),'scenario') ?: [], 'name')) ); ?></span>
      <span class="sb-card-time"><?php echo esc_html( get_the_time('M j, H:i') ); ?></span>
    </div>
    <h3 class="sb-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <div class="sb-card-byline">By <?php echo sb_get_byline(); ?></div>
    <div class="sb-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 22 ) ); ?></div>
    <div class="sb-card-flags">
      <?php if ( get_post_meta(get_the_ID(), '_sb_embed_url', true) ) : ?>
        <span class="sb-flag">▶ watch</span>
      <?php endif; ?>
    </div>
  </div>
</article>
