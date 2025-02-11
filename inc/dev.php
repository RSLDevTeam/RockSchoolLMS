<?php
/**
 * Misc functions
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Disable admin bar
function disable_admin_bar_for_non_admins() {
    // Check if the current user is not an administrator
    if (!current_user_can('administrator') && !is_admin()) {
        // Disable the admin bar for this user
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'disable_admin_bar_for_non_admins');

// ACF options pages
if( function_exists('acf_add_options_page') ) {
    
    acf_add_options_page(array(
        'page_title'    => 'Rock School Theme Settings',
        'menu_title'    => 'Theme Settings',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false,
        'icon_url'      => 'dashicons-marker',
        'position'      => 1
    ));
           
}