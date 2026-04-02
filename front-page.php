<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$posts_page_id     = (int) get_option( 'page_for_posts' );
$blog_url          = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/blog/' );
$hero_image        = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
$newsletter_status = isset( $_GET['newsletter_status'] ) ? sanitize_key( wp_unslash( $_GET['newsletter_status'] ) ) : '';
$top_categories    = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'number'     => 4,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$home_posts        = get_posts(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 7,
		'ignore_sticky_posts' => true,
		'suppress_filters'    => false,
	)
);
$featured_post     = ! empty( $home_posts ) ? $home_posts[0] : null;
$latest_posts      = array_slice( $home_posts, $featured_post ? 1 : 0, 6 );
$category_cards    = array();

if ( count( $latest_posts ) < 3 ) {
	$latest_posts = array_slice( $home_posts, 0, 6 );
}

$home_placeholder_image = static function( $label ) {
	$clean_label = trim( wp_strip_all_tags( (string) $label ) );
	if ( function_exists( 'mb_substr' ) ) {
		$clean_label = mb_substr( $clean_label, 0, 30, 'UTF-8' );
	} else {
		$clean_label = substr( $clean_label, 0, 30 );
	}
	if ( $clean_label === '' ) {
		$clean_label = 'Proizvod Info';
	}
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900">'
		. '<rect width="1200" height="900" fill="#101828"/>'
		. '<rect x="72" y="72" width="1056" height="756" rx="34" fill="none" stroke="#f5efe6" stroke-opacity="0.28" stroke-width="2"/>'
		. '<rect x="110" y="120" width="320" height="14" rx="7" fill="#f5efe6" fill-opacity="0.22"/>'
		. '<rect x="110" y="165" width="480" height="46" rx="16" fill="#f5efe6" fill-opacity="0.16"/>'
		. '<rect x="110" y="233" width="520" height="22" rx="11" fill="#f5efe6" fill-opacity="0.16"/>'
		. '<rect x="110" y="274" width="370" height="22" rx="11" fill="#f5efe6" fill-opacity="0.16"/>'
		. '<rect x="110" y="640" width="980" height="120" rx="24" fill="#f5efe6" fill-opacity="0.08"/>'
		. '<text x="110" y="715" fill="#f5efe6" font-family="Arial, Helvetica, sans-serif" font-size="72" letter-spacing="1">'
		. esc_html( $clean_label )
		. '</text>'
		. '</svg>';

	return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode( $svg );
};

$get_post_image = static function( $post, $size = 'large' ) use ( $hero_image, $home_placeholder_image ) {
	if ( ! $post instanceof WP_Post ) {
		return $home_placeholder_image( 'Proizvod Info' );
	}
	$image = get_the_post_thumbnail_url( $post->ID, $size );
	if ( $image ) {
		return $image;
	}
	if ( $hero_image ) {
		return $hero_image;
	}
	return $home_placeholder_image( get_the_title( $post ) );
};

$get_post_kicker = static function( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return __( 'Vodič', 'proizvod-info' );
	}
	$post_categories = get_the_category( $post->ID );
	if ( ! empty( $post_categories ) ) {
		return $post_categories[0]->name;
	}
	return __( 'Vodič', 'proizvod-info' );
};

