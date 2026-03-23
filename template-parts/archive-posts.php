<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<main class="pi-archive container" id="pi-archive-main">
	<header class="pi-archive__header">
		<?php
		if ( is_home() && ! is_front_page() ) {
			$posts_page_id = (int) get_option( 'page_for_posts' );
			if ( $posts_page_id ) {
				echo '<h1 class="pi-archive__title">' . esc_html( proizvod_info_ucfirst_mb( get_the_title( $posts_page_id ) ) ) . '</h1>';
			} else {
				echo '<h1 class="pi-archive__title">' . esc_html__( 'Blog', 'proizvod-info' ) . '</h1>';
			}
		} elseif ( is_archive() ) {
			the_archive_title( '<h1 class="pi-archive__title">', '</h1>' );
			the_archive_description( '<div class="pi-archive__desc">', '</div>' );
		} else {
			echo '<h1 class="pi-archive__title">' . esc_html__( 'Posts', 'proizvod-info' ) . '</h1>';
		}
		?>
	</header>
	<?php if ( have_posts() ) : ?>
		<ul class="pi-archive__list">
			<?php
			while ( have_posts() ) :
				the_post();
				$pi_title = proizvod_info_ucfirst_mb( get_the_title() );
				?>
				<li class="pi-archive__item">
					<article <?php post_class( 'pi-archive__post' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="pi-archive__thumb-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( $pi_title ); ?>">
								<?php the_post_thumbnail( 'medium_large', array( 'class' => 'pi-archive__thumb' ) ); ?>
							</a>
						<?php endif; ?>
						<a class="pi-archive__link" href="<?php the_permalink(); ?>"><?php echo esc_html( $pi_title ); ?></a>
						<time class="pi-archive__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					</article>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 2,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'class'     => 'pi-archive__pagination',
			)
		);
		?>
	<?php else : ?>
		<p class="pi-archive__empty"><?php esc_html_e( 'No posts found.', 'proizvod-info' ); ?></p>
	<?php endif; ?>
</main>
