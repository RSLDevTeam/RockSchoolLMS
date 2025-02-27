<?php
/**
 * Dashboard linked learners
 *
 */

// Exit if accessed directly.

global $current_user; 
wp_get_current_user();
$user_id = $current_user->ID;
$acf_user_id = 'user_' . $current_user->ID;
?>

<section class="dashboard-primary-section">

	<h2><?php _e('Linked Learners', 'rslfranchise'); ?></h2>

	<p><?php _e('View your linked learners.', 'rslfranchise'); ?></p>

	<section class="dashboard-section">

		<h3><?php _e('Learners', 'rslfranchise'); ?></h3>

		<?php 
		$linked_learners = get_field('linked_learners', 'user_' . $current_user->ID); 
		if (!empty($linked_learners)) : ?>

		    <div class="linked-learners-list">
		    
			    <?php foreach ($linked_learners as $learner) : 
			        $learner_id = $learner['ID'];
			        $learner_name = $learner['display_name'];
			        $learner_email = $learner['user_email'];
			        $learner_avatar = get_avatar($learner_id, 50); 
			        ?>

			        <div class="linked-learner">
			        	<?php echo $learner_avatar; ?>
			        	<div class="learner-details">
			        		<div class="learner-details-copy">
			        			<strong><?php echo esc_html($learner_name); ?> </strong>
			        			<?php if ( $learner_email ) : ?>
			        				<a class="linked-learner-email" href="mailto:<?php echo esc_attr($learner_email); ?>"><?php echo esc_attr($learner_email); ?></a>
			        			<?php endif; ?>
			        		</div>
			        		<button data-bs-toggle="modal" data-bs-target="#learner-modal-<?php echo $learner_id; ?>"><?php _e('View', 'rslfranchise'); ?></button>
			        	</div>
			        </div>

			    <?php endforeach; ?>
		    
		    </div>

		<?php else : ?>

		    <p>No linked learners found.</p>

		<?php endif; ?>

		<?php
		// Check if the current user has the 'parent' role
		if ( in_array('administrator', $current_user->roles) || in_array('parent', $current_user->roles) ) {

			get_template_part( 'snippets/dashboard', 'add-linked-learners' );
			
		} ?>

	</section>

</section>

