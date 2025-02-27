<?php
/**
 * Dashboard link instructor
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user; 
wp_get_current_user();
$user_id = $current_user->ID;

// Initialize variables
$error_messages = [];
$success_message = '';

// Handle form submission
if (!empty($_POST['link_instructor_nonce']) && wp_verify_nonce($_POST['link_instructor_nonce'], 'link_instructor_action')) {

    $instructor_email = sanitize_email($_POST['instructor_email']);

    // Validation
    if (empty($instructor_email) || !is_email($instructor_email)) {
        $error_messages[] = __('Please enter a valid email address.', 'rslfranchise');
    } else {
        // Check if a user with this email exists
        $user = get_user_by('email', $instructor_email);

        if ($user && in_array($user->roles[0], ['instructor', 'administrator'])) {
            // Link the learner to this instructor
            $linked_learners = get_field('linked_learners', 'user_' . $user->ID) ?: [];
            
            if (!in_array($user_id, $linked_learners)) {
                $linked_learners[] = $user_id;
                update_field('linked_learners', $linked_learners, 'user_' . $user->ID);

                // Redirect to avoid resubmission
                wp_safe_redirect(add_query_arg('success', 'linked', get_permalink()));
                exit;
            } else {
                $error_messages[] = __('You are already linked to this instructor.', 'rslfranchise');
            }

        } else {
            // No matching instructor/admin found, send an invite
            $invite_sent = send_instructor_invite($instructor_email, $current_user);

            if ($invite_sent) {
                // Redirect after sending invite
                wp_safe_redirect(add_query_arg('success', 'invited', get_permalink()));
                exit;
            } else {
                $error_messages[] = __('There was an error sending the invite. Please try again later.', 'rslfranchise');
            }
        }
    }
}

// Function to send invite email
function send_instructor_invite($email, $inviter) {
    $subject = sprintf(__('%s invited you to Rockschool Backstage', 'rslfranchise'), $inviter->display_name);
    $message = sprintf(
        __(
            "%s would like to invite you to work with them on Rockschool Backstage. 
            Please visit <a href='https://rockschool.io/' target='_blank'>https://rockschool.io/</a> for more information.",
            'rslfranchise'
        ),
        $inviter->display_name
    );

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    
    return wp_mail($email, $subject, $message, $headers);
}

?>

<hr>

<div id="instructor-messages">

    <?php if (isset($_GET['success']) && $_GET['success'] === 'linked') : ?>
        <p class="success-message"><?php _e('Instructor linked successfully!', 'rslfranchise'); ?></p>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('instructor-messages').scrollIntoView({ behavior: 'smooth' });
            });
        </script>
    <?php elseif (isset($_GET['success']) && $_GET['success'] === 'invited') : ?>
        <p class="success-message"><?php _e('Invite sent successfully!', 'rslfranchise'); ?></p>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('instructor-messages').scrollIntoView({ behavior: 'smooth' });
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error_messages)) : ?>
        <?php foreach ($error_messages as $error) : ?>
            <p class="error-message"><?php echo esc_html($error); ?></p>
        <?php endforeach; ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('instructor-messages').scrollIntoView({ behavior: 'smooth' });
            });
        </script>
    <?php endif; ?>

</div>

<a class="dashboard-section-link" data-bs-toggle="collapse" href="#collapse-link-instructor" role="button" aria-expanded="false" aria-controls="collapse-link-instructor">
    <?php _e('Link Instructor', 'rslfranchise'); ?>
</a>

<div class="link-instructor-section collapse" id="collapse-link-instructor">
    <div class="collapse-inner">

        <h3><?php _e('Link an Instructor', 'rslfranchise'); ?></h3>
        <p><?php _e('Enter the email of an instructor to link with them.', 'rslfranchise'); ?></p>

        <form method="POST" class="link-instructor-form">
            <?php wp_nonce_field('link_instructor_action', 'link_instructor_nonce'); ?>

            <div class="form-group">
                <label for="instructor_email"><?php _e('Instructor Email', 'rslfranchise'); ?></label>
                <input type="email" id="instructor_email" name="instructor_email" required>
            </div>

            <button type="submit"><?php _e('Link Instructor', 'rslfranchise'); ?></button>
        </form>

    </div>
</div>