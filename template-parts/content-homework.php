<?php
/**
 * Template part for displaying homework content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package rslfranchise
 */

$learner = get_field('learner'); 
$instructor = get_field('instructor'); 

$current_user_id = get_current_user_id(); 

$learner_id = is_array($learner) && isset($learner['ID']) ? $learner['ID'] : null;
$instructor_id = is_array($instructor) && isset($instructor['ID']) ? $instructor['ID'] : null;

$is_admin = current_user_can('administrator');

$learner_avatar = get_avatar($learner_id, 50); 
$learner_name = $learner['display_name'];
$instructor_avatar = get_avatar($instructor_id, 50); 
$instructor_name = $instructor['display_name'];
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content homework-content">

		<?php if (!$current_user_id || (!$is_admin && $current_user_id !== $learner_id && $current_user_id !== $instructor_id)) : ?>

			<p><?php _e('You do not have permission to access this assignment.', 'rslfranchise'); ?></p>

		<?php else : ?>

			<header class="entry-header">
				<h1 class="entry-title"><?php echo get_field( 'title' ); ?></h1>
				<p><?php _e('This task was assigned to you by', 'rslfranchise'); echo ' ' . $instructor['display_name']; ?></p>
			</header><!-- .entry-header -->

			<section class="homework-thread">
				<div class="homework-intructor-message homework-message-item">
					<div class="homework-message">
						<?php echo get_field('task'); ?>
					</div>
					<div class="homework-person">
						<?php echo $instructor_avatar; ?>
						<strong><?php echo esc_html($instructor_name); ?> </strong>
					</div>
				</div>
			</section>

			<section class="homework-response">
				<h2><?php _e('Homework response', 'rslfranchise'); ?></h2>
				<?php echo get_field('response'); ?>
			</section>

			<section class="dashboard-section"><?php echo do_shortcode('[project_submission_ajax_s3]'); ?></section>
		
		<?php endif; ?>

	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
