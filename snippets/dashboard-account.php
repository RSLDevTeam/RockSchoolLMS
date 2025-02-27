<?php
/**
 * Dashboard account widget
 *
 */

// Exit if accessed directly.

global $current_user; 
wp_get_current_user();
$user_id = $current_user->ID;
$acf_user_id = 'user_' . $current_user->ID;
?>

<section class="dashboard-section">
	<h3><?php _e('Account', 'rslfranchise'); ?></h3>
	<p>
		<?php 
		echo '<b>Name:</b> ' . $current_user->display_name . '</br>';
		if (!empty($current_user->roles)) {
		    echo '<b>Account type:</b> ' . implode(', ', $current_user->roles);
		} else {
		    echo 'No role assigned.';
		}

		if ( in_array('administrator', $current_user->roles) || in_array('instructor', $current_user->roles) ) {

			if (get_field('is_institution', $acf_user_id)) {
				$institution_tag = 'Centre instructor';
			} else {
				$institution_tag = 'Solo instructor';
			} 

			echo '</br><b>Instructor type:</b> ' . $institution_tag;
		}
		?>
	</p>
	<hr>
	<a href="/account/" class="dashboard-section-link"><?php _e('Manage account', 'rslfranchise'); ?></a>

</section>