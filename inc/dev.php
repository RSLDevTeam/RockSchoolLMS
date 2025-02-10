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