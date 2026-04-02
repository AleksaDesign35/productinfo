<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name         = get_bloginfo( 'name' );
$posts_page_id     = (int) get_option( 'page_for_posts' );
$blog_url          = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/blog/' );
$logo_abs          = content_url( 'uploads/2026/03/proizvod-info.svg' );
$logo_src          = wp_parse_url( $logo_abs, PHP_URL_PATH );

if ( ! is_string( $logo_src ) || $logo_src === '' ) {
	$logo_src = '/wp-content/uploads/2026/03/proizvod-info.svg';
}

$footer_categories = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'number'     => 4,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$footer_nav_menu   = wp_nav_menu(
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
	<div class="pi-site-footer__inner">
		<div class="container">
			<div class="pi-site-footer__grid">
				<div class="pi-site-footer__column pi-site-footer__column--intro">
					<?php if ( $site_name ) : ?>
						<a class="pi-site-footer__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img class="pi-site-footer__logo" src="<?php echo esc_url( $logo_src ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" width="303" height="100" decoding="async" />
						</a>
					<?php endif; ?>
					<p class="pi-site-footer__text">Proizvod Info objavljuje vodiče, poređenja i informativne članke koji pomažu da izbor proizvoda bude jasniji, brži i sigurniji.</p>
				</div>

				<div class="pi-site-footer__column">
					<h2 class="pi-site-footer__heading">Brzi linkovi</h2>
					<ul class="pi-site-footer__list">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a></li>
						<li><a href="<?php echo esc_url( $blog_url ); ?>">Blog</a></li>
						<li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Kontakt</a></li>
						<li><a href="<?php echo esc_url( home_url( '/izjava-o-affiliate-partnerstvu/' ) ); ?>">Affiliate partnerstvo</a></li>
						<li><a href="<?php echo esc_url( home_url( '/#newsletter' ) ); ?>">Newsletter</a></li>
					</ul>
				</div>

				<?php if ( ! empty( $footer_categories ) ) : ?>
					<div class="pi-site-footer__column">
						<h2 class="pi-site-footer__heading">Kategorije</h2>
						<ul class="pi-site-footer__list">
							<?php foreach ( $footer_categories as $footer_category ) : ?>
								<li><a href="<?php echo esc_url( get_category_link( $footer_category->term_id ) ); ?>"><?php echo esc_html( $footer_category->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="pi-site-footer__column">
					<h2 class="pi-site-footer__heading">Prati sadržaj</h2>
					<p class="pi-site-footer__text">Za nove objave, preporuke i vodiče prati najnovije članke ili se prijavi na newsletter direktno sa početne stranice.</p>
					<?php if ( $footer_nav_menu ) : ?>
						<nav class="pi-site-footer__nav" aria-label="<?php echo esc_attr__( 'Footer menu', 'hello-elementor' ); ?>">
							<?php echo $footer_nav_menu; ?>
						</nav>
					<?php endif; ?>
				</div>
			</div>
			<p class="pi-site-footer__copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>. Sva prava zadržana.</p>
		</div>
	</div>
</footer>
<a class="pi-scroll-top" href="#site-header" aria-label="<?php echo esc_attr__( 'Povratak na vrh', 'proizvod-info' ); ?>">
	<i data-lucide="arrow-up"></i>
</a>
