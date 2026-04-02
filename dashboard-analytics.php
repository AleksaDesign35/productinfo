<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function proizvod_info_analytics_table_name() {
	global $wpdb;

	return $wpdb->prefix . 'pi_analytics_visits';
}

function proizvod_info_analytics_db_version() {
	return '1.0';
}

function proizvod_info_analytics_password_option_name() {
	return 'proizvod_info_dashboard_password_hash';
}

function proizvod_info_analytics_cookie_name() {
	return 'pi_dashboard_auth';
}

function proizvod_info_analytics_dashboard_username() {
	return 'AleksaAdmin';
}

function proizvod_info_analytics_visit_window_seconds() {
	return 1800;
}

function proizvod_info_analytics_get_dashboard_page() {
	$page = get_page_by_path( 'dashboard', OBJECT, 'page' );

	return $page instanceof WP_Post ? $page : null;
}

function proizvod_info_analytics_get_dashboard_url() {
	$page = proizvod_info_analytics_get_dashboard_page();

	if ( $page instanceof WP_Post && $page->post_status === 'publish' ) {
		$url = get_permalink( $page );

		if ( is_string( $url ) && $url !== '' ) {
			return $url;
		}
	}

	return home_url( '/dashboard/' );
}

function proizvod_info_analytics_get_dashboard_password_hash() {
	return (string) get_option( proizvod_info_analytics_password_option_name(), '' );
}

function proizvod_info_analytics_has_dashboard_password() {
	return proizvod_info_analytics_get_dashboard_password_hash() !== '';
}

function proizvod_info_analytics_get_safe_dashboard_redirect( $redirect = '' ) {
	return wp_validate_redirect( is_string( $redirect ) ? $redirect : '', proizvod_info_analytics_get_dashboard_url() );
}

function proizvod_info_analytics_sanitize_token( $token ) {
	$token = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $token );
	$token = strtolower( (string) $token );

	return substr( $token, 0, 64 );
}

function proizvod_info_analytics_normalize_url( $url, $allow_external = false ) {
	$url = esc_url_raw( trim( (string) $url ) );

	if ( $url === '' ) {
		return '';
	}

	if ( $allow_external ) {
		return $url;
	}

	$site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$url_host  = wp_parse_url( $url, PHP_URL_HOST );

	if ( is_string( $site_host ) && $site_host !== '' && is_string( $url_host ) && $url_host !== '' && strtolower( $site_host ) !== strtolower( $url_host ) ) {
		return '';
	}

	return $url;
}

function proizvod_info_analytics_get_cookie_signature( $expires ) {
	$password_hash = proizvod_info_analytics_get_dashboard_password_hash();

	if ( $password_hash === '' ) {
		return '';
	}

	return hash_hmac(
		'sha256',
		proizvod_info_analytics_dashboard_username() . '|' . (int) $expires . '|' . $password_hash,
		wp_salt( 'auth' )
	);
}

