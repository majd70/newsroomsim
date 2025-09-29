
<?php get_header();
if ( have_posts() ) : while ( have_posts() ) : the_post();
$platform = get_post_meta(get_the_ID(), '_sb_platform', true) ?: 'tweet';
$avatar = esc_url( get_post_meta(get_the_ID(), '_sb_avatar', true) );
$display = esc_html( get_post_meta(get_the_ID(), '_sb_display', true) );
$handle = esc_html( get_post_meta(get_the_ID(), '_sb_handle', true) );
$timestamp = esc_html( get_post_meta(get_the_ID(), '_sb_timestamp', true) );
$media = esc_url( get_post_meta(get_the_ID(), '_sb_media', true) );
$likes = (int) get_post_meta(get_the_ID(), '_sb_likes', true );
$comments = (int) get_post_meta(get_the_ID(), '_sb_comments', true );
$shares = (int) get_post_meta(get_the_ID(), '_sb_shares', true );
?>
<article class="sb-social sb-<?php echo esc_attr($platform); ?>">
  <header class="sb-social-header">
    <img class="sb-avatar" src="<?php echo $avatar; ?>" alt="avatar"/>
    <div>
      <div class="sb-display"><?php echo $display ? $display : sb_get_byline(); ?></div>
      <div class="sb-handle"><?php echo $handle ? '@'.ltrim($handle,'@') : '@user'; ?> • <?php echo $timestamp ? $timestamp : get_the_time(); ?></div>
    </div>
  </header>

  <div class="sb-social-text">
    <?php the_content(); ?>
  </div>

  <?php if ($media): ?>
    <div class="sb-social-media"><img src="<?php echo $media; ?>" alt=""></div>
  <?php endif; ?>

  <footer class="sb-social-actions">
    <span>❤ <?php echo $likes; ?></span>
    <span>💬 <?php echo $comments; ?></span>
    <span>🔁 <?php echo $shares; ?></span>
  </footer>

  <?php comments_template(); ?>
</article>
<?php endwhile; endif;
get_footer();
