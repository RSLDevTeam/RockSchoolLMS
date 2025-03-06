<?php
/**
 * Homework email functions
 *
 * @package underscores
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Send email on homework post creation
function send_homework_notification($post_id) {
    // Get the assigned learner
    $learner = get_field('learner', $post_id);
    if (!$learner || empty($learner['user_email'])) {
        return;
    }

    // Get learner email and name
    $learner_email = sanitize_email($learner['user_email']);
    $learner_name = esc_html($learner['display_name']);

    // Get the homework title
    $homework_title = get_field('title', $post_id);
    $homework_title = !empty($homework_title) ? esc_html($homework_title) : __('Untitled Homework', 'rslfranchise');

    // Generate the direct link to the homework post
    $homework_link = get_permalink($post_id);

    // Email subject and message
    $subject = sprintf(__('Rockschool Homework Updated: %s', 'rslfranchise'), $homework_title);
    $message = sprintf(__('Hello %s,', 'rslfranchise'), $learner_name) . "\n\n";
    $message .= __('Your rockschool homework has been updated.', 'rslfranchise') . "\n\n";
    $message .= __('Homework Title: ', 'rslfranchise') . $homework_title . "\n\n";
    $message .= __('View your homework:', 'rslfranchise') . "\n";
    $message .= $homework_link . "\n\n";
    $message .= __('Best regards,', 'rslfranchise') . "\n";
    $message .= get_bloginfo('name') . "\n";

    // Send the email
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    
    return wp_mail($learner_email, $subject, $message, $headers);

}

// Send email when learner response
function send_homework_response_notification($post_id) {
    // Get the assigned instructor
    $instructor = get_field('instructor', $post_id);
    if (!$instructor || empty($instructor['user_email'])) {
        return;
    }

    // Get instructor email and name
    $instructor_email = sanitize_email($instructor['user_email']);
    $instructor_name = esc_html($instructor['display_name']);

    // Get learner's name
    $learner = get_field('learner', $post_id);
    $learner_name = $learner ? esc_html($learner['display_name']) : __('A learner', 'rslfranchise');

    // Get the homework title
    $homework_title = get_field('title', $post_id);
    $homework_title = !empty($homework_title) ? esc_html($homework_title) : __('Untitled Homework', 'rslfranchise');

    // Generate the direct link to the homework post
    $homework_link = get_permalink($post_id);

    // Email subject and message
    $subject = sprintf(__('Homework Response Submitted: %s', 'rslfranchise'), $homework_title);
    $message = sprintf(__('Hello %s,', 'rslfranchise'), $instructor_name) . "<br><br>";
    $message .= sprintf(__('%s has submitted a response to their homework.', 'rslfranchise'), $learner_name) . "<br><br>";
    $message .= __('Homework Title: ', 'rslfranchise') . $homework_title . "<br><br>";
    $message .= __('View the homework and response here:', 'rslfranchise') . "<br>";
    $message .= '<a href="' . $homework_link . '">' . $homework_link . '</a><br><br>';
    $message .= __('Best regards,', 'rslfranchise') . "<br>";
    $message .= get_bloginfo('name') . "<br>";

    // Email headers
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    return wp_mail($instructor_email, $subject, $message, $headers);
}