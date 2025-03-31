<?php

/**
 * Sync users scripts to cognito
 *
 */

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;

define('SDK_ACCESS_KEY', get_field('access_key', 'option')?: '');
define('SDK_SECRET_KEY', get_field('secret_key', 'option')?: '');
 
add_action('user_register', 'sync_user_to_cognito', 10, 1);
add_action('profile_update', 'sync_user_to_cognito', 10, 1);

function sync_user_to_cognito($user_id) {
    $user = get_userdata($user_id);
 
    if (!$user || !$user->user_email) {
        return;
    }
    
 
    $client_id = SDK_ACCESS_KEY;
    $client_secret = SDK_SECRET_KEY;
    
    $cognitoClient = new CognitoIdentityProviderClient([
        'region' => 'eu-west-2',
        'version' => '2016-04-18',
        'credentials' => [
            'key'    => $client_id,
            'secret' => $client_secret,
        ],
    ]);
 
    $userPoolId = 'eu-west-2_MjoR6V3RL';
    $username   = $user->user_login;
 
    $attributes = [
        ['Name' => 'name', 'Value' => $user->display_name],
        ['Name' => 'email', 'Value' => $user->user_email],
        ['Name' => 'email_verified', 'Value' => 'true'],
        ['Name' => 'custom:user_role', 'Value' => $user->roles[0] ?? 'subscriber'],
    ];
 
    try {
        // Attempt to update first
        $cognitoClient->adminUpdateUserAttributes([
            'UserPoolId'     => $userPoolId,
            'Username'       => $username,
            'UserAttributes' => $attributes,
        ]);
    } catch (AwsException $e) {
        if ($e->getAwsErrorCode() === 'UserNotFoundException') {
            try {
                $cognitoClient->adminCreateUser([
                    'UserPoolId'     => $userPoolId,
                    'Username'       => $username,
                    'UserAttributes' => $attributes,
                    'MessageAction'  => 'SUPPRESS',
                ]);
            } catch (AwsException $e) {
                error_log('Cognito Create Error: ' . $e->getMessage());
                wp_die('Error syncing user to Cognito' . $e->getMessage());
            }
        } else {
            error_log('Cognito Update Error: ' . $e->getMessage());
            wp_die('Error syncing user to Cognito' . $e->getMessage());
        }
    }
}