function proizvod_info_analytics_set_dashboard_cookie( $expires ) {
	$expires = (int) $expires;

	if ( $expires <= time() ) {
		return;
	}

	$signature = proizvod_info_analytics_get_cookie_signature( $expires );

	if ( $signature === '' ) {
		return;
	}

	$value = $expires . '|' . $signature;

	setcookie(
		proizvod_info_analytics_cookie_name(),
		$value,
		array(
			'expires'  => $expires,
			'path'     => '/',
			'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	$_COOKIE[ proizvod_info_analytics_cookie_name() ] = $value;
}

function proizvod_info_analytics_clear_dashboard_cookie() {
	setcookie(
		proizvod_info_analytics_cookie_name(),
		'',
		array(
			'expires'  => time() - HOUR_IN_SECONDS,
			'path'     => '/',
			'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	unset( $_COOKIE[ proizvod_info_analytics_cookie_name() ] );
}

function proizvod_info_analytics_dashboard_is_authorized() {
	$password_hash = proizvod_info_analytics_get_dashboard_password_hash();

	if ( $password_hash === '' ) {
		return false;
	}

	$cookie = isset( $_COOKIE[ proizvod_info_analytics_cookie_name() ] ) ? (string) wp_unslash( $_COOKIE[ proizvod_info_analytics_cookie_name() ] ) : '';

	if ( $cookie === '' || strpos( $cookie, '|' ) === false ) {
		return false;
	}

	list( $expires, $signature ) = explode( '|', $cookie, 2 );

	if ( ! ctype_digit( $expires ) ) {
		return false;
	}

	$expires = (int) $expires;

	if ( $expires <= time() ) {
		return false;
	}

	$expected_signature = proizvod_info_analytics_get_cookie_signature( $expires );

	if ( $expected_signature === '' ) {
		return false;
	}

	return hash_equals( $expected_signature, $signature );
}

function proizvod_info_analytics_ensure_dashboard_page() {
	$page = proizvod_info_analytics_get_dashboard_page();

	if ( $page instanceof WP_Post ) {
		if ( $page->post_status !== 'publish' ) {
			wp_update_post(
				array(
					'ID'          => (int) $page->ID,
					'post_status' => 'publish',
				)
			);
		}

		return (int) $page->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Dashboard', 'proizvod-info' ),
			'post_name'   => 'dashboard',
		),
		true
	);

	return is_wp_error( $page_id ) ? 0 : (int) $page_id;
}

add_action( 'init', 'proizvod_info_analytics_maybe_create_table', 1 );
function proizvod_info_analytics_maybe_create_table() {
	if ( get_option( 'proizvod_info_analytics_db_version' ) === proizvod_info_analytics_db_version() ) {
		return;
	}

	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name      = proizvod_info_analytics_table_name();
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = "CREATE TABLE {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		visit_token varchar(64) NOT NULL,
		started_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		ended_at datetime NULL DEFAULT NULL,
		referrer_url text NULL,
		referrer_host varchar(191) NOT NULL DEFAULT '',
		landing_url text NOT NULL,
		exit_url text NOT NULL,
		page_views int(10) unsigned NOT NULL DEFAULT 1,
		PRIMARY KEY  (id),
		UNIQUE KEY visit_token (visit_token),
		KEY started_at (started_at),
		KEY referrer_host (referrer_host)
	) {$charset_collate};";

	dbDelta( $sql );

	update_option( 'proizvod_info_analytics_db_version', proizvod_info_analytics_db_version(), false );
}

add_action( 'wp_enqueue_scripts', 'proizvod_info_analytics_enqueue_script', 30 );
function proizvod_info_analytics_enqueue_script() {
	if ( is_admin() || is_feed() || is_preview() || is_page( 'dashboard' ) ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/pi-analytics.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'proizvod-info-analytics',
		get_stylesheet_directory_uri() . '/assets/js/pi-analytics.js',
		array(),
		filemtime( $script_path ),
		true
	);

	wp_add_inline_script(
		'proizvod-info-analytics',
		'window.piAnalyticsConfig = ' . wp_json_encode(
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'trackAction' => 'pi_analytics_track',
				'exitAction'  => 'pi_analytics_exit',
				'visitWindow' => proizvod_info_analytics_visit_window_seconds(),
			)
		) . ';',
		'before'
	);
}

function proizvod_info_analytics_get_referrer_host( $referrer_url ) {
	$referrer_host = wp_parse_url( $referrer_url, PHP_URL_HOST );

	return is_string( $referrer_host ) ? strtolower( $referrer_host ) : '';
}

add_action( 'wp_ajax_nopriv_pi_analytics_track', 'proizvod_info_analytics_track_visit' );
add_action( 'wp_ajax_pi_analytics_track', 'proizvod_info_analytics_track_visit' );
function proizvod_info_analytics_track_visit() {
	global $wpdb;

	$visit_token = proizvod_info_analytics_sanitize_token( isset( $_POST['visitToken'] ) ? wp_unslash( $_POST['visitToken'] ) : '' );
	$current_url = proizvod_info_analytics_normalize_url( isset( $_POST['currentUrl'] ) ? wp_unslash( $_POST['currentUrl'] ) : '' );

	if ( $visit_token === '' || $current_url === '' ) {
		wp_die( '0' );
	}

	$referrer_url  = proizvod_info_analytics_normalize_url( isset( $_POST['referrerUrl'] ) ? wp_unslash( $_POST['referrerUrl'] ) : '', true );
	$referrer_host = $referrer_url !== '' ? proizvod_info_analytics_get_referrer_host( $referrer_url ) : '';
	$now           = current_time( 'mysql' );
	$table_name    = proizvod_info_analytics_table_name();
	$existing_id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$table_name} WHERE visit_token = %s LIMIT 1",
			$visit_token
		)
	);

	if ( $existing_id > 0 ) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table_name} SET updated_at = %s, ended_at = NULL, exit_url = %s, page_views = page_views + 1 WHERE id = %d",
				$now,
				$current_url,
				$existing_id
			)
		);

		wp_die( '1' );
	}

	$wpdb->insert(
		$table_name,
		array(
			'visit_token'   => $visit_token,
			'started_at'    => $now,
			'updated_at'    => $now,
			'referrer_url'  => $referrer_url,
			'referrer_host' => $referrer_host,
			'landing_url'   => $current_url,
			'exit_url'      => $current_url,
			'page_views'    => 1,
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' )
	);

	wp_die( '1' );
}

