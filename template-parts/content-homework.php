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
$homework_id = get_the_ID();
$submitted_files = get_post_meta($homework_id, 'project_file_url');
$bucketName = get_field('bucket_name', 'option');
$endpoint = 'https://' . $endpointPath;
$response = get_field('response');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content homework-content">

		<a href="/homework/"><div class="back-to-link"><i class="fa fa-arrow-left" aria-hidden="true"></i><?php _e('All homework', 'rslfranchise'); ?></div></a>

		<?php if (!$current_user_id || (!$is_admin && $current_user_id !== $learner_id && $current_user_id !== $instructor_id)) : ?>

			<p><?php _e('You do not have permission to access this assignment.', 'rslfranchise'); ?></p>

		<?php else : ?>

			<header class="entry-header">
				<h1 class="entry-title"><?php echo get_field( 'title' ); ?></h1>
				<p><?php _e('This task was assigned to you by', 'rslfranchise'); echo ' ' . $instructor['display_name']; ?></p>
			</header><!-- .entry-header -->

			<section class="homework-thread">

				<div class="homework-intructor-message homework-message-item">

					<div class="homework-person">
						<?php echo $instructor_avatar; ?>
						<strong><?php echo esc_html($instructor_name); ?> </strong>
					</div>

					<div class="homework-message">
						<?php echo get_field('task'); ?>
					</div>

				</div>

				<?php if (get_field('response')) : ?>
					<div class="homework-learner-message homework-message-item">

						<div class="homework-message">
							<?php echo get_field('response'); ?>
							<?php if ( !empty($submitted_files) ): ?>
					            <ul>
					                <?php foreach ($submitted_files as $file_url): ?>
					                    <?php
					                    $file_key = str_replace($endpoint . $bucketName . '/', '', $file_url);
					                    $presigned_url = generate_presigned_view_url($file_key);
					                    ?>
					                    <li><a href="<?php echo esc_url($presigned_url); ?>" target="_blank"><?php echo basename($file_url); ?></a></li>
					                <?php endforeach; ?>
					            </ul>
					        <?php endif; ?>
						</div>

						<div class="homework-person">
							<?php echo $learner_avatar; ?>
							<strong><?php echo esc_html($learner_name); ?> </strong>
						</div>

					</div>
				<?php endif; ?>

			</section>

			<?php if (!$response) : ?>

				<section class="dashboard-section">
					<h2><?php _e('Homework response', 'rslfranchise'); ?></h2>
					<?php echo do_shortcode('[project_submission_ajax_s3]'); ?>
				</section>

			<?php endif; ?>
		
		<?php endif; ?>

	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
