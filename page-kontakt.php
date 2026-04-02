<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contact_status = isset( $_GET['contact_status'] ) ? sanitize_key( wp_unslash( $_GET['contact_status'] ) ) : '';
$page_title     = get_the_title();

if ( ! is_string( $page_title ) || $page_title === '' ) {
	$page_title = __( 'Kontakt', 'proizvod-info' );
}
?>
<main class="pi-contact container">
	<section class="pi-contact-hero">
		<div class="pi-contact-hero__content">
			<span class="pi-contact__eyebrow"><?php esc_html_e( 'Kontakt', 'proizvod-info' ); ?></span>
			<h1 class="pi-contact__title"><?php echo esc_html( $page_title ); ?></h1>
			<p class="pi-contact__lead"><?php esc_html_e( 'Ako imaš pitanje, prijedlog proizvoda za obradu, uočenu grešku ili želiš pokrenuti saradnju, pošalji poruku kroz formu ispod. Poruka stiže direktno u WordPress administraciju sajta kako bi bila pregledana na jednom mjestu.', 'proizvod-info' ); ?></p>
		</div>
	</section>

	<section class="pi-contact-layout">
		<div class="pi-contact-info">
			<article class="pi-contact-card">
				<span class="pi-contact-card__icon" aria-hidden="true"><i data-lucide="message-square-text"></i></span>
				<h2 class="pi-contact-card__title"><?php esc_html_e( 'Opća pitanja', 'proizvod-info' ); ?></h2>
				<p class="pi-contact-card__text"><?php esc_html_e( 'Pošalji poruku ako želiš dodatno pojašnjenje o sadržaju, načinu rada ili nekoj objavljenoj preporuci.', 'proizvod-info' ); ?></p>
			</article>
			<article class="pi-contact-card">
				<span class="pi-contact-card__icon" aria-hidden="true"><i data-lucide="lightbulb"></i></span>
				<h2 class="pi-contact-card__title"><?php esc_html_e( 'Prijedlog teme', 'proizvod-info' ); ?></h2>
				<p class="pi-contact-card__text"><?php esc_html_e( 'Ako postoji kategorija, proizvod ili vodič koji bi vrijedio obraditi, pošalji prijedlog kroz formu.', 'proizvod-info' ); ?></p>
			</article>
			<article class="pi-contact-card">
				<span class="pi-contact-card__icon" aria-hidden="true"><i data-lucide="briefcase-business"></i></span>
				<h2 class="pi-contact-card__title"><?php esc_html_e( 'Saradnja', 'proizvod-info' ); ?></h2>
				<p class="pi-contact-card__text"><?php esc_html_e( 'Za upite vezane uz partnerstva, saradnje i uredničke prijedloge možeš poslati detalje kroz istu kontakt formu.', 'proizvod-info' ); ?></p>
			</article>
		</div>

		<div class="pi-contact-form-card">
			<div class="pi-contact-form-card__head">
				<span class="pi-contact__eyebrow pi-contact__eyebrow--dark"><?php esc_html_e( 'Forma', 'proizvod-info' ); ?></span>
				<h2 class="pi-contact-form-card__title"><?php esc_html_e( 'Pošalji poruku', 'proizvod-info' ); ?></h2>
				<p class="pi-contact-form-card__desc"><?php esc_html_e( 'Unesi osnovne podatke i poruku. Nakon slanja, kontakt poruka će biti spremljena u administraciji sajta.', 'proizvod-info' ); ?></p>
			</div>

			<?php if ( $contact_status === 'success' ) : ?>
				<p class="pi-contact-form-card__msg is-success"><?php esc_html_e( 'Poruka je uspješno poslana.', 'proizvod-info' ); ?></p>
			<?php elseif ( $contact_status === 'invalid' ) : ?>
				<p class="pi-contact-form-card__msg is-error"><?php esc_html_e( 'Provjeri unesene podatke i pokušaj ponovo.', 'proizvod-info' ); ?></p>
			<?php elseif ( $contact_status === 'error' ) : ?>
				<p class="pi-contact-form-card__msg is-error"><?php esc_html_e( 'Došlo je do greške pri slanju poruke. Pokušaj ponovo.', 'proizvod-info' ); ?></p>
			<?php endif; ?>

			<form class="pi-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'pi_contact_submit', 'pi_contact_nonce' ); ?>
				<input type="hidden" name="action" value="pi_contact_submit" />
				<div class="pi-contact-form__row">
					<input class="pi-contact-form__input" type="text" name="pi_contact_name" placeholder="<?php esc_attr_e( 'Ime i prezime', 'proizvod-info' ); ?>" required />
					<input class="pi-contact-form__input" type="email" name="pi_contact_email" placeholder="<?php esc_attr_e( 'Email adresa', 'proizvod-info' ); ?>" required />
				</div>
				<input class="pi-contact-form__input" type="text" name="pi_contact_subject" placeholder="<?php esc_attr_e( 'Tema poruke', 'proizvod-info' ); ?>" />
				<textarea class="pi-contact-form__textarea" name="pi_contact_message" rows="7" placeholder="<?php esc_attr_e( 'Napiši poruku...', 'proizvod-info' ); ?>" required></textarea>
				<button class="pi-contact-form__btn" type="submit"><?php esc_html_e( 'Pošalji poruku', 'proizvod-info' ); ?></button>
			</form>

			<p class="pi-contact-form-card__note"><?php esc_html_e( 'Napomena: poruke se spremaju u WordPress admin pod “Kontakt poruke”.', 'proizvod-info' ); ?></p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