add_action( 'wp_ajax_nopriv_pi_analytics_exit', 'proizvod_info_analytics_track_exit' );
add_action( 'wp_ajax_pi_analytics_exit', 'proizvod_info_analytics_track_exit' );
function proizvod_info_analytics_track_exit() {
	global $wpdb;

	$visit_token = proizvod_info_analytics_sanitize_token( isset( $_POST['visitToken'] ) ? wp_unslash( $_POST['visitToken'] ) : '' );
	$current_url = proizvod_info_analytics_normalize_url( isset( $_POST['currentUrl'] ) ? wp_unslash( $_POST['currentUrl'] ) : '' );

	if ( $visit_token === '' || $current_url === '' ) {
		wp_die( '0' );
	}

	$now        = current_time( 'mysql' );
	$table_name = proizvod_info_analytics_table_name();

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table_name} SET updated_at = %s, ended_at = %s, exit_url = %s WHERE visit_token = %s",
			$now,
			$now,
			$current_url,
			$visit_token
		)
	);

	wp_die( '1' );
}

add_action( 'admin_menu', 'proizvod_info_analytics_register_settings_page' );
function proizvod_info_analytics_register_settings_page() {
	add_options_page(
		__( 'Dashboard analitika', 'proizvod-info' ),
		__( 'Dashboard analitika', 'proizvod-info' ),
		'manage_options',
		'proizvod-info-dashboard-analytics',
		'proizvod_info_analytics_render_settings_page'
	);
}

