<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name           = get_bloginfo( 'name' );
$tagline             = get_bloginfo( 'description', 'display' );
$header_categories   = get_categories(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
	)
);
$current_category_id = 0;

if ( is_category() ) {
	$current_category_id = (int) get_queried_object_id();
} elseif ( is_single() ) {
	$current_post_categories = get_the_category();
	if ( ! empty( $current_post_categories ) ) {
		$current_category_id = (int) $current_post_categories[0]->term_id;
	}
}

$logo_abs  = content_url( 'uploads/2026/03/proizvod-info.svg' );
$logo_src  = wp_parse_url( $logo_abs, PHP_URL_PATH );
if ( ! is_string( $logo_src ) || $logo_src === '' ) {
	$logo_src = '/wp-content/uploads/2026/03/proizvod-info.svg';
}

$menu_locations    = get_nav_menu_locations();
$primary_menu_id   = isset( $menu_locations['menu-1'] ) ? (int) $menu_locations['menu-1'] : 0;
$primary_menu_items = $primary_menu_id ? wp_get_nav_menu_items( $primary_menu_id ) : array();
$menu_children     = array();

if ( $primary_menu_items ) {
	foreach ( $primary_menu_items as $menu_item ) {
		$parent_id = (int) $menu_item->menu_item_parent;
		if ( ! isset( $menu_children[ $parent_id ] ) ) {
			$menu_children[ $parent_id ] = array();
		}
		$menu_children[ $parent_id ][] = $menu_item;
	}
}

$render_primary_nav = static function ( $parent_id = 0 ) use ( &$render_primary_nav, $menu_children ) {
	if ( empty( $menu_children[ $parent_id ] ) ) {
		return '';
	}

	$html = $parent_id === 0 ? '<ul class="pi-site-nav__list">' : '<ul class="sub-menu">';

	foreach ( $menu_children[ $parent_id ] as $menu_item ) {
		if ( $parent_id === 0 && untrailingslashit( $menu_item->url ) === untrailingslashit( home_url( '/' ) ) ) {
			continue;
		}

		if ( $parent_id === 0 && $menu_item->object === 'category' ) {
			continue;
		}

		$item_classes = array( 'menu-item' );
		if ( ! empty( $menu_item->classes ) && is_array( $menu_item->classes ) ) {
			foreach ( $menu_item->classes as $menu_class ) {
				$menu_class = trim( (string) $menu_class );
				if ( $menu_class !== '' ) {
					$item_classes[] = $menu_class;
				}
			}
		}

		$children_markup = $render_primary_nav( (int) $menu_item->ID );
		if ( $children_markup !== '' ) {
			$item_classes[] = 'menu-item-has-children';
		}

		$html .= '<li class="' . esc_attr( implode( ' ', array_unique( $item_classes ) ) ) . '">';
		$html .= '<a href="' . esc_url( $menu_item->url ) . '">' . esc_html( $menu_item->title ) . '</a>';
		$html .= $children_markup;
		$html .= '</li>';
	}

	$html .= '</ul>';

	return $html;
};

$primary_nav_markup = $render_primary_nav();
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
				<button type="button" class="pi-site-header__toggle" aria-expanded="false" aria-controls="pi-site-main-nav" aria-label="<?php echo esc_attr__( 'Menu', 'proizvod-info' ); ?>">
					<span class="pi-site-header__icon-wrap pi-site-header__icon-wrap--menu" aria-hidden="true"><i data-lucide="menu"></i></span>
					<span class="pi-site-header__icon-wrap pi-site-header__icon-wrap--close" aria-hidden="true"><i data-lucide="x"></i></span>
				</button>
				<nav id="pi-site-main-nav" class="pi-site-header__nav" aria-label="<?php echo esc_attr__( 'Main menu', 'hello-elementor' ); ?>">
					<button type="button" class="pi-site-header__drawer-close" aria-label="<?php echo esc_attr__( 'Zatvori meni', 'proizvod-info' ); ?>">
						<i data-lucide="x"></i>
					</button>
					<ul class="pi-site-nav__list">
						<li class="menu-item<?php echo is_front_page() ? ' current-menu-item' : ''; ?>">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Početna', 'proizvod-info' ); ?></a>
						</li>
						<?php foreach ( $header_categories as $header_category ) : ?>
							<li class="menu-item<?php echo $current_category_id === (int) $header_category->term_id ? ' current-menu-item' : ''; ?>">
								<a href="<?php echo esc_url( get_category_link( $header_category->term_id ) ); ?>"><?php echo esc_html( $header_category->name ); ?></a>
							</li>
						<?php endforeach; ?>
						<?php if ( $primary_nav_markup ) : ?>
							<?php
							$primary_nav_markup = preg_replace( '/^<ul class="pi-site-nav__list">/', '', $primary_nav_markup );
							$primary_nav_markup = preg_replace( '/<\/ul>$/', '', (string) $primary_nav_markup );
							echo $primary_nav_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						<?php endif; ?>
					</ul>
				</nav>
				<button type="button" class="pi-site-header__backdrop" aria-hidden="true" tabindex="-1"></button>
			</div>
		</div>
	</div>
</header>
