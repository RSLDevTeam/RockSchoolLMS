<?php
/**
 * Dashboard add new linked learner (child)
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user; 
wp_get_current_user();

// Handle form submission
if (!empty($_POST['add_learner_nonce']) && wp_verify_nonce($_POST['add_learner_nonce'], 'add_learner_action')) {

    $learner_username = sanitize_user($_POST['learner_username']);
    $learner_first_name = sanitize_text_field($_POST['learner_first_name']);
    $learner_last_name = sanitize_text_field($_POST['learner_last_name']);
    $learner_password = $_POST['learner_password'];

    $error_messages = [];

    // Validation
    if (empty($learner_username) || empty($learner_first_name) || empty($learner_last_name) || empty($learner_password)) {
        $error_messages[] = __('All fields are required.', 'rslfranchise');
    } elseif (username_exists($learner_username)) {
        $error_messages[] = __('This username is already taken.', 'rslfranchise');
    } elseif (strlen($learner_password) < 6) {
        $error_messages[] = __('Password must be at least 6 characters long.', 'rslfranchise');
    }

    // If no errors, create the user
    if (empty($error_messages)) {
        $learner_id = wp_create_user($learner_username, $learner_password, '');

        if (!is_wp_error($learner_id)) {
            // Update user meta
            wp_update_user([
                'ID' => $learner_id,
                'first_name' => $learner_first_name,
                'last_name' => $learner_last_name,
                'display_name' => $learner_first_name . ' ' . $learner_last_name,
            ]);

            // Assign the 'learner' role and set ACF field
            (new WP_User($learner_id))->set_role('learner');
            update_field('is_child', true, 'user_' . $learner_id);

            // Link learner to parent
            $linked_learners = get_field('linked_learners', 'user_' . $current_user->ID) ?: [];
            $linked_learners[] = $learner_id;
            update_field('linked_learners', $linked_learners, 'user_' . $current_user->ID);

            // Redirect to avoid form resubmission
            wp_safe_redirect(add_query_arg('learner_added', '1', get_permalink()));
            exit;
        } else {
            $error_messages[] = $learner_id->get_error_message();
        }
        
    }
}

$success_message = isset($_GET['learner_added']) ? __('Learner created and linked successfully!', 'rslfranchise') : '';
?>

<hr>

<div id="learner-messages">

    <?php if (!empty($success_message)) : ?>
        <p class="success-message"><?php echo esc_html($success_message); ?></p>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('learner-messages').scrollIntoView({ behavior: 'smooth' });
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error_messages)) : ?>
        <?php foreach ($error_messages as $error) : ?>
            <p class="error-message"><?php echo esc_html($error); ?></p>
        <?php endforeach; ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('learner-messages').scrollIntoView({ behavior: 'smooth' });
            });
        </script>
    <?php endif; ?>

</div>

<a class="dashboard-section-link" data-bs-toggle="collapse" href="#collapse-add-child" role="button" aria-expanded="false" aria-controls="collapse-add-child">
    <?php _e('Add child', 'rslfranchise'); ?>
</a>

<div class="add-learner-section collapse" id="collapse-add-child">
	<div class="collapse-inner">

	    <h3><?php _e('Add New Child Learner', 'rslfranchise'); ?></h3>
	    <p><?php _e('Complete the form below to create a child backstage account', 'rslfranchise'); ?></p>

	    <form method="POST" class="add-learner-form">
	        <?php wp_nonce_field('add_learner_action', 'add_learner_nonce'); ?>

	        <div class="form-group">
	            <label for="learner_username"><?php _e('Username', 'rslfranchise'); ?></label>
	            <input type="text" id="learner_username" name="learner_username" required>
	        </div>

	        <div class="form-group">
	            <label for="learner_first_name"><?php _e('First Name', 'rslfranchise'); ?></label>
	            <input type="text" id="learner_first_name" name="learner_first_name" required>
	        </div>

	        <div class="form-group">
	            <label for="learner_last_name"><?php _e('Last Name', 'rslfranchise'); ?></label>
	            <input type="text" id="learner_last_name" name="learner_last_name" required>
	        </div>

	        <div class="form-group">
	            <label for="learner_password"><?php _e('Password', 'rslfranchise'); ?></label>
	            <input type="password" id="learner_password" name="learner_password" required>
	        </div>

	        <button type="submit"><?php _e('Create Learner', 'rslfranchise'); ?></button>
	    </form>

	</div>
</div>