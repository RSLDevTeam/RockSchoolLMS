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

// Restrict access to the site for non-logged-in users
function restrict_site_access() {
    if (!is_user_logged_in() && !is_admin() && !wp_doing_ajax()) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'restrict_site_access');

// Redirect Learner, Instructor, and Subscriber roles to the homepage after login
function custom_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('learner', $user->roles) || in_array('instructor', $user->roles) || in_array('subscriber', $user->roles)) {
            return home_url(); // Redirect to homepage
        }
    }
    return $redirect_to; // Default behavior for other roles
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

// Prevent Learner, Instructor, and Subscriber from accessing wp-admin
function restrict_admin_access() {
    if (is_admin() && !current_user_can('edit_posts') && !wp_doing_ajax()) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'restrict_admin_access');