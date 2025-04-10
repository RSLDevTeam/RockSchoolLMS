<?php
/**
 * Misc functions
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Image sizes
add_image_size( 'book-small', 555, 740, true );

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

// Function to customisse the login screen
function custom_login_logo() {
    $logo_path = get_template_directory_uri() . '/img/rs-pick-logo.svg'; 
    $bg_path = get_template_directory_uri() . '/img/RSL-PP-BKG-Blank.jpg'; 
    echo '<style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(' . $logo_path . ');
            width:350px;   
            height:120px;   
            background-size: 350px 120px;   
            background-repeat: no-repeat;
            pointer-events: none;
        }
        .wp-core-ui .button-primary, 
        .wp-core-ui .button-primary:hover {
            background: #4cadc2 !important;
            border-color: #4cadc2 !important;
        }
        body {
            display: flex;
            background: #fcfcfc !important;
            background-image: url(' . $bg_path . ') !important;
            background-size: cover !important;
        }
        div#login {
            padding: 3.5em 1.5em 1.5em 1.5em;
            border: 1px solid #c4c4c4 !important;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 4px 20px rgb(176 176 176 / 87%);
            width: 420px;
            max-width: 100%;
        }
        p#nav {
            width: fit-content;
            display: block;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .login form {
            border: 0 !important;
            box-shadow: none !important;
        }
        input#wp-submit {
            border-radius: 0;
            padding: 5px 25px;
        }
        .login .message, .login .notice, .login .success {
            border-left: 0 !important;
            border-bottom: 1px solid #d6d6d6;
            box-shadow: none !important;
            text-align: center;
        }
         p#backtoblog {
            display: none;
        }
        .register-role-select {
            padding-bottom: 1em !important;
        }
        .register-role-select label, select#user_role {
            width: 100%;
        }
        select#user_role {
            padding-top: 5px;
            padding-bottom: 5px;
        }
        label.privacy_policy_terms {
            font-size: inherit !important;
            margin-bottom: 1em !important;
        }
   </style>';
}
add_action('login_enqueue_scripts', 'custom_login_logo');

// Restrict access to the site for non-logged-in users
function restrict_site_access() {
    if (!is_user_logged_in() && !is_admin() && !wp_doing_ajax()) {

        // Allow public access to Privacy Policy and Terms & Conditions pages
        $allowed_pages = ['privacy-policy', 'terms-and-conditions'];
        if (is_page($allowed_pages)) {
            return;
        }

        wp_redirect(wp_login_url());
        exit;

    }
}
add_action('template_redirect', 'restrict_site_access');

// Add Privacy Policy and Terms checkbox to registration form
function custom_register_form_terms_checkbox() {
    ?>
    <p>
        <label for="privacy_policy_terms" class="privacy_policy_terms">
            <input type="checkbox" name="privacy_policy_terms" id="privacy_policy_terms" value="1" required />
            <?php printf(__('I agree to the <a href="%s" target="_blank">Privacy Policy</a> and <a href="%s" target="_blank">Terms & Conditions</a>.', 'your-text-domain'), 
                esc_url(home_url('/privacy-policy')), 
                esc_url(home_url('/terms-and-conditions'))
            ); ?>
        </label>
    </p>
    <?php
}
add_action('register_form', 'custom_register_form_terms_checkbox');

// Validate the checkbox field
function custom_register_form_terms_validation($errors, $sanitized_user_login, $user_email) {
    if (empty($_POST['privacy_policy_terms'])) {
        $errors->add('privacy_policy_terms_error', __('<strong>ERROR:</strong> You must agree to the Privacy Policy and Terms & Conditions.', 'your-text-domain'));
    }
    return $errors;
}
add_filter('registration_errors', 'custom_register_form_terms_validation', 10, 3);