<?php
/*
Template Name: Policy page
Template Post Type: page
*/

get_header();
?>

	<main id="primary" class="site-main policy-page">

		<?php
		while ( have_posts() ) :
			the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="entry-content">

					<h1><?php the_title(); ?></h1>

					<?php the_content(); ?>

				</div><!-- .entry-content -->

			</article><!-- #post-<?php the_ID(); ?> -->

		<?php endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
get_footer();
