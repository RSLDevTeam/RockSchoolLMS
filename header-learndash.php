<?php
/**
 * The header for our theme - LearnDash Content Version
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">. Used only on LearnDash content and seperate to the regular header.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package rslfranchise
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<div class="site-container">

		<?php get_template_part( 'section-templates/section', 'sidebar-menu' );  ?>

		<div class="main-container">

			<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'rslfranchise' ); ?></a>

			<header id="masthead" class="site-header header-course-header">

				<div class="header-course-progress">

					<?php 
					// Get LearnDash course ID
					if (is_singular('sfwd-courses')) {
					    $course_id = get_the_ID();
					} else {
					    $course_id = learndash_get_course_id(get_the_ID());
					}
					
					$user_id = get_current_user_id();

					if ($course_id && $user_id) {

					    global $post; // Temporarily override LearnDash context for the course
					    $original_post = $post; // Backup the original post object
					    $post = get_post($course_id); // Set the global post to the course

						$shortcode_out = do_shortcode( '[ld_infobar course_id="' . $course_id . '" user_id="' . $user_id . '"]' );
						echo $shortcode_out; 

						$post = $original_post; // Original post
					    wp_reset_postdata(); // Reset and come back to original post

					} else {
						echo 'Course progress could not be displayed.';
					}
					?>

				</div>

				<nav id="site-navigation" class="main-navigation">
					
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'rslfranchise' ); ?></button>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-2',
							'menu_id'        => 'primary-menu',
						)
					);
					?>

				</nav><!-- #site-navigation -->

			</header><!-- #masthead -->

