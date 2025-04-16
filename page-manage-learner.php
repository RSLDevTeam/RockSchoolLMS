<?php
/*
Template Name: Manage user template
Template Post Type: page
*/

get_header();
global $current_user;
wp_get_current_user();
$acf_user_id = 'user_' . $current_user->ID;

$success_message = '';
$error_messages = [];
$access_error = false;
$role_permissions = false;

// Get the learner ID from the URL
$learner_id = isset($_GET['learner_id']) ? intval($_GET['learner_id']) : 0;
$learner_acf_user_id = 'user_' . $learner_id;
$is_child = get_field('is_child', $learner_acf_user_id);

if ((in_array('administrator', $current_user->roles) || in_array('parent', $current_user->roles) || in_array('instructor', $current_user->roles)) && $is_child) {
    $role_permissions = true;
}

// Validate learner ID
$linked_learners = get_field('linked_learners', $acf_user_id);
$linked_learner_ids = is_array($linked_learners) ? array_column($linked_learners, 'ID') : [];

if (!$learner_id || empty($linked_learner_ids) || !in_array($learner_id, $linked_learner_ids) || !$role_permissions) {
    $error_messages[] = __('Invalid learner ID or you do not have permission to manage this learner.', 'rslfranchise');
    $access_error = true;
}

$learner_user = (!$error_messages) ? get_userdata($learner_id) : false;

if (!$learner_user && !$error_messages) {
    $error_messages[] = __('Learner not found.', 'rslfranchise');
    $access_error = true;
}

$first_name = $learner_user ? ($learner_user->first_name ?: '') : '';
$last_name = $learner_user ? ($learner_user->last_name ?: '') : '';

// Handle learner details update form submission
if ($learner_user && !empty($_POST['manage_learner_nonce']) && wp_verify_nonce($_POST['manage_learner_nonce'], 'manage_learner_action')) {
    $first_name = sanitize_text_field($_POST['learner_first_name']);
    $last_name = sanitize_text_field($_POST['learner_last_name']);
    $email = isset($_POST['learner_email']) ? sanitize_email($_POST['learner_email']) : '';
    $password = !empty($_POST['learner_password']) ? $_POST['learner_password'] : '';

    // Validate input
    if (empty($first_name) || empty($last_name)) {
        $error_messages[] = __('First name and last name are required.', 'rslfranchise');
    }

    if (!empty($email) && !is_email($email)) {
        $error_messages[] = __('Please enter a valid email address.', 'rslfranchise');
    }

    if (!empty($password)) {
        if (strlen($password) < 8) {
            $error_messages[] = __('Password must be at least 8 characters.', 'rslfranchise');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $error_messages[] = __('Password must contain at least one uppercase letter.', 'rslfranchise');
        }
        if (!preg_match('/[a-z]/', $password)) {
            $error_messages[] = __('Password must contain at least one lowercase letter.', 'rslfranchise');
        }
        if (!preg_match('/[0-9]/', $password)) {
            $error_messages[] = __('Password must contain at least one number.', 'rslfranchise');
        }
        if (!preg_match('/[\W_]/', $password)) {
            $error_messages[] = __('Password must contain at least one special character.', 'rslfranchise');
        }
    } 

    if (empty($error_messages)) {
        $user_update_data = [
            'ID'           => $learner_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => "$first_name $last_name",
        ];

        if (!empty($email)) {
            $user_update_data['user_email'] = $email;
        }

        $update_result = wp_update_user($user_update_data);

        if (!is_wp_error($update_result)) {
            
            if (!empty($password) && function_exists('sync_user_to_cognito')) {
                sync_user_to_cognito($learner_id, $password);
            }
            if (!empty($password)) {
                wp_set_password($password, $learner_id);
            }

            wp_safe_redirect(add_query_arg(['learner_id' => $learner_id, 'updated' => '1'], get_permalink()));

            exit;

        } else {
            $error_messages[] = $update_result->get_error_message();
        }
    }
}

// Handle course revocation form submission
if (!empty($_POST['revoke_course_nonce']) && wp_verify_nonce($_POST['revoke_course_nonce'], 'revoke_course_action')) {
    $course_id = intval($_POST['course_id']);

    if (in_array($learner_id, $linked_learner_ids) && get_post_type($course_id) === 'sfwd-courses') {
        ld_update_course_access($learner_id, $course_id, true); // Unenroll learner
        ld_update_course_access($current_user->ID, $course_id, false); // Re-enroll parent/admin

        wp_safe_redirect(add_query_arg(['learner_id' => $learner_id, 'revoked' => '1', 'course_id' => $course_id], get_permalink()));
        exit;
    } else {
        $error_messages[] = __('Failed to revoke course access. Please try again.', 'rslfranchise');
    }
}

// Check for success messages
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $success_message = __('Learner details updated successfully!', 'rslfranchise');
} elseif (isset($_GET['revoked']) && $_GET['revoked'] === '1') {
    $success_message = __('Course access revoked and reassigned to you.', 'rslfranchise');
}
?>

