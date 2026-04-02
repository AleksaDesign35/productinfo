<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$posts_page_id = (int) get_option( 'page_for_posts' );
$blog_url      = $posts_page_id ? get_permalink( $posts_page_id ) : '';

if ( ! $blog_url ) {
	$blog_url = home_url( '/blog/' );
}

$review_url   = '';
$reviews_page = get_page_by_path( 'recenzije' );

if ( $reviews_page instanceof WP_Post ) {
	$review_url = get_permalink( $reviews_page );
}

if ( ! $review_url && post_type_exists( 'review' ) ) {
	$review_url = get_post_type_archive_link( 'review' );
}

if ( ! $review_url ) {
	$reviews_category = get_category_by_slug( 'recenzije' );
	if ( $reviews_category instanceof WP_Term ) {
		$reviews_category_link = get_category_link( $reviews_category );
		if ( ! is_wp_error( $reviews_category_link ) ) {
			$review_url = $reviews_category_link;
		}
	}
}
?>
<main class="pi-home pi-404">
	<section class="pi-home-hero pi-404__hero">
		<div class="container pi-home-hero__inner">
			<div class="pi-home-hero__content pi-404__content">
				<span class="pi-home-eyebrow">Greška 404</span>
				<p class="pi-404__code">404</p>
				<h1 class="pi-home-hero__title">Stranica koju tražiš nije pronađena.</h1>
				<p class="pi-home-hero__desc">Link je možda pogrešan, zastario ili je sadržaj premješten. Vrati se na početnu ili otvori sekciju koja ti trenutno najviše treba.</p>
				<div class="pi-home-hero__actions pi-404__actions">
					<a class="pi-home-hero__btn pi-home-hero__btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Vrati se na početnu</a>
					<?php if ( $review_url ) : ?>
						<a class="pi-home-hero__btn pi-home-hero__btn--secondary" href="<?php echo esc_url( $review_url ); ?>">Pogledaj recenzije</a>
					<?php endif; ?>
					<a class="pi-home-hero__btn pi-home-hero__btn--secondary" href="<?php echo esc_url( $blog_url ); ?>">Otvori blog</a>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
