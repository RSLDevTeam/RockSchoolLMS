<?php
/**
 * User roles
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Learner, Instructor, and Parent roles
function custom_add_roles_once() {
    // Add roles if they don't already exist
    if (!get_role('learner')) {
        add_role('learner', 'Learner', get_role('subscriber')->capabilities);
    }
    if (!get_role('instructor')) {
        add_role('instructor', 'Instructor', get_role('subscriber')->capabilities);
    }
    if (!get_role('parent')) {
        add_role('parent', 'Parent', get_role('subscriber')->capabilities);
    }

    // Add custom capabilities to the roles
    $learner = get_role('learner');
    if ($learner && !$learner->has_cap('is_learner')) {
        $learner->add_cap('is_learner');
    }

    $instructor = get_role('instructor');
    if ($instructor && !$instructor->has_cap('is_instructor')) {
        $instructor->add_cap('is_instructor');
    }

    $parent = get_role('parent');
    if ($parent && !$parent->has_cap('is_parent')) {
        $parent->add_cap('is_parent');
    }
}
add_action('init', 'custom_add_roles_once');

// Hide admin bar for custom roles
function hide_admin_bar_for_custom_roles() {
    if (current_user_can('learner') || current_user_can('instructor') || current_user_can('parent')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'hide_admin_bar_for_custom_roles');

// Redirect Learner, Instructor, Parent, and Subscriber roles to the homepage after login
function custom_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('learner', $user->roles) || in_array('instructor', $user->roles) || in_array('parent', $user->roles) || in_array('subscriber', $user->roles)) {
            return home_url(); // Redirect to homepage
        }
    }
    return $redirect_to; // Default behavior for other roles
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

// Prevent Learner, Instructor, Parent, and Subscriber from accessing wp-admin
function restrict_admin_access() {
    if (is_admin() && !current_user_can('edit_posts') && !wp_doing_ajax()) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'restrict_admin_access');

// Add a role selection dropdown to the registration form
function custom_register_form_role_field() {
    ?>
    <p class="register-role-select">
        <label for="user_role"><?php esc_html_e('Register as', 'rslfranchise'); ?><br/>
            <select name="user_role" id="user_role" required>
                <option value="" disabled selected><?php esc_html_e('Please select', 'rslfranchise'); ?></option>
                <option value="parent"><?php esc_html_e('Parent', 'rslfranchise'); ?></option>
                <option value="learner"><?php esc_html_e('Learner (age 16+)', 'rslfranchise'); ?></option>
            </select>
        </label>
    </p>
    <?php
}
add_action('register_form', 'custom_register_form_role_field');

// Validate the role field input
function custom_register_form_role_validation($errors, $sanitized_user_login, $user_email) {
    if (empty($_POST['user_role']) || !in_array($_POST['user_role'], ['learner', 'parent'])) {
        $errors->add('user_role_error', __('<strong>ERROR</strong>: Please select a valid role.', 'your-text-domain'));
    }
    return $errors;
}
add_filter('registration_errors', 'custom_register_form_role_validation', 10, 3);

// Assign the selected role after user registration
function custom_register_form_role_assign($user_id) {
    if (!empty($_POST['user_role']) && in_array($_POST['user_role'], ['learner', 'parent'])) {
        $user = new WP_User($user_id);
        $user->set_role(sanitize_text_field($_POST['user_role']));
    }
}
add_action('user_register', 'custom_register_form_role_assign');

// Add 'not-logged-in' class to body for non-logged-in users
function add_not_logged_in_body_class($classes) {
    if (!is_user_logged_in()) {
        $classes[] = 'not-logged-in';
    }
    return $classes;
}
add_filter('body_class', 'add_not_logged_in_body_class');