<?php
/**
 * LearnDash content sidebar - Appears on lessons, topic and quizes
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<section class="dashboard-section ld-content-sidebar">

	<div class="ld-content-sidebar-nav-toggle"><?php _e('Contents', 'rslfranchise');  ?> <i class="fa fa-angle-down" aria-hidden="true"></i></div>

	<div class="ld-content-sidebar-inner">

		<?php echo do_shortcode('[course_content course_id="' . $course_id . '"]'); ?>

		<a href="<?php echo get_the_permalink($course_id); ?>" class="sidebar-back"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>

	</div>

</section>

	





