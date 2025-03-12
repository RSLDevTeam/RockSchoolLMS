<?php
/**
 * Template part for displaying courses within a loop with learner assignment feature.
 *
 * @package rslfranchise
 */

$course_id = get_the_ID();

global $current_user;
wp_get_current_user();
$acf_user_id = 'user_' . $current_user->ID;

// Course transfer if $allow_parent_transfer

// Get linked learners if the user is an admin or parent
$linked_learners = [];
if ((in_array('administrator', $current_user->roles) || in_array('parent', $current_user->roles)) && $allow_parent_transfer) {
    $linked_learners = get_field('linked_learners', $acf_user_id) ?: [];
}
// check if in SOW group
$group_category = get_field('schemes_of_work_taxonomy', 'option');
$classes = '';
$group_ids = learndash_get_course_groups($course_id);
if (!empty($group_ids)) {
    foreach ($group_ids as $group_id) {
        if (has_term($group_category, 'ld_group_category', $group_id)) {
            $classes = 'sow-course';
            break;
        }
    }
}

?>

<div class="course-card-inner <?php echo $classes; ?>">

    <?php if (has_post_thumbnail()) : ?>
        <a href="<?php echo get_permalink(); ?>">
            <div class="course-thumbnail">
                <?php the_post_thumbnail('large'); ?>
            </div>
        </a>
    <?php endif; ?>

    <div class="course-card-copy">

        <h3><?php the_title(); ?></h3>

        <?php
        $levels = get_the_terms(get_the_ID(), 'ld_course_level');
        $categories = get_the_terms(get_the_ID(), 'ld_course_category');
        $terms = get_the_terms(get_the_ID(), 'ld_course_term');
        ?>

        <div class="course-meta">
            <?php if ($categories && !is_wp_error($categories)) : ?>
                <div class="course-category course-meta-item"><strong>Category:</strong>
                    <span><?php echo esc_html(implode(', ', wp_list_pluck($categories, 'name'))); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($levels && !is_wp_error($levels)) : ?>
                <div class="course-level course-meta-item"><strong>Level:</strong>
                    <span><?php echo esc_html(implode(', ', wp_list_pluck($levels, 'name'))); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($terms && !is_wp_error($terms)) : ?>
                <div class="course-term course-meta-item"><strong>Term:</strong>
                    <span><?php echo esc_html(implode(', ', wp_list_pluck($terms, 'name'))); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="course-progress">
            <?php echo do_shortcode('[learndash_course_progress course_id="' . get_the_ID() . '"]'); ?>
        </div>

        <?php if (!empty($linked_learners)) : ?>
            <!-- Course transfer -->
            <div class="assign-course-to-learner">
                <h3><?php _e('Assign Course to Learner', 'rslfranchise'); ?></h3>

                <form method="POST">
                    <?php wp_nonce_field('assign_course_action', 'assign_course_nonce'); ?>
                    <input type="hidden" name="course_id" value="<?php echo get_the_ID(); ?>">

                    <div class="form-group">
                        <label for="learner_select_<?php echo get_the_ID(); ?>"><?php _e('Select Learner:', 'rslfranchise'); ?></label>
                        <select name="learner_id" id="learner_select_<?php echo get_the_ID(); ?>" required>
                            <option value=""><?php _e('Choose a learner', 'rslfranchise'); ?></option>
                            <?php foreach ($linked_learners as $learner) : ?>
                                <option value="<?php echo esc_attr($learner['ID']); ?>">
                                    <?php echo esc_html($learner['display_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="assign-course-btn"><?php _e('Assign Course', 'rslfranchise'); ?></button>
                </form>
            </div>
            <!-- Course transfer end -->
        <?php endif; ?>

    </div>
</div>

<?php
// Handle course assignment on form submission
if (!empty($_POST['assign_course_nonce']) && wp_verify_nonce($_POST['assign_course_nonce'], 'assign_course_action')) {
    $course_id = intval($_POST['course_id']);
    $learner_id = intval($_POST['learner_id']);

    // Ensure the learner is linked to the current user and the course ID is valid
    if (in_array($learner_id, array_column($linked_learners, 'ID')) && get_post_type($course_id) === 'sfwd-courses') {
        // Unenroll the parent/admin from the course
        ld_update_course_access($current_user->ID, $course_id, true); // Unenroll current user (parent/admin)

        // Enroll the learner in the course
        ld_update_course_access($learner_id, $course_id, false);

        // Redirect with success message and link to manage learner
        $redirect_url = add_query_arg([
            'assigned'   => '1',
            'course_id'  => $course_id,
            'learner_id' => $learner_id
        ], home_url($_SERVER['REQUEST_URI']));

        wp_safe_redirect($redirect_url);
        exit;
    } else {
        $error_message = __('Invalid learner or course.', 'rslfranchise');
    }
}
// course transfer gubbins end