<main id="primary" class="site-main dashboard-page">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="entry-content">
                <h1><?php _e('Manage Learner', 'rslfranchise'); ?><?php if ($first_name) { echo ' - ' . esc_html($first_name . ' ' . $last_name); } ?></h1>

                <div id="learner-messages">
                    <?php if (!empty($success_message)) : ?>
                        <p class="success-message"><?php echo esc_html($success_message); ?></p>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                document.getElementById('learner-messages').scrollIntoView({ behavior: 'smooth' });
                            });
                        </script>
                    <?php endif; ?>

                    <?php if (!empty($error_messages)) : ?>
                        <?php foreach ($error_messages as $error) : ?>
                            <p class="error-message"><?php echo esc_html($error); ?></p>
                        <?php endforeach; ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                document.getElementById('learner-messages').scrollIntoView({ behavior: 'smooth' });
                            });
                        </script>
                    <?php endif; ?>
                </div>

                <?php the_content(); ?>

                <?php if ($learner_user && !$access_error) : ?>
                    <section class="dashboard-section">
                        <h2><?php _e('Update Learner', 'rslfranchise'); ?></h2>

                        <p><?php _e('Use this form to update the account details for this child learner. Email address is optional and only recommended for older learners.', 'rslfranchise'); ?></p>

                        <form method="POST" class="manage-learner-form add-learner-form">
                            <?php wp_nonce_field('manage_learner_action', 'manage_learner_nonce'); ?>

                            <div class="form-group">
                                <label for="learner_first_name"><?php _e('First Name', 'rslfranchise'); ?></label>
                                <input type="text" id="learner_first_name" name="learner_first_name" value="<?php echo esc_attr($first_name); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="learner_last_name"><?php _e('Last Name', 'rslfranchise'); ?></label>
                                <input type="text" id="learner_last_name" name="learner_last_name" value="<?php echo esc_attr($last_name); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="learner_email"><?php _e('Email (optional)', 'rslfranchise'); ?></label>
                                <input type="email" id="learner_email" name="learner_email" value="<?php echo esc_attr($learner_user->user_email); ?>">
                            </div>

                            <div class="form-group">
                                <label for="learner_password"><?php _e('New Password (optional, must be at least 8 characters, include uppercase, lowercase, a number, and a special character.)', 'rslfranchise'); ?></label>
                                <input type="password" id="learner_password" name="learner_password"
                                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                                       title="Password must be at least 8 characters, include uppercase, lowercase, a number, and a special character."
                                       placeholder="<?php _e('Leave blank to keep current password', 'rslfranchise'); ?>">
                            </div>

                            <button type="submit"><?php _e('Update Learner', 'rslfranchise'); ?></button>
                        </form>
                    </section>

                    <section class="learner-courses">
                        <?php $enrolled_courses = learndash_user_get_enrolled_courses($learner_id); ?>

                        <?php if (!empty($enrolled_courses)) : ?>
                            <h2><?php _e('Enrolled Courses', 'rslfranchise'); ?></h2>
                            <p><?php _e('Manage the learner’s enrolled courses and progress.', 'rslfranchise'); ?></p>

                            <div class="row">
                                <?php foreach ($enrolled_courses as $course_id) : 
                                    $course_title = get_the_title($course_id);
                                    $progress = learndash_course_progress([
                                        'user_id'   => $learner_id,
                                        'course_id' => $course_id,
                                        'array'     => true,
                                    ]);
                                    $progress_percent = !empty($progress['percentage']) ? $progress['percentage'] : 0;
                                ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <div class="course-card-inner">
                                            <?php if (has_post_thumbnail($course_id)) : ?>
                                                <div class="course-thumbnail">
                                                    <?php echo get_the_post_thumbnail($course_id, 'large'); ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="course-card-copy">
                                                <h3><?php echo esc_html($course_title); ?></h3>

                                                <div class="learndash-wrapper learndash-widget">
                                                    <div class="ld-progress ld-progress-inline">
                                                        <div class="ld-progress-heading">
                                                            <div class="ld-progress-percentage ld-secondary-color">
                                                                <?php echo esc_html($progress_percent) . '% Complete'; ?>
                                                            </div>
                                                        </div>
                                                        <div class="ld-progress-bar">
                                                            <div class="ld-progress-bar-percentage ld-secondary-background" style="width:<?php echo esc_attr($progress_percent); ?>%"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <form method="POST" class="revoke-course-form" style="margin-top: 10px;">
                                                    <?php wp_nonce_field('revoke_course_action', 'revoke_course_nonce'); ?>
                                                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>">
                                                    <button type="submit" class="revoke-course-btn">
                                                        <?php _e('Revoke Access', 'rslfranchise'); ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <p><?php _e('This learner is not enrolled in any courses.', 'rslfranchise'); ?></p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->
    <?php endwhile; ?>
</main><!-- #main -->

<script>
    // Form validation
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.manage-learner-form');
    const passwordInput = document.getElementById('learner_password');

    if (!form) return;

    form.addEventListener('submit', (e) => {
        const password = passwordInput.value;
        const errors = [];

        if (password.length > 0) {
            if (password.length < 8) {
                errors.push("Password must be at least 8 characters.");
            }
            if (!/[A-Z]/.test(password)) {
                errors.push("Password must include at least one uppercase letter.");
            }
            if (!/[a-z]/.test(password)) {
                errors.push("Password must include at least one lowercase letter.");
            }
            if (!/[0-9]/.test(password)) {
                errors.push("Password must include at least one number.");
            }
            if (!/[\W_]/.test(password)) {
                errors.push("Password must include at least one special character.");
            }
        }

        if (errors.length) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
});
</script>

<?php get_sidebar(); get_footer(); ?>