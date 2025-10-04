
<?php
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
<article class="sb-card sb-social sb-<?php echo esc_attr($platform); ?>">
  <div class="sb-social-top">
    <img class="sb-avatar" src="<?php echo $avatar; ?>" alt="avatar"/>
    <div class="sb-social-id">
      <div class="sb-display"><?php echo $display ? $display : sb_get_byline(); ?></div>
      <div class="sb-handle"><?php echo $handle ? '@'.ltrim($handle,'@') : '@user'; ?> • <?php echo $timestamp ? $timestamp : get_the_time('M j, H:i'); ?></div>
    </div>
  </div>
  <div class="sb-social-text">
    <a href="<?php the_permalink(); ?>"><?php echo wp_kses_post( wp_trim_words( get_the_content(), 32 ) ); ?></a>
  </div>
  <?php if ($media): ?>
  <div class="sb-social-media"><img src="<?php echo $media; ?>" alt=""></div>
  <?php endif; ?>

</article>
