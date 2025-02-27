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

	<h3><?php _e('Instructor Training', 'rslfranchise'); ?></h3>

	<?php
	$user_id = $current_user->ID;
	$group_id = get_field('instructor_group', 'option');

	// Fetch all courses the user is explicitly enrolled in
	$enrolled_courses = learndash_user_get_enrolled_courses($user_id);

	// Filter only courses that belong to this group
	$group_courses = [];
	if (!empty($enrolled_courses)) {
	    foreach ($enrolled_courses as $course_id) {
	        $course_groups = learndash_get_course_groups($course_id);
	        if (in_array($group_id, $course_groups)) {
	            $group_courses[] = $course_id;
	        }
	    }
	}

	$total_courses = count($group_courses);
	$completed_courses_count = 0;

	// Check progress for each course
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
	?>

	<div class="section-widget-metrics">
	    <div class="section-widget-metric">
	        <div class="metric-number"><?php echo $total_courses; ?></div>
	        <div class="metric-title"><?php _e('Available courses', 'rslfranchise'); ?></div>
	    </div>
	    <div class="section-widget-metric">
	        <div class="metric-number"><?php echo $completed_courses_count; ?></div>
	        <div class="metric-title"><?php _e('Completed courses', 'rslfranchise'); ?></div>
	    </div>
	</div>

	<hr>
	<a href="/training/" class="dashboard-section-link"><?php _e('View Instructor Training', 'rslfranchise'); ?></a>
	
</section>