foreach ( $top_categories as $category ) {
	$category_posts   = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'cat'                 => (int) $category->term_id,
			'suppress_filters'    => false,
		)
	);
	$category_cards[] = array(
		'term' => $category,
		'post' => ! empty( $category_posts ) ? $category_posts[0] : null,
	);
}
?>
<main class="pi-home">
	<section class="pi-home-hero<?php echo $hero_image ? ' has-image' : ''; ?>"<?php echo $hero_image ? ' style="--pi-hero-bg:url(' . esc_url( $hero_image ) . ');"' : ''; ?>>
		<div class="container pi-home-hero__inner">
			<div class="pi-home-hero__content">
				<span class="pi-home-eyebrow">Blog o proizvodima i informativnim vodičima</span>
				<h1 class="pi-home-hero__title">Jasni članci koji pomažu da brže i sigurnije izabereš pravi proizvod.</h1>
				<p class="pi-home-hero__desc">Proizvod Info okuplja vodiče, poređenja i praktične informacije bez suvišne buke. Fokus je na onome što korisniku zaista treba prije kupovine: kontekst, razlike i jasan zaključak.</p>
				<div class="pi-home-hero__actions">
					<a class="pi-home-hero__btn pi-home-hero__btn--primary" href="<?php echo esc_url( $blog_url ); ?>">Pogledaj najnovije članke</a>
				</div>
			</div>

			<?php if ( $featured_post ) : ?>
				<article class="pi-home-featured pi-home-featured--hero">
					<a class="pi-home-featured__media" href="<?php echo esc_url( get_permalink( $featured_post ) ); ?>">
						<img src="<?php echo esc_url( $get_post_image( $featured_post, 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $featured_post ) ); ?>" loading="eager" decoding="async" />
					</a>
					<div class="pi-home-featured__body">
						<div class="pi-home-featured__meta">
							<span>Najnovije</span>
							<span><?php echo esc_html( $get_post_kicker( $featured_post ) ); ?></span>
						</div>
						<h2 class="pi-home-featured__title">
							<a href="<?php echo esc_url( get_permalink( $featured_post ) ); ?>"><?php echo esc_html( get_the_title( $featured_post ) ); ?></a>
						</h2>
						<a class="pi-home-featured__link" href="<?php echo esc_url( get_permalink( $featured_post ) ); ?>">Pročitaj članak</a>
					</div>
				</article>
			<?php endif; ?>
		</div>
	</section>

	<div class="container pi-home__content">
		<section class="pi-home-proof">
			<div class="pi-home-proof__grid">
				<article class="pi-home-proof__card">
					<span class="pi-home-proof__icon" aria-hidden="true"><i data-lucide="badge-check"></i></span>
					<h2 class="pi-home-proof__title">Provjeren pristup</h2>
					<p class="pi-home-proof__text">Teme biramo tako da sadržaj bude koristan i čitljiv, a ne samo tehnički tačan.</p>
				</article>
				<article class="pi-home-proof__card">
					<span class="pi-home-proof__icon" aria-hidden="true"><i data-lucide="scale"></i></span>
					<h2 class="pi-home-proof__title">Jasna poređenja</h2>
					<p class="pi-home-proof__text">Razlike između proizvoda objašnjene su kroz stvarnu upotrebu, cijenu i vrijednost.</p>
				</article>
				<article class="pi-home-proof__card">
					<span class="pi-home-proof__icon" aria-hidden="true"><i data-lucide="refresh-cw"></i></span>
					<h2 class="pi-home-proof__title">Aktuelne informacije</h2>
					<p class="pi-home-proof__text">Sadržaj se redovno osvježava kada tržište donese novu ili bolju opciju.</p>
				</article>
				<article class="pi-home-proof__card">
					<span class="pi-home-proof__icon" aria-hidden="true"><i data-lucide="users"></i></span>
					<h2 class="pi-home-proof__title">Pisano za korisnika</h2>
					<p class="pi-home-proof__text">Tekstovi su kratki, pregledni i korisni i kad nemaš vremena čitati sve detalje.</p>
				</article>
			</div>
		</section>

		<section class="pi-home-section pi-home-latest">
			<div class="pi-home-section__head">
				<div>
					<span class="pi-home-eyebrow">Novi sadržaj</span>
					<h2 class="pi-home-section__title">Najnoviji članci i vodiči</h2>
				</div>
				<a class="pi-home-section__link" href="<?php echo esc_url( $blog_url ); ?>">Otvori blog</a>
			</div>

			<?php if ( ! empty( $latest_posts ) ) : ?>
				<div class="pi-home-latest__grid">
					<?php foreach ( $latest_posts as $latest_post ) : ?>
						<article class="pi-home-article-card">
							<a class="pi-home-article-card__media" href="<?php echo esc_url( get_permalink( $latest_post ) ); ?>">
								<img src="<?php echo esc_url( $get_post_image( $latest_post, 'medium_large' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $latest_post ) ); ?>" loading="lazy" decoding="async" />
							</a>
							<div class="pi-home-article-card__body">
								<p class="pi-home-article-card__meta"><?php echo esc_html( $get_post_kicker( $latest_post ) ); ?> / <?php echo esc_html( get_the_date( 'd.m.Y', $latest_post ) ); ?></p>
								<h3 class="pi-home-article-card__title">
									<a href="<?php echo esc_url( get_permalink( $latest_post ) ); ?>"><?php echo esc_html( get_the_title( $latest_post ) ); ?></a>
								</h3>
								<a class="pi-home-article-card__link" href="<?php echo esc_url( get_permalink( $latest_post ) ); ?>">Otvori članak</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="pi-home-empty">Objave uskoro stižu. Dodaj prve članke i homepage će ih automatski prikazati ovdje.</p>
			<?php endif; ?>
		</section>

		<section class="pi-home-section pi-home-topics">
			<div class="pi-home-section__head">
				<div>
					<span class="pi-home-eyebrow">Kategorije</span>
					<h2 class="pi-home-section__title">Teme koje su trenutno najvažnije</h2>
				</div>
			</div>

			<?php if ( ! empty( $category_cards ) ) : ?>
				<div class="pi-home-topics__grid">
					<?php foreach ( $category_cards as $category_card ) : ?>
						<?php
						$category          = $category_card['term'];
						$category_post     = $category_card['post'];
						$category_image    = $category_post ? $get_post_image( $category_post, 'medium_large' ) : $home_placeholder_image( $category->name );
						$category_desc     = trim( wp_strip_all_tags( category_description( $category->term_id ) ) );
						$category_desc     = $category_desc ? wp_trim_words( $category_desc, 18, '...' ) : 'Pregled najboljih objava, preporuka i informativnih vodiča unutar ove kategorije.';
						$category_post_url = $category_post ? get_permalink( $category_post ) : get_category_link( $category->term_id );
						?>
						<article class="pi-home-topic-card">
							<a class="pi-home-topic-card__media" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
								<img src="<?php echo esc_url( $category_image ); ?>" alt="<?php echo esc_attr( $category->name ); ?>" loading="lazy" decoding="async" />
							</a>
							<div class="pi-home-topic-card__body">
								<div class="pi-home-topic-card__meta">
									<span><?php echo esc_html( number_format_i18n( (int) $category->count ) ); ?> članaka</span>
									<span>Kategorija</span>
								</div>
								<h3 class="pi-home-topic-card__title">
									<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
								</h3>
								<p class="pi-home-topic-card__desc"><?php echo esc_html( $category_desc ); ?></p>
								<div class="pi-home-topic-card__actions">
									<a class="pi-home-topic-card__link" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">Otvori kategoriju</a>
									<a class="pi-home-topic-card__sub-link" href="<?php echo esc_url( $category_post_url ); ?>">Izdvojeni članak</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="pi-home-empty">Kada dodaš kategorije i objave, ovdje će se pojaviti pregled najvažnijih tema.</p>
			<?php endif; ?>
		</section>

		<section class="pi-home-method">
			<div class="pi-home-method__intro">
				<span class="pi-home-eyebrow">Kako radimo</span>
				<h2 class="pi-home-section__title">Sadržaj je strukturiran da pomogne odluci prije kupovine.</h2>
				<p class="pi-home-method__desc">Umjesto previše tehničkih detalja bez konteksta, tekstovi ističu ono što korisniku stvarno pomaže: razlike, prioritete i realan zaključak.</p>
			</div>
			<div class="pi-home-method__grid">
				<article class="pi-home-method__card">
					<span class="pi-home-method__step">01</span>
					<h3 class="pi-home-method__title">Izdvajamo bitno</h3>
					<p class="pi-home-method__text">Svaka tema počinje kriterijima koji su važni pri stvarnoj kupovini.</p>
				</article>
				<article class="pi-home-method__card">
					<span class="pi-home-method__step">02</span>
					<h3 class="pi-home-method__title">Poređenja pretvaramo u zaključak</h3>
					<p class="pi-home-method__text">Ne ostajemo na specifikacijama, nego objašnjavamo šta promjena znači u praksi.</p>
				</article>
				<article class="pi-home-method__card">
					<span class="pi-home-method__step">03</span>
					<h3 class="pi-home-method__title">Dajemo više opcija</h3>
					<p class="pi-home-method__text">Cilj je ponuditi smislen izbor za različite budžete i načine korištenja.</p>
				</article>
			</div>
		</section>

		<section id="newsletter" class="pi-home-newsletter">
			<div class="pi-home-newsletter__box">
				<div class="pi-home-newsletter__content">
					<span class="pi-home-eyebrow">Newsletter</span>
					<h2 class="pi-home-newsletter__title">Prijavi se za kratak pregled novih preporuka i korisnih vodiča.</h2>
					<p class="pi-home-newsletter__desc">Umjesto da provjeravaš sajt svaki put, dobit ćeš najnovije članke, poređenja i informativne teme direktno na email kada objavimo nešto vrijedno čitanja.</p>
					<ul class="pi-home-newsletter__benefits">
						<li>Novi članci i poređenja na jednom mjestu</li>
						<li>Kratke i korisne preporuke bez suvišnog spama</li>
						<li>Pregled tema koje vrijedi otvoriti prije kupovine</li>
					</ul>
				</div>
				<div class="pi-home-newsletter__form-wrap">
					<?php if ( $newsletter_status === 'success' ) : ?>
						<p class="pi-home-newsletter__msg is-success">Uspješno si prijavljen/a na newsletter.</p>
					<?php elseif ( $newsletter_status === 'exists' ) : ?>
						<p class="pi-home-newsletter__msg is-info">Ova email adresa je već prijavljena.</p>
					<?php elseif ( $newsletter_status === 'invalid' || $newsletter_status === 'error' ) : ?>
						<p class="pi-home-newsletter__msg is-error">Provjeri unesene podatke i pokušaj ponovo.</p>
					<?php endif; ?>
					<form class="pi-home-newsletter__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'pi_newsletter_subscribe', 'pi_newsletter_nonce' ); ?>
						<input type="hidden" name="action" value="pi_newsletter_subscribe" />
						<input class="pi-home-newsletter__input" type="text" name="pi_newsletter_name" placeholder="Ime i prezime" required />
						<input class="pi-home-newsletter__input" type="email" name="pi_newsletter_email" placeholder="Email adresa" required />
						<button class="pi-home-newsletter__btn" type="submit">Prijavi se</button>
					</form>
					<p class="pi-home-newsletter__note">Jedan pregled korisnih tema. Bez svakodnevnih poruka.</p>
				</div>
			</div>
		</section>

		<section class="pi-home-faq">
			<div class="pi-home-section__head">
				<div>
					<span class="pi-home-eyebrow">Česta pitanja</span>
					<h2 class="pi-home-section__title">Kratki odgovori prije kupovine</h2>
				</div>
			</div>
			<div class="pi-home-faq__list">
				<article class="pi-home-faq__item">
					<h3 class="pi-home-faq__question">Kako birate proizvode koje predstavljate?</h3>
					<p class="pi-home-faq__answer">Biramo teme koje imaju stvarnu vrijednost za čitatelja i objašnjavamo ih bez nepotrebnog komplikovanja.</p>
				</article>
				<article class="pi-home-faq__item">
					<h3 class="pi-home-faq__question">Da li je sadržaj prilagođen početnicima?</h3>
					<p class="pi-home-faq__answer">Da. Tekstovi su pisani jasno, tako da i korisnik bez puno predznanja može brzo razumjeti glavne razlike.</p>
				</article>
				<article class="pi-home-faq__item">
					<h3 class="pi-home-faq__question">Koliko često osvježavate sadržaj?</h3>
					<p class="pi-home-faq__answer">Kad se tržište promijeni, cilj je da se i preporuke i informativni članci prilagode novoj situaciji.</p>
				</article>
				<article class="pi-home-faq__item">
					<h3 class="pi-home-faq__question">Zašto čitati Proizvod Info prije kupovine?</h3>
					<p class="pi-home-faq__answer">Zato što na jednom mjestu dobijaš pregled, objašnjenje i zaključak bez potrebe da sam skupljaš informacije s više strana.</p>
				</article>
			</div>
		</section>
	</div>
</main>
<?php get_footer(); ?>