function proizvod_info_analytics_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$status        = isset( $_GET['pi_dashboard_settings'] ) ? sanitize_key( wp_unslash( $_GET['pi_dashboard_settings'] ) ) : '';
	$dashboard_url = proizvod_info_analytics_get_dashboard_url();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Dashboard analitika', 'proizvod-info' ); ?></h1>
		<?php if ( $status === 'saved' ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Lozinka je spremljena i /dashboard je spreman za korištenje.', 'proizvod-info' ); ?></p></div>
		<?php elseif ( $status === 'unchanged' ) : ?>
			<div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'Lozinka nije mijenjana. Dashboard stranica je provjerena.', 'proizvod-info' ); ?></p></div>
		<?php elseif ( $status === 'empty' ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Unesi lozinku da bi dashboard bio dostupan.', 'proizvod-info' ); ?></p></div>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Dashboard URL', 'proizvod-info' ); ?></th>
					<td><a href="<?php echo esc_url( $dashboard_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $dashboard_url ); ?></a></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Korisničko ime', 'proizvod-info' ); ?></th>
					<td><strong><?php echo esc_html( proizvod_info_analytics_dashboard_username() ); ?></strong></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Status lozinke', 'proizvod-info' ); ?></th>
					<td><?php echo esc_html( proizvod_info_analytics_has_dashboard_password() ? __( 'Postavljena', 'proizvod-info' ) : __( 'Nije postavljena', 'proizvod-info' ) ); ?></td>
				</tr>
			</tbody>
		</table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'pi_dashboard_save_settings', 'pi_dashboard_settings_nonce' ); ?>
			<input type="hidden" name="action" value="pi_dashboard_save_settings" />
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="pi_dashboard_password"><?php esc_html_e( 'Nova lozinka', 'proizvod-info' ); ?></label></th>
						<td>
							<input id="pi_dashboard_password" name="pi_dashboard_password" type="password" class="regular-text" autocomplete="new-password" />
							<p class="description"><?php esc_html_e( 'Ako je polje prazno i lozinka već postoji, ostaje ista.', 'proizvod-info' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button( __( 'Spremi dashboard pristup', 'proizvod-info' ) ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_post_pi_dashboard_save_settings', 'proizvod_info_analytics_save_settings' );
function proizvod_info_analytics_save_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Nemaš dozvolu za ovu akciju.', 'proizvod-info' ) );
	}

	check_admin_referer( 'pi_dashboard_save_settings', 'pi_dashboard_settings_nonce' );

	$password_hash = proizvod_info_analytics_get_dashboard_password_hash();
	$password      = isset( $_POST['pi_dashboard_password'] ) ? trim( (string) wp_unslash( $_POST['pi_dashboard_password'] ) ) : '';
	$status        = 'saved';

	if ( $password === '' ) {
		$status = $password_hash === '' ? 'empty' : 'unchanged';
	} else {
		update_option( proizvod_info_analytics_password_option_name(), wp_hash_password( $password ), false );
	}

	proizvod_info_analytics_ensure_dashboard_page();

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                  => 'proizvod-info-dashboard-analytics',
				'pi_dashboard_settings' => $status,
			),
			admin_url( 'options-general.php' )
		)
	);
	exit;
}

add_action( 'admin_post_nopriv_pi_dashboard_login', 'proizvod_info_analytics_handle_dashboard_login' );
add_action( 'admin_post_pi_dashboard_login', 'proizvod_info_analytics_handle_dashboard_login' );
function proizvod_info_analytics_handle_dashboard_login() {
	check_admin_referer( 'pi_dashboard_login', 'pi_dashboard_login_nonce' );

	$redirect = proizvod_info_analytics_get_safe_dashboard_redirect( isset( $_POST['redirect_to'] ) ? wp_unslash( $_POST['redirect_to'] ) : '' );
	$username = isset( $_POST['pi_dashboard_username'] ) ? sanitize_text_field( wp_unslash( $_POST['pi_dashboard_username'] ) ) : '';
	$password = isset( $_POST['pi_dashboard_password'] ) ? (string) wp_unslash( $_POST['pi_dashboard_password'] ) : '';
	$hash     = proizvod_info_analytics_get_dashboard_password_hash();

	if ( $hash === '' ) {
		wp_safe_redirect( add_query_arg( 'pi_dashboard', 'unavailable', $redirect ) );
		exit;
	}

	if ( $username !== proizvod_info_analytics_dashboard_username() || $password === '' || ! wp_check_password( $password, $hash ) ) {
		wp_safe_redirect( add_query_arg( 'pi_dashboard', 'invalid', $redirect ) );
		exit;
	}

	proizvod_info_analytics_set_dashboard_cookie( time() + DAY_IN_SECONDS );

	wp_safe_redirect( remove_query_arg( 'pi_dashboard', $redirect ) );
	exit;
}

