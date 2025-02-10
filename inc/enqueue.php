<?php
/**
 * Understrap enqueue scripts
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue scripts and styles.
 */
function blankslate_scripts() {

	// _s defaults
	wp_enqueue_style( 'blankslate-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'blankslate-style', 'rtl', 'replace' );
	wp_enqueue_script( 'blankslate-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	// Bootstrap 5
	wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap.min.css' );
	wp_enqueue_script( 'bootstrap', get_stylesheet_directory_uri() . '/js/bootstrap.min.js', array('jquery'), null, true ); 

	// Font awesome 4
	wp_enqueue_style( 'fontawesome', get_stylesheet_directory_uri() . '/css/fontawesome.min.css', array(), filemtime( get_stylesheet_directory() . '/css/fontawesome.min.css' ) );

	// Slick.js 
	wp_enqueue_style( 'slick', get_stylesheet_directory_uri() . '/css/slick.min.css', array(), filemtime( get_stylesheet_directory() . '/vendor/slick/slick.min.css' ) );
	wp_enqueue_script( 'slick', get_stylesheet_directory_uri() . '/js/slick.min.js', array(), filemtime( get_stylesheet_directory() . '/vendor/slick/slick.min.js' ) );

	// Custom stylesheet 
	wp_enqueue_style( 'custom', get_stylesheet_directory_uri() . '/css/custom.min.css', array(), filemtime( get_stylesheet_directory() . '/css/custom.min.css' ) );

	// Custom scripts
	wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/js/custom.min.js', array(), filemtime( get_stylesheet_directory() . '/js/custom.min.js' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

}
add_action( 'wp_enqueue_scripts', 'blankslate_scripts' );