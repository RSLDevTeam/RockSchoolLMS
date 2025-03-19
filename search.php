<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package rslfranchise
 */

get_header();
?>

	<main id="primary" class="site-main">
		<header class="page-header">
			<h1 class="page-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Search Results for: %s', 'rslfranchise' ), '<span>' . get_search_query() . '</span>' );
				?>
			</h1>
		</header><!-- .page-header -->

		<?php if ( have_posts() ) : ?>

			

			<?php
			$allow_parent_transfer = get_sub_field('allow_parent_transfer');
			$grouped_posts = [];
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				$course_id = get_the_ID();

				// Get all LearnDash groups this course belongs to
				$group_ids = learndash_get_course_groups($course_id);

				if (!empty($group_ids)) {
						foreach ($group_ids as $group_id) {
								// Get group categories (taxonomy: `ld_group_category`)
								$group_categories = wp_get_post_terms($group_id, 'ld_group_category');

								if (!empty($group_categories)) {
										foreach ($group_categories as $category) {
												$category_name = $category->name;
												$courses_by_category[$category_name][] = $course_id;
										}
								} else {
										// If group has no category, store under "Uncategorized"
										$courses_by_category['Uncategorized'][] = $course_id;
								}
						}
				} else {
						// If course is not in a group, store it under "Ungrouped Courses"
						$courses_by_category['Ungrouped Courses'][] = $course_id;
				}
			endwhile;
			

			// Remove duplicate course IDs in each category
			foreach ($courses_by_category as $category_name => $courses) {
				$courses_by_category[$category_name] = array_unique($courses);
			}
		
			set_query_var('courses_by_category', $courses_by_category);
			set_query_var('allow_parent_transfer', $allow_parent_transfer);
			set_query_var('posts', have_posts());
			get_template_part( 'template-parts/content', 'search' );
			
		the_posts_navigation();
		else :
			// If no courses found, display a message not found
			?>

			<?php
			get_template_part( 'template-parts/content', 'none' );
			
		endif;
		?>
			

	</main><!-- #main -->

<?php
get_sidebar();
get_footer();