add_action( 'admin_post_nopriv_pi_dashboard_logout', 'proizvod_info_analytics_handle_dashboard_logout' );
add_action( 'admin_post_pi_dashboard_logout', 'proizvod_info_analytics_handle_dashboard_logout' );
function proizvod_info_analytics_handle_dashboard_logout() {
	check_admin_referer( 'pi_dashboard_logout', 'pi_dashboard_logout_nonce' );

	$redirect = proizvod_info_analytics_get_safe_dashboard_redirect( isset( $_POST['redirect_to'] ) ? wp_unslash( $_POST['redirect_to'] ) : '' );

	proizvod_info_analytics_clear_dashboard_cookie();

	wp_safe_redirect( add_query_arg( 'pi_dashboard', 'logged_out', $redirect ) );
	exit;
}

function proizvod_info_analytics_get_stats() {
	global $wpdb;

	$table_name = proizvod_info_analytics_table_name();
	$today      = current_time( 'Y-m-d' );

	return array(
		'total'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ),
		'today'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE DATE(started_at) = %s", $today ) ),
		'direct'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE referrer_host = ''" ),
		'referral' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE referrer_host <> ''" ),
	);
}

function proizvod_info_analytics_get_visits( $limit = 100 ) {
	global $wpdb;

	$limit      = max( 1, min( 500, (int) $limit ) );
	$table_name = proizvod_info_analytics_table_name();

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, started_at, updated_at, ended_at, referrer_url, referrer_host, landing_url, exit_url, page_views FROM {$table_name} ORDER BY started_at DESC LIMIT %d",
			$limit
		)
	);
}

function proizvod_info_analytics_format_duration( $start, $end ) {
	$start_time = strtotime( (string) $start );
	$end_time   = strtotime( (string) $end );

	if ( ! $start_time || ! $end_time || $end_time < $start_time ) {
		return '0s';
	}

	$diff    = $end_time - $start_time;
	$hours   = (int) floor( $diff / HOUR_IN_SECONDS );
	$minutes = (int) floor( ( $diff % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
	$seconds = (int) ( $diff % MINUTE_IN_SECONDS );

	if ( $hours > 0 ) {
		return trim( $hours . 'h ' . $minutes . 'm' );
	}

	if ( $minutes > 0 ) {
		return trim( $minutes . 'm ' . $seconds . 's' );
	}

	return $seconds . 's';
}

function proizvod_info_analytics_get_source_label( $visit ) {
	$referrer_host = isset( $visit->referrer_host ) ? trim( (string) $visit->referrer_host ) : '';

	if ( $referrer_host === '' ) {
		return __( 'Direktno', 'proizvod-info' );
	}

	$site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

	if ( is_string( $site_host ) && $site_host !== '' && strtolower( $referrer_host ) === strtolower( $site_host ) ) {
		return __( 'Interno', 'proizvod-info' );
	}

	return $referrer_host;
}

function proizvod_info_analytics_format_url_label( $url ) {
	$url = trim( (string) $url );

	if ( $url === '' ) {
		return 'N/A';
	}

	$host  = wp_parse_url( $url, PHP_URL_HOST );
	$path  = wp_parse_url( $url, PHP_URL_PATH );
	$query = wp_parse_url( $url, PHP_URL_QUERY );
	$label = is_string( $path ) && $path !== '' ? $path : '/';

	if ( is_string( $query ) && $query !== '' ) {
		$label .= '?' . $query;
	}

	$site_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

	if ( is_string( $host ) && $host !== '' && is_string( $site_host ) && strtolower( $host ) !== strtolower( $site_host ) ) {
		return $host . $label;
	}

	return $label;
}