<?php if (!empty($linked_learners)) : 
	foreach ($linked_learners as $learner) : 
		
		$learner_id = $learner['ID'];
        $learner_name = $learner['display_name'];
        $learner_email = $learner['user_email'];
        $learner_avatar = get_avatar($learner_id, 75); 
        $learner_acf_user_id = 'user_' . $learner_id;
        $is_child = get_field('is_child', $learner_acf_user_id);
        ?>

		<!-- Modal -->
		<div class="modal fade learner-modal" id="learner-modal-<?php echo $learner_id; ?>" tabindex="-1" aria-labelledby="learner-modal-<?php echo $learner_id; ?>Label" aria-hidden="true">
		  	<div class="modal-dialog modal-dialog-centered">
		    	<div class="modal-content">

			      	<div class="modal-header">

			      		<?php echo $learner_avatar; ?>

			        	<h5 class="modal-title" id="learner-modal-<?php echo $learner_id; ?>Label"><?php echo $learner_name; ?></h5>

			        	<?php if ( ( in_array('administrator', $current_user->roles) || in_array('parent', $current_user->roles) ) && $is_child ) { _e('Child account', 'rslfranchise'); } ?>

			        	<?php if ( $learner_email ) : ?>
			        	<a href="mailto:<?php echo esc_attr($learner_email); ?>"><?php echo esc_attr($learner_email); ?></a>
			        	<?php endif; ?>

			      	</div>

			      	<div class="modal-body">
			        <?php
			        // Fetch the learner's enrolled courses
			        $enrolled_courses = learndash_user_get_enrolled_courses($learner_id);

			        if (!empty($enrolled_courses)) : ?>

			            <div class="learner-courses">

			            	<h5 class="modal-title margin-bottom"><?php _e('Enrolled courses', 'rslfranchise'); ?></h5>

			            	<div class="row">

					            <?php foreach ($enrolled_courses as $course_id) :

					                $course_title = get_the_title($course_id);
					                $course_link = get_permalink($course_id);
					                $levels = get_the_terms($course_id, 'ld_course_level');
						            $categories = get_the_terms($course_id, 'ld_course_category');
						            $terms = get_the_terms($course_id, 'ld_course_term');
					                
					                // Get course progress
					                $progress = learndash_course_progress([
					                    'user_id'   => $learner_id,
					                    'course_id' => $course_id,
					                    'array'     => true, 
					                ]);

					                $progress_percent = !empty($progress['percentage']) ? $progress['percentage'] : 0; ?>

					                <div class="col-lg-3 col-md-4 col-sm-6">

					                	<div class="course-card-inner">

					                		<?php if (has_post_thumbnail($course_id)) : ?>
									            <div class="course-thumbnail">
									                <?php echo get_the_post_thumbnail($course_id, 'large'); ?>
									            </div>
									        <?php endif; ?>

									        <div class="course-card-copy">

									        	<h3><?php echo get_the_title($course_id); ?></h3>

									        	<div class="course-meta">

									                <?php if ($categories && !is_wp_error($categories)) : ?>
									                    <div class="course-category course-meta-item"><strong>Category:</strong> 
									                        <span><?php echo esc_html(implode(', ', wp_list_pluck($categories, 'name'))); ?></span>
									                    </div>
									                <?php endif; ?>

									                <?php if ($levels && !is_wp_error($levels)) : ?>
									                    <div class="course-level course-meta-item"><strong>Level:</strong> 
									                        <span><?php echo esc_html(implode(', ', wp_list_pluck($levels, 'name'))); ?></span>
									                    </div>
									                <?php endif; ?>

									                <?php if ($terms && !is_wp_error($terms)) : ?>
									                    <div class="course-term course-meta-item"><strong>Term:</strong> 
									                        <span><?php echo esc_html(implode(', ', wp_list_pluck($terms, 'name'))); ?></span>
									                    </div>
									                <?php endif; ?>

									            </div>

									            <div class="learndash-wrapper learndash-widget">

									            	<div class="ld-progress ld-progress-inline">

									            		<div class="ld-progress-heading">
															<div class="ld-progress-stats">
																<div class="ld-progress-percentage ld-secondary-color">
																	<?php echo esc_html($progress_percent) . '% '; ?> Complete
																</div>
															</div> 
														</div>

											            <div class="ld-progress-bar">
															<div class="ld-progress-bar-percentage ld-secondary-background" style="width:<?php echo esc_html($progress_percent); ?>%"></div>
														</div>

													</div>

												</div>

												<!-- <button class="view-as-button" data-course_id="<?php echo $course_id; ?>" data-learner_id="<?php echo $learner_id; ?>"><?php _e('View as learner', 'rslfranchise'); ?></button> -->

								            </div>

							            </div>

						            </div>

					            <?php endforeach; ?>

					        </div>

			            </div>

			        <?php else : ?>

			            <p>No courses enrolled.</p>

			        <?php endif; ?>

			      	</div>

			      	<div class="modal-footer">

			      		<?php if ( ( in_array('administrator', $current_user->roles) || in_array('parent', $current_user->roles) || in_array('instructor', $current_user->roles) ) && $is_child ) : ?>

			      			<a href="/manage-learner/?learner_id=<?php echo $learner_id; ?>"><button><?php _e('Manage learner', 'rslfranchise'); ?></button></a>

				    	<?php endif; ?>

			        	<button type="button" data-bs-dismiss="modal"><?php _e('Close', 'rslfranchise'); ?></button>

			      	</div>

		    	</div>
		  	</div>
		</div>

	<?php endforeach;
endif; ?>