<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );
$tagline   = get_bloginfo( 'description', 'display' );
$logo_abs  = content_url( 'uploads/2026/03/proizvod-info.svg' );
$logo_src  = wp_parse_url( $logo_abs, PHP_URL_PATH );
if ( ! is_string( $logo_src ) || $logo_src === '' ) {
	$logo_src = '/wp-content/uploads/2026/03/proizvod-info.svg';
}
$header_nav_menu = wp_nav_menu(
	array(
		'theme_location'  => 'menu-1',
		'fallback_cb'     => false,
		'container'       => false,
		'echo'            => false,
		'menu_class'      => 'pi-site-nav__list',
		'item_spacing'    => 'discard',
		'depth'           => 3,
	)
);
?>
<header id="site-header" class="site-header pi-site-header">
	<div class="pi-site-header__bar">
		<div class="container">
			<div class="pi-site-header__row">
				<div class="pi-site-header__brand">
					<a class="pi-site-header__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img class="pi-site-header__logo" src="<?php echo esc_url( $logo_src ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" width="303" height="100" decoding="async" />
					</a>
					<?php if ( $tagline ) : ?>
						<p class="pi-site-header__tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $header_nav_menu ) : ?>
					<button type="button" class="pi-site-header__toggle" aria-expanded="false" aria-controls="pi-site-main-nav" aria-label="<?php echo esc_attr__( 'Menu', 'proizvod-info' ); ?>">
						<span class="pi-site-header__icon-wrap pi-site-header__icon-wrap--menu" aria-hidden="true"><i data-lucide="menu"></i></span>
						<span class="pi-site-header__icon-wrap pi-site-header__icon-wrap--close" aria-hidden="true"><i data-lucide="x"></i></span>
					</button>
					<nav id="pi-site-main-nav" class="pi-site-header__nav" aria-label="<?php echo esc_attr__( 'Main menu', 'hello-elementor' ); ?>">
						<?php echo $header_nav_menu; ?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>
