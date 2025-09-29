
<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="sb-header">
  <div class="sb-container">
    <div class="sb-brand"><a href="<?php echo esc_url(home_url('/')); ?>">Newsroom Simulator</a></div>
    <nav class="sb-nav">
      <?php wp_nav_menu(['theme_location'=>'primary','container'=>false]); ?>
    </nav>
  </div>
</header>
<main class="sb-main sb-container">
