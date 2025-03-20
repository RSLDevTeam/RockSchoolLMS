<?php

function call_cloud_api_on_user_register($user_id, $userdata) {
    // API URL
    $api_url = get_field('cloud_login_link_path', 'option');

    $all_user_mata = get_user_meta($user_id);

    // Data to send to the API
    $data = array(
        'user_id' => $user_id,
        'user_email' => $userdata['user_email'],
        'user_password' => $userdata['user_pass'],
        'user_meta' => $all_user_mata
    );

    // Make the API call using wp_remote_post
    $response = wp_remote_post($api_url, array(
        'method'    => 'POST',
        'body'      => json_encode($data),
        'headers'   => array(
        'Content-Type' => 'application/json',
        ),
    ));

    // Check for errors
    if (is_wp_error($response)) {
        error_log('API call failed: ' . $response->get_error_message());
    } else {
        error_log('API call succeeded: ' . wp_remote_retrieve_body($response));
    }
}

// Hook into the user_register action
add_action('user_register', 'call_cloud_api_on_user_register');