<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
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
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="theme-color" content="#ffffff">

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site menu-clicked">

	<div class="site-container">

		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'rslfranchise' ); ?></a>

		<header id="masthead" class="site-header">

			<div class="mobile-menu-toggle">
				<i class="fa fa-bars" aria-hidden="true"></i>
			</div>

			<div class="site-branding">
				<a href="/">
					<img class="header-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/rs-nopick-logo.svg" />
				</a>
			</div>

			<div class="header-search">

				<form role="search" method="get" class="search-form form-inline" action="/">
					<div class="form-group">
						<i class="fa fa-search search-icon" aria-hidden="true"></i>
						<label class="sr-only">
							<span class="screen-reader-text sr-only">Search</span>
						</label>
						<input type="search" class="search-field form-control ui-autocomplete-input" placeholder="Search..." title="Search for:" value="" name="s">
						<button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
					</div>
				</form>

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

			<div class="header-profile">
				<?php 
				$current_user = wp_get_current_user();
				$profile_avatar = get_avatar(get_current_user_id(), 50); 
				?>
				<div class="header-name"><?php echo $current_user->user_firstname; ?></div>
				<div class="header-avatar"><?php echo $profile_avatar; ?></div>
				<!-- <div class="viewing-as"><?php _e('Viewing as', 'rslfranchise'); ?></div>	 -->
			</div>

		</header><!-- #masthead -->

		<?php get_template_part( 'section-templates/section', 'sidebar-menu' );  ?>

		<div class="main-container">

			<?php 
			// LearnDash course progress bar - displays on all course content templates unless SoundSlice embed present 
			$post_type = get_post_type();
			if (!get_field('track_id')) :
				if ( $post_type === 'sfwd-courses' || $post_type === 'sfwd-lessons' || $post_type === 'sfwd-topic' || $post_type === 'sfwd-quiz' ) : 
					// if on a LearnDash course element...
					?>

					<div class="header-course-progress">

						<?php 
						if (is_singular('sfwd-courses')) {
						    $course_id = get_the_ID();
						} else {
						    $course_id = learndash_get_course_id(get_the_ID());
						}
						
						$user_id = get_current_user_id();

						if ($course_id && $user_id) {

						    global $post; // override LearnDash context for the course
						    $original_post = $post; 
						    $post = get_post($course_id); 

							$shortcode_out = do_shortcode( '[ld_infobar course_id="' . $course_id . '" user_id="' . $user_id . '"]' );
							echo $shortcode_out; 

							$post = $original_post; // original post
						    wp_reset_postdata(); 

						} else {
							echo 'Course progress could not be displayed.';
						}
						?>

					</div>

				<?php // end LearnDash course element check
				endif; 
			endif;?>

			

