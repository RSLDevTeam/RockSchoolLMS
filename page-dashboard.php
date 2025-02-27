<?php
/*
Template Name: Dashboard
Template Post Type: page
*/

get_header();

global $current_user; 
wp_get_current_user();
$user_id = $current_user->ID;
$acf_user_id = 'user_' . $current_user->ID;
?>

<main id="primary" class="site-main dashboard-page">

	<?php
	while ( have_posts() ) :
		the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<div class="entry-content">

                <h1><?php _e('Hi', 'rslfranchise'); echo ' ' . $current_user->display_name; ?></h1>

				<?php the_content(); ?>

				<section class="dashboard-primary-section">

					<div class="row">

						<div class="col-lg-4">

							<?php get_template_part( 'snippets/dashboard', 'account' ); ?>

						</div>

						<?php if ( in_array('administrator', $current_user->roles) || in_array('instructor', $current_user->roles) ) : ?>

							<div class="col-lg-4 col-sm-6">

								<?php get_template_part( 'snippets/dashboard', 'training' ); ?>

							</div>

						<?php endif; ?>

						<?php if ( in_array('administrator', $current_user->roles) || in_array('instructor', $current_user->roles) ) : ?>

							<div class="col-lg-4 col-sm-6">

								<?php get_template_part( 'snippets/dashboard', 'sow' ); ?>

							</div>

						<?php endif; ?>

					</div>

				</section>

				<?php if ( in_array('administrator', $current_user->roles) || in_array('learner', $current_user->roles) || in_array('parent', $current_user->roles) ) : 

					get_template_part( 'snippets/dashboard', 'linked-instructors' ); 
					
				endif;	?>

				<?php if ( in_array('administrator', $current_user->roles) || in_array('instructor', $current_user->roles) || in_array('parent', $current_user->roles) ) : 

					get_template_part( 'snippets/dashboard', 'linked-learners' ); 
					
				endif;	?>

				<?php get_template_part( 'section-templates/section', 'flex-content' ); ?>

			</div><!-- .entry-content -->

		</article><!-- #post-<?php the_ID(); ?> -->

	<?php endwhile; // End of the loop.
	?>

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
