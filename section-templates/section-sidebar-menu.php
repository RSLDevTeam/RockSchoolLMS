<?php
/**
 * Sidebar menu
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<div class="vertical-menu">
		
	<div class="vertical-menu-inner-top">

		<section class="sidebar-top">

			<div class="site-branding">
				<a href="/">
					<img class="header-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/RS-Logo-TM-Inline-Wte.svg" />
					<img class="header-logo-m" src="<?php echo get_stylesheet_directory_uri(); ?>/img/r-school-circle-logo.svg" />
				</a>
			</div>

			<div class="navigation__burger">
				<span class="navigation__burger-el navigation__burger-el--top"></span>
				<span class="navigation__burger-el navigation__burger-el--middle"></span>
				<span class="navigation__burger-el navigation__burger-el--bottom"></span>
			</div>

		</section>

		<nav id="sidebar-navigation" class="sidebar-navigation">
					
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'sidebar-menu',
				)
			);
			?>

		</nav><!-- #site-navigation -->

	</div>

	<div class="vertical-menu-inner-bottom">

		<nav id="sidebar-misc-menu" class="sidebar-navigation">

			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-3',
					'menu_id'        => 'utility-menu',
				)
			);
			?>

			<ul>
				<li><a href="/wp-login.php?action=logout"><i class="fa fa-sign-out" aria-hidden="true"></i> <span>Sign out</span></a></li>
			</ul>
			
		</nav>

	</div>

</div>