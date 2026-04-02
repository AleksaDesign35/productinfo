<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$page_title = get_the_title();

if ( ! is_string( $page_title ) || $page_title === '' ) {
	$page_title = __( 'Izjava o affiliate partnerstvu', 'proizvod-info' );
}
?>
<main class="pi-affiliate container">
	<section class="pi-affiliate-hero">
		<div class="pi-affiliate-hero__content">
			<?php proizvod_info_render_breadcrumbs( array( 'modifier' => 'light' ) ); ?>
			<span class="pi-affiliate__eyebrow"><?php esc_html_e( 'Transparentnost', 'proizvod-info' ); ?></span>
			<h1 class="pi-affiliate__title"><?php echo esc_html( $page_title ); ?></h1>
			<p class="pi-affiliate__lead"><?php esc_html_e( 'Na ovoj stranici objašnjavamo kako funkcionišu affiliate poveznice, na koji način ostvarujemo proviziju i zašto nam je važno da taj odnos prema čitateljima bude potpuno jasan i transparentan.', 'proizvod-info' ); ?></p>
		</div>
	</section>

	<section class="pi-affiliate-layout">
		<div class="pi-affiliate-content">
			<article class="pi-affiliate-card pi-affiliate-card--statement">
				<span class="pi-affiliate__eyebrow pi-affiliate__eyebrow--dark"><?php esc_html_e( 'Izjava', 'proizvod-info' ); ?></span>
				<h2 class="pi-affiliate-card__title"><?php esc_html_e( 'Izjava o affiliate partnerstvu', 'proizvod-info' ); ?></h2>
				<p class="pi-affiliate-card__text"><?php esc_html_e( 'Ova stranica sudjeluje u affiliate programima, uključujući Amazon EU Associates program. To znači da na određenim poveznicama možemo ostvariti proviziju ako nakon klika dođe do kvalificirane kupnje na Amazon.de ili povezanim Amazon stranicama.', 'proizvod-info' ); ?></p>
				<p class="pi-affiliate-card__text"><?php esc_html_e( 'Takva provizija ne utiče na cijenu proizvoda za kupca. Ako odlučiš kupiti proizvod putem jedne od affiliate poveznica, cijena ostaje ista, dok nama ta kupnja može pomoći u održavanju, unapređenju i daljnjem razvoju sadržaja na ovoj stranici.', 'proizvod-info' ); ?></p>
				<p class="pi-affiliate-card__text"><?php esc_html_e( 'Važno nam je naglasiti da preporuke, vodiči i poređenja nisu automatski određeni time postoji li affiliate poveznica. Cilj nam je da sadržaj ostane urednički koristan, informativan i relevantan čitatelju, a da eventualna provizija bude podrška radu stranice, a ne zamjena za kvalitetnu procjenu proizvoda.', 'proizvod-info' ); ?></p>
			</article>

			<div class="pi-affiliate-principles">
				<article class="pi-affiliate-card">
					<span class="pi-affiliate-card__icon" aria-hidden="true"><i data-lucide="badge-check"></i></span>
					<h2 class="pi-affiliate-card__title"><?php esc_html_e( 'Preporuke ostaju naš urednički izbor', 'proizvod-info' ); ?></h2>
					<p class="pi-affiliate-card__text"><?php esc_html_e( 'Preporučujemo proizvode za koje vjerujemo da mogu biti korisni našim čitateljima, na osnovu istraživanja, dostupnih informacija, usporedbi i uredničke procjene.', 'proizvod-info' ); ?></p>
				</article>
				<article class="pi-affiliate-card">
					<span class="pi-affiliate-card__icon" aria-hidden="true"><i data-lucide="banknote"></i></span>
					<h2 class="pi-affiliate-card__title"><?php esc_html_e( 'Kupcu se ne povećava cijena', 'proizvod-info' ); ?></h2>
					<p class="pi-affiliate-card__text"><?php esc_html_e( 'Kupnja putem affiliate poveznice ne znači veću cijenu proizvoda za korisnika. Provizija dolazi iz partnerskog programa, a ne iz dodatne naknade koju plaća čitatelj.', 'proizvod-info' ); ?></p>
				</article>
				<article class="pi-affiliate-card">
					<span class="pi-affiliate-card__icon" aria-hidden="true"><i data-lucide="shield-check"></i></span>
					<h2 class="pi-affiliate-card__title"><?php esc_html_e( 'Transparentnost nam je važna', 'proizvod-info' ); ?></h2>
					<p class="pi-affiliate-card__text"><?php esc_html_e( 'Kad god postoji mogućnost da putem poveznice ostvarimo proviziju, želimo da to bude jasno navedeno kako bi korisnik znao kontekst u kojem koristi sadržaj i poveznice na stranici.', 'proizvod-info' ); ?></p>
				</article>
			</div>
		</div>

		<aside class="pi-affiliate-side">
			<div class="pi-affiliate-note">
				<span class="pi-affiliate__eyebrow pi-affiliate__eyebrow--dark"><?php esc_html_e( 'Napomena', 'proizvod-info' ); ?></span>
				<h2 class="pi-affiliate-note__title"><?php esc_html_e( 'Šta ovo znači za čitatelja?', 'proizvod-info' ); ?></h2>
				<ul class="pi-affiliate-note__list">
					<li><?php esc_html_e( 'Možemo zaraditi proviziju na određenim kupnjama.', 'proizvod-info' ); ?></li>
					<li><?php esc_html_e( 'To ne povećava cijenu proizvoda za korisnika.', 'proizvod-info' ); ?></li>
					<li><?php esc_html_e( 'Provizija pomaže održavanju i razvoju sajta.', 'proizvod-info' ); ?></li>
					<li><?php esc_html_e( 'Trudimo se da preporuke ostanu korisne, jasne i poštene.', 'proizvod-info' ); ?></li>
				</ul>
				<p class="pi-affiliate-note__text"><?php esc_html_e( 'Hvala ti na podršci. Ako koristiš naše affiliate poveznice, pomažeš da nastavimo objavljivati nove vodiče, preporuke i informativne članke.', 'proizvod-info' ); ?></p>
			</div>
		</aside>
	</section>
</main>
<?php get_footer(); ?>
