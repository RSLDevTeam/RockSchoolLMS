<?php
/**
 * User roles
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Learner and Instructor roles
function custom_add_roles_once() {
    if (!get_role('learner')) {
        add_role('learner', 'Learner', get_role('subscriber')->capabilities);
    }
    if (!get_role('instructor')) {
        add_role('instructor', 'Instructor', get_role('subscriber')->capabilities);
    }
}
add_action('init', 'custom_add_roles_once');

// Hide admin bar
function hide_admin_bar_for_custom_roles() {
    if (current_user_can('learner') || current_user_can('instructor')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'hide_admin_bar_for_custom_roles');

