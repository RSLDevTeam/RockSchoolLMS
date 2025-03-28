<?php 

// lets define constants for the cognito configuration from ACF values

define('COGNITO_CLIENT_ID', get_field('client_id', 'option')?: '');
define('COGNITO_CLIENT_SECRET', get_field('client_secret', 'option')?: '');
define('COGNITO_DOMAIN', get_field('cognito_domain', 'option')?: '');
define('COGNITO_REDIRECT_URI', home_url('/cognito-login'));
define('COGNITO_SCOPE', get_field('scope', 'option')?: '');


ob_start();

// Redirect WordPress Login to Cognito Hosted UI

add_action('login_form', 'redirect_to_cognito_hosted_ui');
function redirect_to_cognito_hosted_ui() {
    if (!isset($_GET['code']) && !isset($_GET['error'])) {
        $client_id = COGNITO_CLIENT_ID;
        $redirect_uri = COGNITO_REDIRECT_URI;
        $cognito_domain = COGNITO_DOMAIN;

        $login_url = "https://{$cognito_domain}/oauth2/authorize?" . http_build_query([
            'client_id' => $client_id,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'redirect_uri' => $redirect_uri,
            'prompt' => 'none',
        ]);

        wp_redirect($login_url);
        exit;
    }
}

// Handle the Cognito Callback in WordPress

add_action('init', 'handle_cognito_login_callback');
function handle_cognito_login_callback() {
    if (isset($_GET['code']) && strpos($_SERVER['REQUEST_URI'], '/cognito-login') !== false) {
        $client_id = COGNITO_CLIENT_ID;
        $client_secret = COGNITO_CLIENT_SECRET;
        $redirect_uri = COGNITO_REDIRECT_URI;
        $cognito_domain = COGNITO_DOMAIN;
        $code = sanitize_text_field($_GET['code']);

        // Exchange the code for tokens
        $response = wp_remote_post("https://{$cognito_domain}/oauth2/token", [
            'body' => http_build_query([
                'grant_type' => 'authorization_code',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'code' => $code,
                'redirect_uri' => $redirect_uri,
            ]),
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
        ]);

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['id_token'])) {
            $user_info = jwt_decode($body['id_token']);

            // Auto-provision or get existing user
            $user = get_user_by('email', $user_info['email']);
            if (!$user) {
                $user_id = wp_create_user($user_info['email'], wp_generate_password(), $user_info['email']);
                $user = get_user_by('id', $user_id);
            }

            // Map Cognito custom:user_type to WordPress role
            $user_type = $user_info['custom:user_role'] ?? '';
            //wp_die('Cognito Login Failed: ' . print_r($user_type, true));
            switch ($user_type) {
                
                case 'instructor':
                    $role = 'instructor';
                    break;
                case 'parent':
                    $role = 'parent';
                    break;
                case 'learner':
                    $role = 'learner';
                    break;
                case 'group_leader':
                    $role = 'group_leader';
                    break;
                case 'contributor':
                    $role = 'contributor';
                    break;
                case 'editor':
                    $role = 'editor';
                    break;
                case 'author':
                    $role = 'author';
                    break;
                case 'admin':
                    $role = 'administrator';
                    break;
                default:
                    $role = 'aubscriber';
                    break;
            }

            $user->set_role($role);

            // Log the user in
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);

            // Redirect after successful login
            wp_redirect(home_url());
            exit;
        } else {
            wp_die('Cognito Login Failed: ' . print_r($body, true));
        }
    }
}

// JWT decoder

function jwt_decode($jwt) {
    $token_parts = explode('.', $jwt);
    return json_decode(base64_decode(strtr($token_parts[1], '-_', '+/')), true);
}

// Logout with cognito

add_action('wp_logout', function () {
    $cognito_domain = COGNITO_DOMAIN;
    $client_id = COGNITO_CLIENT_ID;
    $logout_redirect = urlencode(home_url('/logout'));

    wp_redirect("https://{$cognito_domain}/logout?client_id={$client_id}&logout_uri={$logout_redirect}");
    exit;
});

