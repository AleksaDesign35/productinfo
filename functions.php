<?php
/*
 * This is the child theme for Hello Elementor theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_action( 'wp_enqueue_scripts', 'proizvod_info_enqueue_styles', 20 );
function proizvod_info_enqueue_styles() {
	$child_style_path = get_stylesheet_directory() . '/style.css';
	$child_style_ver  = file_exists( $child_style_path ) ? filemtime( $child_style_path ) : null;

	wp_enqueue_style( 'proizvod-info-child-style', get_stylesheet_uri(), array(), $child_style_ver );
}

add_theme_support('post-thumbnails');
add_post_type_support('post', 'thumbnail');

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
/*
 * Your code goes below
 */

