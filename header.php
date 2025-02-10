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

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<div class="site-container">

		<?php get_template_part( 'section-templates/section', 'sidebar-menu' );  ?>

		<div class="main-container">

			<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'rslfranchise' ); ?></a>

			<header id="masthead" class="site-header">

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

			</header><!-- #masthead -->

