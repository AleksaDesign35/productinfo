<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

while ( have_posts() ) :
	the_post();
	?>
	<main id="content" <?php post_class( 'site-main pi-singular' ); ?>>
		<div class="container pi-singular__header">
			<?php proizvod_info_render_breadcrumbs(); ?>
			<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
				<div class="page-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</div>
			<?php endif; ?>
		</div>
		<div class="page-content">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Stranice:', 'proizvod-info' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>
		<?php if ( comments_open() || get_comments_number() ) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	</main>
	<?php
endwhile;
