<?php
/**
 * BabaYaga child theme functions.
 *
 * @package BabaYagaChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue parent and child stylesheets.
 */
function babayaga_child_enqueue_styles() {
	$parent_handle = 'hello-elementor-theme-style';
	$parent_path   = get_template_directory() . '/style.css';
	$child_path    = get_stylesheet_directory() . '/style.css';

	$parent_version = file_exists( $parent_path ) ? filemtime( $parent_path ) : wp_get_theme( get_template() )->get( 'Version' );
	$child_version  = file_exists( $child_path ) ? filemtime( $child_path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		$parent_handle,
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_version
	);

	wp_enqueue_style(
		'babayaga-child-theme-style',
		get_stylesheet_uri(),
		array( $parent_handle ),
		$child_version
	);
}
add_action( 'wp_enqueue_scripts', 'babayaga_child_enqueue_styles', 20 );

/**
 * Display typefaces for the premium design layer.
 *
 * One grotesk (Inter) carries the whole system - display, UI and body.
 * Only the four weights actually used are requested.
 */
function babayaga_child_enqueue_fonts() {
	wp_enqueue_style(
		'babayaga-child-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'babayaga_child_enqueue_fonts', 19 );

/**
 * Preconnect to the font host so the stylesheet request starts earlier.
 *
 * @param array  $urls           URLs to print.
 * @param string $relation_type  Relation type being filtered.
 * @return array
 */
function babayaga_child_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'babayaga_child_resource_hints', 10, 2 );

/**
 * Premium interaction layer: header scroll state and scroll reveal.
 *
 * Deferred and dependency-free. Everything it does is an enhancement, so the
 * page renders correctly if it never executes.
 */
function babayaga_child_enqueue_scripts() {
	$path = get_stylesheet_directory() . '/assets/js/premium.js';

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'babayaga-child-premium',
		get_stylesheet_directory_uri() . '/assets/js/premium.js',
		array(),
		filemtime( $path ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'babayaga_child_enqueue_scripts', 20 );

/**
 * Site icon.
 *
 * No icon is set in the Customizer, so browsers fall back to a blank default
 * and the tab is unbranded. The mark ships inside the theme, which keeps it
 * on a theme-only deploy. A Customizer icon, if one is ever set, wins.
 */
function babayaga_child_site_icon() {
	if ( get_option( 'site_icon' ) ) {
		return;
	}

	$uri = get_stylesheet_directory_uri();

	if ( ! file_exists( get_stylesheet_directory() . '/assets/img/favicon-512.png' ) ) {
		return;
	}

	printf( '<link rel="icon" href="%s" sizes="any">' . "
", esc_url( $uri . '/assets/img/favicon-512.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "
", esc_url( $uri . '/assets/img/apple-touch-icon.png' ) );
}
add_action( 'wp_head', 'babayaga_child_site_icon' );

/**
 * Menu building blocks: breakfast data, its renderer, and the Elementor widget.
 */
foreach ( array( 'inc/menu-breakfast.php', 'inc/class-breakfast-widget.php' ) as $babayaga_part ) {
	$babayaga_path = get_stylesheet_directory() . '/' . $babayaga_part;

	if ( file_exists( $babayaga_path ) ) {
		require_once $babayaga_path;
	}
}

unset( $babayaga_part, $babayaga_path );

/**
 * Structural layer: hero content block, photography swap, order bar.
 *
 * Loaded defensively. If the file is missing - for example a deploy that
 * copied functions.php but not its sibling - the site drops back to the
 * stylesheet-only design instead of dying on a missing require.
 */
$babayaga_structural = get_stylesheet_directory() . '/structural.php';

if ( file_exists( $babayaga_structural ) ) {
	require_once $babayaga_structural;
}

unset( $babayaga_structural );
