<?php
/*
 * This is the child theme for Hello Elementor theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_filter( 'hello_elementor_enqueue_style', '__return_false' );
add_filter( 'hello_elementor_enqueue_theme_style', '__return_false' );

add_action( 'wp_enqueue_scripts', 'proizvod_info_disable_hello_theme_css', 100 );
function proizvod_info_disable_hello_theme_css() {
	wp_dequeue_style( 'hello-elementor' );
	wp_dequeue_style( 'hello-elementor-theme-style' );
	wp_dequeue_style( 'hello-elementor-header-footer' );
}

add_action( 'wp_enqueue_scripts', 'proizvod_info_enqueue_styles', 20 );
function proizvod_info_enqueue_styles() {
	$child_style_path = get_stylesheet_directory() . '/style.css';
	$child_style_ver  = file_exists( $child_style_path ) ? filemtime( $child_style_path ) : null;

	wp_enqueue_style( 'proizvod-info-child-style', get_stylesheet_uri(), array(), $child_style_ver );

	$theme_uri = get_stylesheet_directory_uri();
	$lucide_path = get_stylesheet_directory() . '/assets/js/lucide.min.js';
	$pi_header_path = get_stylesheet_directory() . '/assets/js/pi-header.js';
	wp_enqueue_script(
		'proizvod-info-lucide',
		$theme_uri . '/assets/js/lucide.min.js',
		array(),
		file_exists( $lucide_path ) ? filemtime( $lucide_path ) : null,
		true
	);
	wp_enqueue_script(
		'proizvod-info-header',
		$theme_uri . '/assets/js/pi-header.js',
		array( 'proizvod-info-lucide' ),
		file_exists( $pi_header_path ) ? filemtime( $pi_header_path ) : null,
		true
	);
}

add_action( 'after_setup_theme', 'proizvod_info_setup' );
function proizvod_info_setup() {
	register_nav_menus(
		array(
			'menu-1' => __( 'Primary', 'proizvod-info' ),
			'menu-2' => __( 'Footer', 'proizvod-info' ),
		)
	);
	add_theme_support( 'custom-logo' );
	add_theme_support( 'post-thumbnails' );
	add_post_type_support( 'post', 'thumbnail' );
}

function proizvod_info_ucfirst_mb( $str ) {
	if ( $str === '' || $str === null ) {
		return $str;
	}
	if ( function_exists( 'mb_substr' ) ) {
		return mb_strtoupper( mb_substr( $str, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $str, 1, null, 'UTF-8' );
	}
	return ucfirst( $str );
}

add_action( 'pre_get_posts', 'proizvod_info_posts_per_page' );
function proizvod_info_posts_per_page( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_home() && ! $query->is_archive() && ! $query->is_search() ) {
		return;
	}
	$post_type = $query->get( 'post_type' );
	if ( is_array( $post_type ) ) {
		return;
	}
	if ( $post_type && $post_type !== 'post' ) {
		return;
	}
	$query->set( 'posts_per_page', 9 );
}

add_action( 'init', 'proizvod_info_register_newsletter_post_type' );
function proizvod_info_register_newsletter_post_type() {
	register_post_type(
		'pi_newsletter',
		array(
			'labels' => array(
				'name'          => __( 'Newsletter', 'proizvod-info' ),
				'singular_name' => __( 'Newsletter prijava', 'proizvod-info' ),
				'add_new'       => __( 'Dodaj prijavu', 'proizvod-info' ),
				'add_new_item'  => __( 'Dodaj newsletter prijavu', 'proizvod-info' ),
				'edit_item'     => __( 'Uredi prijavu', 'proizvod-info' ),
				'new_item'      => __( 'Nova prijava', 'proizvod-info' ),
				'view_item'     => __( 'Pregled prijave', 'proizvod-info' ),
				'search_items'  => __( 'Pretraži prijave', 'proizvod-info' ),
				'not_found'     => __( 'Nema prijava', 'proizvod-info' ),
				'menu_name'     => __( 'Newsletter', 'proizvod-info' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-email-alt',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}

add_action( 'admin_post_nopriv_pi_newsletter_subscribe', 'proizvod_info_newsletter_subscribe' );
add_action( 'admin_post_pi_newsletter_subscribe', 'proizvod_info_newsletter_subscribe' );
function proizvod_info_newsletter_subscribe() {
	$redirect = wp_get_referer();
	if ( ! $redirect ) {
		$redirect = home_url( '/' );
	}
	if ( ! isset( $_POST['pi_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pi_newsletter_nonce'] ) ), 'pi_newsletter_subscribe' ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter_status', 'error', $redirect ) );
		exit;
	}
	$full_name = isset( $_POST['pi_newsletter_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pi_newsletter_name'] ) ) : '';
	$email     = isset( $_POST['pi_newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['pi_newsletter_email'] ) ) : '';
	if ( $full_name === '' || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter_status', 'invalid', $redirect ) );
		exit;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'pi_newsletter',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'pi_newsletter_email',
					'value' => $email,
				),
			),
		)
	);
	if ( ! empty( $existing ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter_status', 'exists', $redirect ) );
		exit;
	}
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'pi_newsletter',
			'post_status' => 'publish',
			'post_title'  => $full_name,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter_status', 'error', $redirect ) );
		exit;
	}
	update_post_meta( $post_id, 'pi_newsletter_email', $email );
	wp_safe_redirect( add_query_arg( 'newsletter_status', 'success', $redirect ) );
	exit;
}

add_filter( 'manage_pi_newsletter_posts_columns', 'proizvod_info_newsletter_columns' );
function proizvod_info_newsletter_columns( $columns ) {
	return array(
		'cb'    => $columns['cb'],
		'title' => __( 'Ime i prezime', 'proizvod-info' ),
		'email' => __( 'Email', 'proizvod-info' ),
		'date'  => $columns['date'],
	);
}

add_action( 'manage_pi_newsletter_posts_custom_column', 'proizvod_info_newsletter_column_value', 10, 2 );
function proizvod_info_newsletter_column_value( $column, $post_id ) {
	if ( $column === 'email' ) {
		echo esc_html( (string) get_post_meta( $post_id, 'pi_newsletter_email', true ) );
	}
}

add_action( 'init', 'proizvod_info_register_contact_post_type' );
function proizvod_info_register_contact_post_type() {
	register_post_type(
		'pi_contact_message',
		array(
			'labels' => array(
				'name'          => __( 'Kontakt poruke', 'proizvod-info' ),
				'singular_name' => __( 'Kontakt poruka', 'proizvod-info' ),
				'add_new'       => __( 'Dodaj poruku', 'proizvod-info' ),
				'add_new_item'  => __( 'Dodaj kontakt poruku', 'proizvod-info' ),
				'edit_item'     => __( 'Uredi poruku', 'proizvod-info' ),
				'new_item'      => __( 'Nova poruka', 'proizvod-info' ),
				'view_item'     => __( 'Pregled poruke', 'proizvod-info' ),
				'search_items'  => __( 'Pretraži poruke', 'proizvod-info' ),
				'not_found'     => __( 'Nema kontakt poruka', 'proizvod-info' ),
				'menu_name'     => __( 'Kontakt poruke', 'proizvod-info' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'supports'            => array( 'title', 'editor' ),
			'has_archive'         => false,
			'menu_position'       => 27,
			'menu_icon'           => 'dashicons-email',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}

add_action( 'admin_post_nopriv_pi_contact_submit', 'proizvod_info_contact_submit' );
add_action( 'admin_post_pi_contact_submit', 'proizvod_info_contact_submit' );
function proizvod_info_contact_submit() {
	$redirect = wp_get_referer();
	if ( ! $redirect ) {
		$redirect = home_url( '/kontakt/' );
	}
	if ( ! isset( $_POST['pi_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pi_contact_nonce'] ) ), 'pi_contact_submit' ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect ) );
		exit;
	}

	$full_name = isset( $_POST['pi_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pi_contact_name'] ) ) : '';
	$email     = isset( $_POST['pi_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['pi_contact_email'] ) ) : '';
	$subject   = isset( $_POST['pi_contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['pi_contact_subject'] ) ) : '';
	$message   = isset( $_POST['pi_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pi_contact_message'] ) ) : '';

	if ( $full_name === '' || ! is_email( $email ) || $message === '' ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'invalid', $redirect ) );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'pi_contact_message',
			'post_status'  => 'publish',
			'post_title'   => $full_name,
			'post_content' => $message,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'contact_status', 'error', $redirect ) );
		exit;
	}

	update_post_meta( $post_id, 'pi_contact_email', $email );
	update_post_meta( $post_id, 'pi_contact_subject', $subject );

	wp_safe_redirect( add_query_arg( 'contact_status', 'success', $redirect ) );
	exit;
}

add_filter( 'manage_pi_contact_message_posts_columns', 'proizvod_info_contact_columns' );
function proizvod_info_contact_columns( $columns ) {
	return array(
		'cb'      => $columns['cb'],
		'title'   => __( 'Ime i prezime', 'proizvod-info' ),
		'email'   => __( 'Email', 'proizvod-info' ),
		'subject' => __( 'Tema', 'proizvod-info' ),
		'date'    => $columns['date'],
	);
}

add_action( 'manage_pi_contact_message_posts_custom_column', 'proizvod_info_contact_column_value', 10, 2 );
function proizvod_info_contact_column_value( $column, $post_id ) {
	if ( $column === 'email' ) {
		echo esc_html( (string) get_post_meta( $post_id, 'pi_contact_email', true ) );
	}

	if ( $column === 'subject' ) {
		$subject = (string) get_post_meta( $post_id, 'pi_contact_subject', true );
		echo esc_html( $subject !== '' ? $subject : __( 'Bez teme', 'proizvod-info' ) );
	}
}

function proizvod_info_required_pages() {
	$pages = array();
	$files = glob( trailingslashit( get_stylesheet_directory() ) . 'page-*.php' );

	if ( ! empty( $files ) ) {
		foreach ( $files as $file ) {
			$basename = basename( $file );
			if ( $basename === 'page.php' ) {
				continue;
			}

			$slug = sanitize_title( substr( $basename, 5, -4 ) );
			if ( $slug === '' ) {
				continue;
			}

			$title = str_replace( '-', ' ', $slug );
			$title = proizvod_info_ucfirst_mb( ucwords( $title ) );

			$pages[ $slug ] = array(
				'slug'  => $slug,
				'title' => $title,
				'file'  => $basename,
			);
		}
	}

	return apply_filters( 'proizvod_info_required_pages', $pages );
}

function proizvod_info_missing_required_pages() {
	$missing_pages = array();

	foreach ( proizvod_info_required_pages() as $required_page ) {
		$page = get_page_by_path( $required_page['slug'], OBJECT, 'page' );
		if ( ! $page instanceof WP_Post || $page->post_status !== 'publish' ) {
			$required_page['page_id']      = $page instanceof WP_Post ? (int) $page->ID : 0;
			$required_page['post_status']  = $page instanceof WP_Post ? $page->post_status : '';
			$missing_pages[]               = $required_page;
		}
	}

	return $missing_pages;
}

add_action( 'admin_init', 'proizvod_info_handle_required_pages_request' );
function proizvod_info_handle_required_pages_request() {
	if ( ! is_admin() || ! current_user_can( 'publish_pages' ) ) {
		return;
	}

	if ( ! isset( $_GET['pi_create_required_pages'] ) ) {
		return;
	}

	check_admin_referer( 'pi_create_required_pages' );

	$processed = 0;
	foreach ( proizvod_info_missing_required_pages() as $required_page ) {
		if ( ! empty( $required_page['page_id'] ) ) {
			$result = wp_update_post(
				array(
					'ID'          => (int) $required_page['page_id'],
					'post_status' => 'publish',
				),
				true
			);
		} else {
			$result = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $required_page['title'],
					'post_name'   => $required_page['slug'],
				),
				true
			);
		}

		if ( ! is_wp_error( $result ) ) {
			$processed++;
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type'                 => 'page',
				'pi_required_pages_created' => $processed,
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}

add_action( 'admin_notices', 'proizvod_info_required_pages_notice' );
function proizvod_info_required_pages_notice() {
	if ( ! is_admin() || ! current_user_can( 'publish_pages' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ! in_array( $screen->base, array( 'dashboard', 'edit', 'themes' ), true ) ) {
		return;
	}

	if ( isset( $_GET['pi_required_pages_created'] ) ) {
		$created = absint( $_GET['pi_required_pages_created'] );
		if ( $created > 0 ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html( sprintf( _n( 'Kreirana je ili objavljena %d potrebna stranica.', 'Kreirano je ili objavljeno %d potrebnih stranica.', $created, 'proizvod-info' ), $created ) ); ?></p>
			</div>
			<?php
		}
	}

	$missing_pages = proizvod_info_missing_required_pages();
	if ( empty( $missing_pages ) ) {
		return;
	}

	$create_url = wp_nonce_url(
		add_query_arg(
			array(
				'post_type'                => 'page',
				'pi_create_required_pages' => 1,
			),
			admin_url( 'edit.php' )
		),
		'pi_create_required_pages'
	);
	?>
	<div class="notice notice-warning">
		<p><strong><?php esc_html_e( 'Postoje kodirane stranice koje još nisu kreirane ili objavljene u WordPressu.', 'proizvod-info' ); ?></strong></p>
		<p><?php esc_html_e( 'Datoteke tipa page-*.php trebaju odgovarajuću WordPress stranicu sa istim slugom da bi bile vidljive na sajtu.', 'proizvod-info' ); ?></p>
		<ul style="list-style:disc;padding-left:1.25rem;">
			<?php foreach ( $missing_pages as $missing_page ) : ?>
				<li>
					<?php
					echo esc_html(
						sprintf(
							'%1$s (%2$s)',
							$missing_page['title'],
							$missing_page['slug']
						)
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
		<p><a class="button button-primary" href="<?php echo esc_url( $create_url ); ?>"><?php esc_html_e( 'Kreiraj / objavi potrebne stranice', 'proizvod-info' ); ?></a></p>
	</div>
	<?php
}

add_filter( 'the_content', 'proizvod_info_append_post_bottom_section' );
function proizvod_info_append_post_bottom_section( $content ) {
	if ( is_admin() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$post_id    = get_the_ID();
	$permalink  = get_permalink( $post_id );
	$post_title = get_the_title( $post_id );
	$share_text = rawurlencode( $post_title );
	$share_url  = rawurlencode( $permalink );
	$share_links = array(
		array( 'label' => 'Facebook', 'icon' => 'share-2', 'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url ),
		array( 'label' => 'X', 'icon' => 'send', 'url' => 'https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_text ),
		array( 'label' => 'Pinterest', 'icon' => 'pin', 'url' => 'https://pinterest.com/pin/create/button/?url=' . $share_url . '&description=' . $share_text ),
		array( 'label' => 'LinkedIn', 'icon' => 'link-2', 'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url ),
		array( 'label' => 'Viber', 'icon' => 'message-circle', 'url' => 'viber://forward?text=' . $share_text . '%20' . $share_url ),
		array( 'label' => 'Reddit', 'icon' => 'globe', 'url' => 'https://www.reddit.com/submit?url=' . $share_url . '&title=' . $share_text ),
		array( 'label' => 'WhatsApp', 'icon' => 'message-circle-more', 'url' => 'https://wa.me/?text=' . $share_text . '%20' . $share_url ),
	);
	$prev_post = get_previous_post();
	$next_post = get_next_post();
	ob_start();
	?>
	<section class="pi-post-bottom">
		<h2 class="pi-post-bottom__share-title">Ako ti se članak svidio, podijeli ga!</h2>
		<div class="pi-post-bottom__share-grid">
			<?php foreach ( $share_links as $link ) : ?>
				<a class="pi-post-bottom__share-link" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $link['label'] ); ?>">
					<i data-lucide="<?php echo esc_attr( $link['icon'] ); ?>"></i>
				</a>
			<?php endforeach; ?>
		</div>
		<div class="pi-post-bottom__nav">
			<?php if ( $next_post ) : ?>
				<a class="pi-post-bottom__card" href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">
					<?php if ( has_post_thumbnail( $next_post ) ) : ?>
						<span class="pi-post-bottom__thumb"><?php echo get_the_post_thumbnail( $next_post, 'thumbnail' ); ?></span>
					<?php endif; ?>
					<span class="pi-post-bottom__meta">
						<span class="pi-post-bottom__label">SLIJEDEĆA OBJAVA</span>
						<span class="pi-post-bottom__name"><?php echo esc_html( get_the_title( $next_post ) ); ?></span>
					</span>
				</a>
			<?php endif; ?>
			<?php if ( $prev_post ) : ?>
				<a class="pi-post-bottom__card pi-post-bottom__card--right" href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>">
					<?php if ( has_post_thumbnail( $prev_post ) ) : ?>
						<span class="pi-post-bottom__thumb"><?php echo get_the_post_thumbnail( $prev_post, 'thumbnail' ); ?></span>
					<?php endif; ?>
					<span class="pi-post-bottom__meta">
						<span class="pi-post-bottom__label">PRETHODNA OBJAVA</span>
						<span class="pi-post-bottom__name"><?php echo esc_html( get_the_title( $prev_post ) ); ?></span>
					</span>
				</a>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return $content . ob_get_clean();
}

/*
 * Your code goes below
 */
