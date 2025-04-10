<?php
/**
 * Dashboard training widget
 *
 */

// Exit if accessed directly.

global $current_user; 
wp_get_current_user();
$user_id = $current_user->ID;
$acf_user_id = 'user_' . $current_user->ID;
?>

<section class="dashboard-section">
	<h3><?php _e('Schemes of Lessons', 'rslfranchise'); ?></h3>

	<?php
	$group_category = get_field('schemes_of_work_taxonomy', 'option');

	$total_courses = 0;
	$completed_courses_count = 0;

	// Get all groups in the specified category
	$args = [
	    'taxonomy'   => 'ld_group_category',
	    'field'      => 'term_id',
	    'terms'      => $group_category,
	    'hide_empty' => false,
	];

	$groups = get_posts([
	    'post_type'   => 'groups',
	    'tax_query'   => [$args],
	    'numberposts' => -1,
	    'fields'      => 'ids',
	]);

	$group_courses = [];
	$enrolled_courses = learndash_user_get_enrolled_courses($user_id);

	if (!empty($groups) && !empty($enrolled_courses)) {
	    foreach ($groups as $group_id) {
	        foreach ($enrolled_courses as $course_id) {
	            $course_groups = learndash_get_course_groups($course_id);
	            if (in_array($group_id, $course_groups)) {
	                $group_courses[] = $course_id;
	            }
	        }
	    }

	    // Remove duplicates
	    $group_courses = array_unique($group_courses);
	    $total_courses = count($group_courses);

	    foreach ($group_courses as $course_id) {
	        $progress = learndash_course_progress([
	            'user_id'   => $user_id,
	            'course_id' => $course_id,
	            'array'     => true, 
	        ]);

	        if (!empty($progress) && isset($progress['percentage']) && $progress['percentage'] == 100) {
	            $completed_courses_count++;
	        }
	    }
	}
	?>

	<div class="section-widget-metrics">
	    <div class="section-widget-metric">
	        <div class="metric-number"><?php echo $total_courses; ?></div>
	        <div class="metric-title"><?php _e('Available schemes of work', 'rslfranchise'); ?></div>
	    </div>
	</div>

	<hr>
	<a href="/schemes-of-work/" class="dashboard-section-link">
	    <?php _e('View Schemes of Work', 'rslfranchise'); ?>
	</a>

</section>