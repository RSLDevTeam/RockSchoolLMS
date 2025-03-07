<?php
/**
 * Block registration 
 */

defined( 'ABSPATH' ) || exit;

// Soundslice block
function custom_enqueue_soundslice_assets() {
    wp_register_script(
        'custom-soundslice-block',
        get_stylesheet_directory_uri() . '/assets/blocks/soundslice.js',
        array('wp-blocks', 'wp-element'),
        filemtime(get_stylesheet_directory() . '/assets/blocks/soundslice.js'),
        true
    );
}
add_action('init', 'custom_enqueue_soundslice_assets');

// Load block registration
require_once get_template_directory() . '/inc/blocks/soundslice.php';