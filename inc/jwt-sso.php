<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Custom JWT decoding function (using HMAC SHA256)
function custom_jwt_decode($jwt, $secret_key) {
    $jwt_parts = explode('.', $jwt);
    
    if (count($jwt_parts) !== 3) {
        return false; // Invalid JWT structure
    }

    list($header, $payload, $signature) = $jwt_parts;

    $decoded_header = json_decode(base64_decode($header), true);
    $decoded_payload = json_decode(base64_decode($payload), true);

    if (!$decoded_header || !$decoded_payload) {
        return false;
    }

    // Verify signature
    $valid_signature = hash_hmac('sha256', "$header.$payload", $secret_key, true);
    $valid_signature_encoded = rtrim(strtr(base64_encode($valid_signature), '+/', '-_'), '=');

    if (!hash_equals($valid_signature_encoded, $signature)) {
        return false; // Signature mismatch
    }

    return $decoded_payload;
}

// JWT SSO Login Function
function sso_login_via_jwt() {
    if (!isset($_GET['token'])) {
        return;
    }

    $jwt = $_GET['token'];
    $secret_key = get_field('jwt_secret', 'option');

    $decoded = custom_jwt_decode($jwt, $secret_key);
    if (!$decoded) {
        wp_die("Invalid JWT token");
    }

    $user_email = $decoded['email'] ?? '';
    $course_id = intval($decoded['course_id'] ?? 0);

    if (!$user_email || !$course_id) {
        wp_die("Invalid SSO request");
    }

    // Check if user exists
    $user = get_user_by('email', $user_email);
    if (!$user) {
        // Create new user
        $user_id = wp_create_user($user_email, wp_generate_password(), $user_email);
        if (is_wp_error($user_id)) {
            wp_die("Failed to create user");
        }
        $user = get_user_by('ID', $user_id);
    }

    // Enroll user in LearnDash course
    ld_update_course_access($user->ID, $course_id, true);

    // Log user in
    wp_set_auth_cookie($user->ID);
    wp_set_current_user($user->ID);

    // // Redirect to the LearnDash course
    // $course_url = get_permalink($course_id);
    // wp_redirect($course_url);
    // exit;
}
add_action('init', 'sso_login_via_jwt');
