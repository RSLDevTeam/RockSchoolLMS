<?php
/*
Template Name: Instructor Training
Template Post Type: page
*/

get_header();
global $current_user; 
wp_get_current_user();
$acf_user_id = 'user_' . $current_user->ID;
?>

	<main id="primary" class="site-main dashboard-page">

		<?php
		while ( have_posts() ) :
			the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="entry-content">

					<h1><?php _e('Instructor Training', 'rslfranchise'); ?></h1>

					<?php the_content(); ?>

                    <div class="row">

                    	<div class="col-lg-3">

                    		<section class="dashboard-section">
                    			<h3><?php _e('Instructor Details', 'rslfranchise'); ?></h3>
                    			<p>
									<?php 
									echo '<b>Name:</b> ' . $current_user->display_name . '</br>';

									if (!empty($current_user->roles)) {
									    echo '<b>Account type:</b> ' . implode(', ', $current_user->roles);
									} else {
									    echo 'No role assigned.';
									}

									if (get_field('is_institution', $acf_user_id)) {
										$institution_tag = 'Centre instructor';
									} else {
										$institution_tag = 'Solo instructor';
									} 

									echo '</br><b>Instructor type:</b> ' . $institution_tag . '</br>';

									if (get_field('onboarding_completed', $acf_user_id)) {
										echo '<b>Onboarding:</b> Completed';
									} else {
										echo '<b>Onboarding:</b> Not complete';
									}
									?>
								</p>
                    		</section>

                    	</div>

                    	<div class="col-lg-9">

                    		<section class="training-section">
                    			<?php 
                    			// echo '<h2>' . get_the_title('99') . '</h2>'; 
                    			// echo do_shortcode('[learndash_course_progress course_id="99"]'); 
                    			// echo get_the_content('99'); 
                    			// echo do_shortcode('[course_content course_id="99"]'); 
                    			?>
                    		</section>

                    		<?php get_template_part( 'section-templates/section', 'flex-content' ); ?>

                    	</div>

                    </div>

					

				</div><!-- .entry-content -->

			</article><!-- #post-<?php the_ID(); ?> -->

		<?php endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
get_sidebar();
get_footer();
