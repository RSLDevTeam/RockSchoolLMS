<?php
/**
 * Dashboard linked instructors
 *
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

global $current_user;
wp_get_current_user();
$user_id = $current_user->ID;

?>

<section class="dashboard-primary-section">

    <h2><?php _e('Linked Instructors', 'rslfranchise'); ?></h2>

    <p><?php _e('View your linked instructors.', 'rslfranchise'); ?></p>

    <section class="dashboard-section">

        <h3><?php _e('Instructors', 'rslfranchise'); ?></h3>

        <?php 
        // Query users who have the current user in their 'linked_learners' field
        $args = [
            'role__in'   => ['instructor', 'administrator'], 
            'number'     => -1,
            'meta_query' => [
                [
                    'key'     => 'linked_learners', 
                    'value'   => '"' . $user_id . '"', 
                    'compare' => 'LIKE'
                ]
            ]
        ];
        $user_query = new WP_User_Query($args);
        $linked_instructors = $user_query->get_results();

        if (!empty($linked_instructors)) : ?>

            <div class="linked-learners-list">

                <?php foreach ($linked_instructors as $instructor) :
                    $instructor_id    = $instructor->ID;
                    $instructor_name  = $instructor->display_name;
                    $instructor_email = $instructor->user_email;
                    $instructor_avatar = get_avatar($instructor_id, 50);
                ?>

                    <div class="linked-learner">
                        <?php echo $instructor_avatar; ?>
                        <div class="learner-details">
                            <div class="learner-details-copy">
                                <strong><?php echo esc_html($instructor_name); ?></strong>
                                <?php if ($instructor_email) : ?>
                                    <a class="linked-learner-email" href="mailto:<?php echo esc_attr($instructor_email); ?>">
                                        <?php echo esc_attr($instructor_email); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <button data-bs-toggle="modal" data-bs-target="#learner-modal-<?php echo $instructor_id; ?>">
                                <?php _e('View', 'rslfranchise'); ?>
                            </button>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php else : ?>

            <p><?php _e('No linked instructors found.', 'rslfranchise'); ?></p>

        <?php endif; ?>

        <?php get_template_part( 'snippets/dashboard', 'add-linked-instructor' ); ?>

    </section>

</section>

<?php if (!empty($linked_instructors)) : 
	foreach ($linked_instructors as $instructor) : 
		
		$instructor_id    = $instructor->ID;
        $instructor_name  = $instructor->display_name;
        $instructor_email = $instructor->user_email;
        $instructor_avatar = get_avatar($instructor_id, 50);
        ?>

		<!-- Modal -->
		<div class="modal fade learner-modal" id="learner-modal-<?php echo $instructor_id; ?>" tabindex="-1" aria-labelledby="learner-modal-<?php echo $instructor_id; ?>Label" aria-hidden="true">
		  	<div class="modal-dialog modal-dialog-centered">
		    	<div class="modal-content">

			      	<div class="modal-header">

			      		<?php echo $instructor_avatar; ?>

			        	<h5 class="modal-title" id="learner-modal-<?php echo $instructor_id; ?>Label"><?php echo $instructor_name; ?></h5>

			        	<?php if ( $instructor_email ) : ?>
			        	<a href="mailto:<?php echo esc_attr($instructor_email); ?>"><?php echo esc_attr($instructor_email); ?></a>
			        	<?php endif; ?>

                        <?php
                        if (get_field('is_institution', $acf_user_id)) {
                            $institution_tag = 'Centre instructor';
                        } else {
                            $institution_tag = 'Solo instructor';
                        } 

                        echo '<b>Instructor type:</b> ' . $institution_tag;
                        ?>

			      	</div>

			      	<div class="modal-body">
			        
                        <!-- No content here yet -->

			      	</div>

			      	<div class="modal-footer">

			        	<button type="button" data-bs-dismiss="modal"><?php _e('Close', 'rslfranchise'); ?></button>

			      	</div>

		    	</div>
		  	</div>
		</div>

	<?php endforeach;
endif; ?>