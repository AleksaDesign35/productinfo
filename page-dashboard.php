<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

nocache_headers();

if ( ! headers_sent() ) {
	header( 'X-Robots-Tag: noindex, nofollow', true );
}

get_header();

$dashboard_status = isset( $_GET['pi_dashboard'] ) ? sanitize_key( wp_unslash( $_GET['pi_dashboard'] ) ) : '';
$has_password     = proizvod_info_analytics_has_dashboard_password();
$is_authorized    = proizvod_info_analytics_dashboard_is_authorized();
$stats            = array(
	'total'    => 0,
	'today'    => 0,
	'direct'   => 0,
	'referral' => 0,
);
$visits           = array();

if ( $has_password && $is_authorized ) {
	$stats  = proizvod_info_analytics_get_stats();
	$visits = proizvod_info_analytics_get_visits( 150 );
}
?>
<main class="pi-dashboard">
	<div class="container pi-dashboard__shell">
		<?php if ( ! $has_password ) : ?>
			<section class="pi-dashboard__login">
				<div class="pi-dashboard__login-card">
					<span class="pi-dashboard__eyebrow">Dashboard analitika</span>
					<h1 class="pi-dashboard__title">Dashboard još nije aktiviran.</h1>
					<p class="pi-dashboard__desc">Postavi lozinku u WordPress adminu pod Settings > Dashboard analitika, a pristup će biti spreman na <?php echo esc_html( wp_parse_url( proizvod_info_analytics_get_dashboard_url(), PHP_URL_PATH ) ?: '/dashboard/' ); ?> sa korisničkim imenom <?php echo esc_html( proizvod_info_analytics_dashboard_username() ); ?>.</p>
				</div>
			</section>
		<?php elseif ( ! $is_authorized ) : ?>
			<section class="pi-dashboard__login">
				<div class="pi-dashboard__login-card">
					<span class="pi-dashboard__eyebrow">Dashboard analitika</span>
					<h1 class="pi-dashboard__title">Prijava na dashboard analitike</h1>
					<p class="pi-dashboard__desc">Ovdje vidiš samo jedinstvene posjete: odakle je korisnik došao, na koji link je prvi put sletio i na kojoj stranici je završio posjetu.</p>
					<?php if ( $dashboard_status === 'invalid' ) : ?>
						<p class="pi-dashboard__message pi-dashboard__message--error">Korisničko ime ili lozinka nisu ispravni.</p>
					<?php elseif ( $dashboard_status === 'logged_out' ) : ?>
						<p class="pi-dashboard__message pi-dashboard__message--info">Odjavljen si sa dashboarda.</p>
					<?php elseif ( $dashboard_status === 'unavailable' ) : ?>
						<p class="pi-dashboard__message pi-dashboard__message--error">Dashboard još nema postavljenu lozinku u WordPress adminu.</p>
					<?php endif; ?>
					<form class="pi-dashboard__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'pi_dashboard_login', 'pi_dashboard_login_nonce' ); ?>
						<input type="hidden" name="action" value="pi_dashboard_login" />
						<input type="hidden" name="redirect_to" value="<?php echo esc_url( proizvod_info_analytics_get_dashboard_url() ); ?>" />
						<label class="pi-dashboard__field">
							<span class="pi-dashboard__label">Korisničko ime</span>
							<input class="pi-dashboard__input" type="text" name="pi_dashboard_username" value="<?php echo esc_attr( proizvod_info_analytics_dashboard_username() ); ?>" autocomplete="username" />
						</label>
						<label class="pi-dashboard__field">
							<span class="pi-dashboard__label">Lozinka</span>
							<input class="pi-dashboard__input" type="password" name="pi_dashboard_password" autocomplete="current-password" required />
						</label>
						<button class="pi-dashboard__button" type="submit">Otvori dashboard</button>
					</form>
				</div>
			</section>
		<?php else : ?>
			<section class="pi-dashboard__head">
				<div class="pi-dashboard__head-copy">
					<span class="pi-dashboard__eyebrow">Dashboard analitika</span>
					<h1 class="pi-dashboard__title">Jedinstvene posjete sajtu</h1>
					<p class="pi-dashboard__desc">Jedna posjeta predstavlja jednu sesiju pregledavanja unutar 30 minuta aktivnosti. U tabeli vidiš izvor, ulazni link i izlaznu stranicu za svaku jedinstvenu posjetu.</p>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'pi_dashboard_logout', 'pi_dashboard_logout_nonce' ); ?>
					<input type="hidden" name="action" value="pi_dashboard_logout" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( proizvod_info_analytics_get_dashboard_url() ); ?>" />
					<button class="pi-dashboard__button pi-dashboard__button--ghost" type="submit">Odjavi se</button>
				</form>
			</section>

			<section class="pi-dashboard__stats">
				<article class="pi-dashboard__stat">
					<span class="pi-dashboard__stat-label">Ukupno jedinstvenih posjeta</span>
					<strong class="pi-dashboard__stat-value"><?php echo esc_html( number_format_i18n( (int) $stats['total'] ) ); ?></strong>
				</article>
				<article class="pi-dashboard__stat">
					<span class="pi-dashboard__stat-label">Danas</span>
					<strong class="pi-dashboard__stat-value"><?php echo esc_html( number_format_i18n( (int) $stats['today'] ) ); ?></strong>
				</article>
				<article class="pi-dashboard__stat">
					<span class="pi-dashboard__stat-label">Direktan dolazak</span>
					<strong class="pi-dashboard__stat-value"><?php echo esc_html( number_format_i18n( (int) $stats['direct'] ) ); ?></strong>
				</article>
				<article class="pi-dashboard__stat">
					<span class="pi-dashboard__stat-label">Dolazak sa drugih sajtova</span>
					<strong class="pi-dashboard__stat-value"><?php echo esc_html( number_format_i18n( (int) $stats['referral'] ) ); ?></strong>
				</article>
			</section>

			<section class="pi-dashboard__table-card">
				<div class="pi-dashboard__section-head">
					<div>
						<h2 class="pi-dashboard__section-title">Zadnje jedinstvene posjete</h2>
						<p class="pi-dashboard__section-desc">Svaki red je jedna jedinstvena posjeta, bez dupliranja pregleda stranica unutar iste aktivne sesije.</p>
					</div>
				</div>
				<?php if ( ! empty( $visits ) ) : ?>
					<div class="pi-dashboard__table-wrap">
						<table class="pi-dashboard__table">
							<thead>
								<tr>
									<th>Vrijeme</th>
									<th>Izvor</th>
									<th>Ulazna stranica</th>
									<th>Izlazna stranica</th>
									<th>Pregleda</th>
									<th>Trajanje</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $visits as $visit ) : ?>
									<?php
									$source_label = proizvod_info_analytics_get_source_label( $visit );
									$source_url   = isset( $visit->referrer_url ) ? (string) $visit->referrer_url : '';
									$landing_url  = isset( $visit->landing_url ) ? (string) $visit->landing_url : '';
									$exit_url     = isset( $visit->exit_url ) ? (string) $visit->exit_url : '';
									$end_time     = ! empty( $visit->ended_at ) ? $visit->ended_at : $visit->updated_at;
									?>
									<tr>
										<td><?php echo esc_html( mysql2date( 'd.m.Y H:i', (string) $visit->started_at ) ); ?></td>
										<td>
											<?php if ( $source_url ) : ?>
												<a class="pi-dashboard__table-link" href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $source_label ); ?></a>
											<?php else : ?>
												<span class="pi-dashboard__table-text"><?php echo esc_html( $source_label ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ( $landing_url ) : ?>
												<a class="pi-dashboard__table-link" href="<?php echo esc_url( $landing_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( proizvod_info_analytics_format_url_label( $landing_url ) ); ?></a>
											<?php else : ?>
												<span class="pi-dashboard__table-text">N/A</span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ( $exit_url ) : ?>
												<a class="pi-dashboard__table-link" href="<?php echo esc_url( $exit_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( proizvod_info_analytics_format_url_label( $exit_url ) ); ?></a>
											<?php else : ?>
												<span class="pi-dashboard__table-text">N/A</span>
											<?php endif; ?>
										</td>
										<td><?php echo esc_html( number_format_i18n( (int) $visit->page_views ) ); ?></td>
										<td><?php echo esc_html( proizvod_info_analytics_format_duration( $visit->started_at, $end_time ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<p class="pi-dashboard__empty">Još nema zabilježenih posjeta.</p>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
