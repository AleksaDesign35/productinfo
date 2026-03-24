<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );
$footer_nav_menu = wp_nav_menu(
	array(
		'theme_location'  => 'menu-2',
		'fallback_cb'     => false,
		'container'       => false,
		'echo'            => false,
		'menu_class'      => 'pi-site-footer-nav__list',
		'item_spacing'    => 'discard',
		'depth'           => 2,
	)
);
?>
<footer id="site-footer" class="site-footer pi-site-footer">
	<div class="container pi-site-footer__inner">
		<div class="pi-site-footer__top">
			<div class="pi-site-footer__brand">
				<?php if ( $site_name ) : ?>
					<a class="pi-site-footer__title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( $site_name ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( $footer_nav_menu ) : ?>
				<nav class="pi-site-footer__nav" aria-label="<?php echo esc_attr__( 'Footer menu', 'hello-elementor' ); ?>">
					<?php echo $footer_nav_menu; ?>
				</nav>
			<?php endif; ?>
		</div>
		<p class="pi-site-footer__copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?></p>
	</div>
</footer>
