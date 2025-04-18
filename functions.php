<?php
/**
 * rslfranchise functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package rslfranchise
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function rslfranchise_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on rslfranchise, use a find and replace
		* to change 'rslfranchise' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'rslfranchise', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'rslfranchise' ),
			'menu-2' => esc_html__( 'Secondary', 'rslfranchise' ),
			'menu-3' => esc_html__( 'Utility', 'rslfranchise' ),
			'menu-4' => esc_html__( 'Instructor', 'rslfranchise' ),
			'menu-5' => esc_html__( 'Learner', 'rslfranchise' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'rslfranchise_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'rslfranchise_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function rslfranchise_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'rslfranchise_content_width', 640 );
}
add_action( 'after_setup_theme', 'rslfranchise_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function rslfranchise_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'rslfranchise' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'rslfranchise' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'rslfranchise_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function rslfranchise_scripts() {
	wp_enqueue_style( 'rslfranchise-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'rslfranchise-style', 'rtl', 'replace' );

	wp_enqueue_script( 'rslfranchise-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'rslfranchise_scripts' );

/**
 * Enqueue scripts and styles.
 */
require get_template_directory() . '/inc/enqueue.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Composer autoload.
 */
require get_template_directory() . '/vendor/autoload.php';

/**
 * Custom user roles.
 */
require get_template_directory() . '/inc/user-roles.php';

/**
 * Custom and misc functions.
 */
require get_template_directory() . '/inc/learndash.php';

/**
 * Wavesurfer.
 */
require get_template_directory() . '/inc/wavesurfer-shortcode.php';

/**
 * Block registration.
 */
require get_template_directory() . '/inc/block-registration.php';

/**
 * Custom post types.
 */
require get_template_directory() . '/inc/cpt-homework.php';

/**
 * Homework functions.
 */
require get_template_directory() . '/inc/homework-functions.php';

/**
 * AWS functions.
 */
require get_template_directory() . '/inc/aws-functions.php';

/**
 * AWS shortcode.
 */
require get_template_directory() . '/inc/aws-shortcode.php';

/**
 * Woocommerce functions.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require get_template_directory() . '/inc/woocommerce.php';
}
add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );
});

/**
 * LearnDash functions.
 */
require get_template_directory() . '/inc/dev.php';

/**
 * Snippet functions.
 */
require get_template_directory() . '/inc/snippets.php';

/**
 * Search Webhook functions.
 */
require get_template_directory() . '/inc/search-webhook.php';

/**
 * SSO Cognito functions.
 */
// require get_template_directory() . '/inc/sso-cognito.php';

/**
 * Custom bbPress functions.
 */
require get_template_directory() . '/inc/bbpress.php';

/**
 * Sync users to cognito functions.
 */
// require get_template_directory() . '/inc/sync-users.php';

/**
 * API to learndash courses access functions.
 */
// require get_template_directory() . '/apis/learndash-courses-access.php';

/**
 * API to get user token functions.
 */
// require get_template_directory() . '/apis/get-cognito-token.php';
