<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );
$logo_abs  = content_url( 'uploads/2026/03/proizvod-info.svg' );
$logo_src  = wp_parse_url( $logo_abs, PHP_URL_PATH );

if ( ! is_string( $logo_src ) || $logo_src === '' ) {
	$logo_src = '/wp-content/uploads/2026/03/proizvod-info.svg';
}

$footer_quick_links = wp_nav_menu(
	array(
		'theme_location' => 'menu-2',
		'fallback_cb'    => false,
		'container'      => false,
		'echo'           => false,
		'menu_class'     => 'pi-site-footer__list',
		'item_spacing'   => 'discard',
		'depth'          => 1,
	)
);

$footer_categories = wp_nav_menu(
	array(
		'theme_location' => 'menu-3',
		'fallback_cb'    => false,
		'container'      => false,
		'echo'           => false,
		'menu_class'     => 'pi-site-footer__list',
		'item_spacing'   => 'discard',
		'depth'          => 1,
	)
);

$footer_info = wp_nav_menu(
	array(
		'theme_location' => 'menu-4',
		'fallback_cb'    => false,
		'container'      => false,
		'echo'           => false,
		'menu_class'     => 'pi-site-footer__list',
		'item_spacing'   => 'discard',
		'depth'          => 1,
	)
);
?>
<footer id="site-footer" class="site-footer pi-site-footer">
	<div class="pi-site-footer__inner">
		<div class="container">
			<div class="pi-site-footer__grid">
				<div class="pi-site-footer__column pi-site-footer__column--intro">
					<?php if ( $site_name ) : ?>
						<a class="pi-site-footer__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img class="pi-site-footer__logo" src="<?php echo esc_url( $logo_src ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" width="303" height="100" decoding="async" />
						</a>
					<?php endif; ?>
					<p class="pi-site-footer__text">Proizvod Info objavljuje recenzije proizvoda, vodiče za kupnju i usporedbe modela koji pomažu da izbor bude jasniji, brži i sigurniji.</p>
				</div>

				<?php if ( $footer_quick_links ) : ?>
					<div class="pi-site-footer__column">
						<p class="pi-site-footer__heading">Brzi linkovi</p>
						<nav class="pi-site-footer__nav" aria-label="<?php echo esc_attr__( 'Footer quick links', 'proizvod-info' ); ?>">
							<?php echo $footer_quick_links; ?>
						</nav>
					</div>
				<?php endif; ?>

				<?php if ( $footer_categories ) : ?>
					<div class="pi-site-footer__column">
						<p class="pi-site-footer__heading">Kategorije</p>
						<nav class="pi-site-footer__nav" aria-label="<?php echo esc_attr__( 'Footer categories', 'proizvod-info' ); ?>">
							<?php echo $footer_categories; ?>
						</nav>
					</div>
				<?php endif; ?>

				<?php if ( $footer_info ) : ?>
					<div class="pi-site-footer__column">
						<p class="pi-site-footer__heading">Informacije</p>
						<nav class="pi-site-footer__nav" aria-label="<?php echo esc_attr__( 'Footer info', 'proizvod-info' ); ?>">
							<?php echo $footer_info; ?>
						</nav>
					</div>
				<?php endif; ?>
			</div>

			<p class="pi-site-footer__copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>. Sva prava zadržana.</p>
		</div>
	</div>
</footer>
<a class="pi-scroll-top" href="#site-header" aria-label="<?php echo esc_attr__( 'Povratak na vrh', 'proizvod-info' ); ?>">
	<i data-lucide="arrow-up"></i>
</a>