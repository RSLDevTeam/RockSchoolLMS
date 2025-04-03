<?php
/**
 * The Api for managing course access
 * This file will be used to manage course access for users using REST API.
 * @package rslfranchise
 */

 use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
 use Aws\Exception\AwsException;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/manage-course/', [
        'methods'  => 'POST',
        'callback' => 'handle_course_access',
        'permission_callback' => '__return_true', // We'll validate inside
    ]);
});

function handle_course_access(WP_REST_Request $request) {
    $token = $request->get_header('Authorization');
    if (!$token) {
        return new WP_Error('unauthorized', 'Missing Authorization Token', ['status' => 401]);
    }
    $request_body = $request->get_body();
    $params = json_decode($request_body, true);

    if (!$params || !isset($params['courses'])) {
        return new WP_Error('invalid_params', 'Missing or invalid parameters', ['status' => 400]);
    }
    $courses = $params['courses'];

    if (!function_exists('ld_update_course_access')) {
        return new WP_Error('learndash_missing', 'LearnDash plugin is not active', ['status' => 500]);
    }

    // Remove "Bearer " if present
    $token = str_replace('Bearer ', '', $token);
    $response = validate_cognito_token($token);
    
    if (!$response) {
        return new WP_Error('unauthorized', 'Invalid or Expired Token', ['status' => 401]);
    }

    //return validate_user_role($response['email'], $response['role']); //uncomment this line after testing
    $user           = get_user_by('email', $response['email']);    
    $acf_user_id    = 'user_' . $user->ID;
    $linked_learners= get_field('linked_learners', 'user_' . $user->ID);
    if (!$linked_learners) return new WP_Error('check', 'No child learners associated', ['status' => 201]);
    
    $linked_learners_ids = array_map(fn($learner) => $learner['ID'], $linked_learners);
    array_push($linked_learners_ids, $user->ID); //save user it self to linked in learners to check access
    
    //invalid course & learner ids
    $invalid_course_ids = [];
    $invalid_linked_learners_ids = [];
    $invalid_access_course = [];
    //forech courses to update access
    foreach ($courses as $course) {
        $course_id = $course['course_id'];
        $accessable_user_id = $course['accessable_user_id'];
        $purchased_user_id = $course['purchased_user_id'];
        $revoke_access = $course['revoke_access'];

        // Check if the accessible user or the purchased user is in the linked learners
        $is_accessable_user_valid = in_array($accessable_user_id, $linked_learners_ids);
        $is_purchased_user_valid = in_array($purchased_user_id, $linked_learners_ids);

        // Check if either of the conditions for user validity is not met
        if (!$is_accessable_user_valid || !$is_purchased_user_valid) {
            if (!$is_accessable_user_valid) {
                $invalid_linked_learners_ids[] = $accessable_user_id;
            }
            if (!$is_purchased_user_valid) {
                $invalid_linked_learners_ids[] = $purchased_user_id;
            }
            continue; // Skip to the next course
        }

        // Validate the course existence in LearnDash
        if (!is_valid_learndash_course($course_id)) {
            $invalid_course_ids[] = $course_id;
            continue; // Skip to the next course
        }

        // Update course access if all checks are valid
        $is_updated_course =  update_course_access($course);
        
        if (!$is_updated_course) {
            $invalid_access_course[] = $course_id;
            continue; // Skip to the next course
        }
    }
    // If there are invalid course IDs, return them
    if (!empty($invalid_course_ids) || !empty($invalid_linked_learners_ids) || !empty($invalid_access_course)) {
        $invalid_course_ids = array_unique($invalid_course_ids);
        $invalid_linked_learners_ids = array_unique($invalid_linked_learners_ids);
        $message_parts = [];

        if (!empty($invalid_course_ids)) {
            $message_parts[] = 'Invalid course IDs: ' . implode(', ', $invalid_course_ids);
        }
        if (!empty($invalid_linked_learners_ids)) {
            $message_parts[] = 'Invalid learner IDs: ' . implode(', ', $invalid_linked_learners_ids);
        }
        if (!empty($invalid_access_course)) {
            $message_parts[] = 'Invalid access course IDs: ' . implode(', ', $invalid_access_course);
        }

        return new WP_Error(
            'invalid_courses_or_learners',
            implode(' / ', $message_parts),
            ['status' => 400]
        );
    }
    return new WP_REST_Response([
        'message' => "User has been updated to courses"
    ], 200);
}

function validate_cognito_token($token) {
    $aws_region = AWS_REGION;
    $client = new CognitoIdentityProviderClient([
        'region' => $aws_region,
        'version' => '2016-04-18',
        'credentials' => [
            'key'    => SDK_ACCESS_KEY,
            'secret' => SDK_SECRET_KEY,
        ],
    ]);

    try {
        $result = $client->getUser([
            'AccessToken' => $token
        ]);
        // Extract user attributes as an associative array
        $attributes = array_column($result['UserAttributes'] ?? [], 'Value', 'Name');

        // Return email and user role if they exist
        return isset($attributes['email'], $attributes['custom:user_role']) 
            ? ['email' => $attributes['email'], 'role' => $attributes['custom:user_role']]
            : false;

    } catch (AwsException $e) {
        error_log('Cognito Token Validation Failed: ' . $e->getMessage());
        return false; // Invalid token
    }
}

function validate_user_role($email, $role) {
    // Check if the user exists in Backstage by email
    $user = get_user_by('email', $email);
    if (!$user) {
        return new WP_Error('user_not_found', 'User not found in WordPress', ['status' => 404]);
    }

    // Check if the user has the parent or same role as backstage
    if (!user_can($user, $role) || $role !== 'parent') {
        return new WP_Error('invalid_role', 'User does not have the required role', ['status' => 403]);
    }

}

function is_valid_learndash_course($course_id) {
    $post = get_post($course_id);
    if (!$post || 'sfwd-courses' !== $post->post_type) {
        return false;
    }
    return true;
}

function update_course_access($course_request) {
    $course_id = $course_request['course_id'];
    $accessable_user_id = $course_request['accessable_user_id'];
    $purchased_user_id = $course_request['purchased_user_id'];
    $revoke_access = $course_request['revoke_access'];
    try {
        //check learndash course exists to the users
        $accessable_user_courses = learndash_get_user_course_access_list($accessable_user_id);
        if($revoke_access) {
            if(!in_array($course_id, $accessable_user_courses)) {
                return false;
            }
            ld_update_course_access($accessable_user_id, $course_id, true); //unenrolled child
            ld_update_course_access($purchased_user_id, $course_id, true); //enrolled parent
        }
        
        ld_update_course_access($accessable_user_id, $course_id, $false);
        return true;
    }catch (WP_Exception $e) {
        return new WP_Error('accessable_user_courses3', $accessable_user_courses, ['status' => 401]);
    } catch (Exception $e) {
        // Catch general exceptions
        return new WP_Error('accessable_user_courses4', $accessable_user_courses, ['status' => 401]);
    }
    
}
