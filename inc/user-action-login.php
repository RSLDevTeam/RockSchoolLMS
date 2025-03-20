<?php
require_once get_template_directory() . '/inc/aes-hash-password.php';

function call_cloud_api_on_user_login($user_login, $user) {
    
    // API URL
    $api_url = get_field('cloud_login_link_path', 'option');

    $hashed_password = aes_encrypt($_POST['pwd']);
    $all_user_mata = get_user_meta($user->ID);

    // Data to send to the API
    $data = array(
        'userName' => $user->user_email,
        'password' => $hashed_password,
        'user_meta' => $all_user_mata
    );
    
    // Build the query string
    $query_string = http_build_query($data);

    // Make the API call using wp
    $response = wp_remote_get($api_url . '?' . $query_string, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
    ));

    // Check for respose errors
    if (is_wp_error($response)) {
        error_log('API call failed: ' . $response->get_error_message());
        return;
    }

    // Retrieve the body of the response
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);

    // Extract the token from the response data
    if (isset($response_data['Token'])) {
        $token = $response_data['Token'];

        // Save the token in a cookie
        setcookie('user_token', $token, time() + (86400 * 1.5), "/"); // 1.5 days expiration
    } else {
        error_log('Token not found in API response');
    }
}

// Hook into the wp_login action
add_action('wp_login', 'call_cloud_api_on_user_login', 10, 2);