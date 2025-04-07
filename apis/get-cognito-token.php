<?php


/**
 * The API for getting the cognito token
 * This file will be used to get the cognito token for the user
 * @package rslfranchise
 */

 use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
 use Aws\Exception\AwsException;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


add_action('rest_api_init', function() {
    register_rest_route('api/v1', '/user-token/', [
        'methods'  => 'POST',
        'callback' => 'get_user_token',
        'permission_callback' => '__return_true', // We'll handle auth inside
    ]);
});

function get_user_token(WP_REST_Request $request) {
    $params = $request->get_body_params();
    $username = $params['username'] ?? '';
    $password = $params['password'] ?? '';

    if (!$username || !$password) {
        return new WP_Error('missing_params', 'Username and password are required', ['status' => 400]);
    }

    $client_id = COGNITO_CLIENT_ID;
    $client_secret = COGNITO_CLIENT_SECRET;
    $aws_region = AWS_REGION;
    $user_pool_id = AWS_USER_POOL_ID;

    // Compute the SECRET_HASH
    $secret_hash = base64_encode(
      hash_hmac('sha256', $username . $client_id, $client_secret, true)
    );

    $cognitoClient = new CognitoIdentityProviderClient([
        'region' => $aws_region,
        'version' => '2016-04-18',
        'credentials' => [
        'key'    => SDK_ACCESS_KEY,  
        'secret' => SDK_SECRET_KEY,
        ],
    ]);

    try {
        $result = $cognitoClient->initiateAuth([
            'AuthFlow' => 'USER_PASSWORD_AUTH',
            'ClientId' => $client_id,
            'UserPoolId' => $user_pool_id,
            'AuthParameters' => [
                'USERNAME' => $username,
                'PASSWORD' => $password,
                'SECRET_HASH' => $secret_hash,
            ],
        ]);

        return new WP_REST_Response([
          'id_token' => $result['AuthenticationResult']['IdToken'],
          'access_token' => $result['AuthenticationResult']['AccessToken'],
          'refresh_token' => $result['AuthenticationResult']['RefreshToken'],
        ], 200);
    } catch (AwsException $e) {
        return new WP_Error('cognito_error', $e->getMessage(), ['status' => 400]);
    }
  
}