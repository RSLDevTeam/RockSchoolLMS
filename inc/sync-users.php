<?php

/**
 * Sync users scripts to cognito
 *
 */

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;

define('SDK_ACCESS_KEY', get_field('access_key', 'option')?: '');
define('SDK_SECRET_KEY', get_field('secret_key', 'option')?: '');
define('AWS_REGION', get_field('aws_region', 'option')?: '');
define('AWS_USER_POOL_ID', get_field('aws_user_pool_id', 'option')?: '');

 
add_action('user_register', 'sync_user_to_cognito', 10, 1);
add_action('profile_update', 'sync_user_to_cognito', 10, 2);

function sync_user_to_cognito($user_id) {
    $user = get_userdata($user_id);
 
    if (!$user || !$user->user_email) {
        return;
    }
    
    $client_id = SDK_ACCESS_KEY;
    $client_secret = SDK_SECRET_KEY;
    $aws_region = AWS_REGION;
    $user_pool_id = AWS_USER_POOL_ID;
    
    $cognitoClient = new CognitoIdentityProviderClient([
        'region' => $aws_region,
        'version' => '2016-04-18',
        'credentials' => [
            'key'    => $client_id,
            'secret' => $client_secret,
        ],
    ]);
 
    $username   = $user->user_login;
    $userPhone  = get_user_meta($user_id, 'billing_phone', true) ?? "";
    $userAddress = get_user_address($user_id);
 
    $attributes = [
        ['Name' => 'name', 'Value' => $user->display_name],
        ['Name' => 'email', 'Value' => $user->user_email],
        ['Name' => 'email_verified', 'Value' => 'true'],
        ['Name' => 'phone_number', 'Value' => $userPhone],
        ['Name' => 'phone_number_verified', 'Value' => 'true'],
        ['Name' => 'address', 'Value' => $userAddress],
        ['Name' => 'custom:user_role', 'Value' => $user->roles[0] ?? 'subscriber'],
    ];
 
    try {
        // Attempt to update first
        $cognitoClient->adminUpdateUserAttributes([
            'UserPoolId'     => $user_pool_id,
            'Username'       => $username,
            'UserAttributes' => $attributes,
        ]);
    } catch (AwsException $e) {
        if ($e->getAwsErrorCode() === 'UserNotFoundException') {
            try {
                $cognitoClient->adminCreateUser([
                    'UserPoolId'     => $user_pool_id,
                    'Username'       => $username,
                    'UserAttributes' => $attributes,
                    //'MessageAction'  => 'SUPPRESS', //For Welocming user email
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

// function get address from user meta and jason signifying the address
function get_user_address($user_id) {
    $user = get_userdata($user_id);

    // Get WooCommerce billing details
    $billing_address_1  = get_user_meta($user_id, 'billing_address_1', true) ?? "";
    $billing_address_2  = get_user_meta($user_id, 'billing_address_2', true) ?? "";
    $billing_city       = get_user_meta($user_id, 'billing_city', true) ?? "";
    $billing_postcode   = get_user_meta($user_id, 'billing_postcode', true) ?? "";
    $billing_state      = get_user_meta($user_id, 'billing_state', true) ?? "";
    $billing_country    = get_user_meta($user_id, 'billing_country', true) ?? "";


    // Format address into a single string
    $billing_address = trim("$billing_address_1 $billing_address_2, $billing_city, $billing_postcode, $billing_country");
    $billing_street_address = trim("$billing_address_1 $billing_address_2");


    $address = [
          "formatted"        => $billing_address,
          "street_address"   => $billing_street_address,
          "locality"         => $billing_city,
          "region"           => $billing_state,
          "postal_code"      => $billing_postcode,
          "country"          => $billing_country
    ];

    return json_encode($address);
}