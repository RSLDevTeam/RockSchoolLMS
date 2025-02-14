<?php
/**
 * Sidebar menu
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<div class="vertical-menu vertical-menu-clicked">
		
	<div class="vertical-menu-inner-top">

		<nav id="sidebar-navigation" class="sidebar-navigation">
					
			<?php

			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'sidebar-menu',
				)
			);

			if ( current_user_can('administrator') || current_user_can('instructor') ) {
			    wp_nav_menu(
				array(
					'theme_location' => 'menu-5',
					'menu_id'        => 'instructor-menu',
					)
				);
			}


			if ( current_user_can('administrator') || current_user_can('learner') ) {
			    wp_nav_menu(
				array(
					'theme_location' => 'menu-4',
					'menu_id'        => 'learner-menu',
					)
				);
			}

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