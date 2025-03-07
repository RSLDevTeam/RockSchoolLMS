<?php
/**
 * Block registration 
 */

defined( 'ABSPATH' ) || exit;

// Soundslice block
function custom_enqueue_soundslice_block() {
    $screen = get_current_screen();
    if ($screen && $screen->base === 'post') {
        wp_enqueue_script(
            'custom-soundslice-block',
            get_stylesheet_directory_uri() . '/assets/blocks/soundslice.js',
            array('wp-blocks', 'wp-element'),
            filemtime(get_stylesheet_directory() . '/assets/blocks/soundslice.js'),
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'custom_enqueue_soundslice_block');
// Load block registration
require_once get_template_directory() . '/assets/blocks/soundslice.php';