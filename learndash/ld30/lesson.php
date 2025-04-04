<?php
/**
 * LearnDash LD30 Displays a lesson.
 *
 * Available Variables:
 *
 * $course_id                  : (int) ID of the course
 * $course                     : (object) Post object of the course
 * $course_settings            : (array) Settings specific to current course
 * $course_status              : Course Status
 * $has_access                 : User has access to course or is enrolled.
 *
 * $courses_options            : Options/Settings as configured on Course Options page
 * $lessons_options            : Options/Settings as configured on Lessons Options page
 * $quizzes_options            : Options/Settings as configured on Quiz Options page
 *
 * $user_id                    : (object) Current User ID
 * $logged_in                  : (true/false) User is logged in
 * $current_user               : (object) Currently logged in user object
 *
 * $quizzes                    : (array) Quizzes Array
 * $post                       : (object) The lesson post object
 * $topics                     : (array) Array of Topics in the current lesson
 * $all_quizzes_completed      : (true/false) User has completed all quizzes on the lesson Or, there are no quizzes.
 * $lesson_progression_enabled : (true/false)
 * $show_content               : (true/false) true if lesson progression is disabled or if previous lesson is completed.
 * $previous_lesson_completed  : (true/false) true if previous lesson is completed
 * $lesson_settings            : Settings specific to the current lesson.
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$in_focus_mode = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' );

add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 ); 

$soundslice_id = get_field('track_id');
if ($soundslice_id) { $soundsice_class = 'w-soundslice'; } else { $soundsice_class = ''; }
?>

<article id="post-<?php the_ID(); ?>" <?php post_class($soundsice_class); ?>>

	<div class="row">

		<?php if (!$soundslice_id) : ?>

			<div class="col-lg-3">

				<?php get_template_part( 'section-templates/section', 'ld-content-sidebar' );  ?>

				<script>
				    const currentLessonId = <?php echo $lesson_post->ID; ?>;

				    document.addEventListener('DOMContentLoaded', function () {
				        console.log('DOM fully loaded and parsed');

				        const observer = new MutationObserver(function (mutations, observerInstance) {
				            const expandButton = document.querySelector(`#ld-expand-${currentLessonId} .ld-expand-button`);
				            if (expandButton) {
				                console.log('Expand button found. Triggering click.');
				                expandButton.click();
				                observerInstance.disconnect(); // Stop observing once the button is clicked
				            }
				        });

				        // Observe changes to the DOM
				        observer.observe(document.body, {
				            childList: true,
				            subtree: true,
				        });
				    });
				</script>

			</div>

		<?php else : ?>

			<?php
			wp_enqueue_script(
			    'soundslice-embed',
			    get_template_directory_uri() . '/js/soundslice-embed.js',
			    array(),
			    filemtime(get_template_directory() . '/js/soundslice-embed.js'),
			    true
			);
			?>

		<?php endif; ?>

		<div class="<?php if ($soundslice_id) { echo 'col'; } else { echo 'col-lg-9'; } ?>">
	
			<header class="entry-header">

				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

				<?php if ($soundslice_id) : ?>
					<div class="width-toggle"><i class="fa fa-arrows-h" aria-hidden="true"></i></div>
				<?php endif; ?>

			</header><!-- .entry-header -->

			<div class="entry-content">

				<div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?>">

					<?php
					/**
					 * Fires before the lesson.
					 *
					 * @since 3.0.0
					 *
					 * @param int $post_id   Post ID.
					 * @param int $course_id Course ID.
					 * @param int $user_id   User ID.
					 */
					do_action( 'learndash-lesson-before', get_the_ID(), $course_id, $user_id );

					if ( ( defined( 'LEARNDASH_TEMPLATE_CONTENT_METHOD' ) ) && ( 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD ) ) {
						$shown_content_key = 'learndash-shortcode-wrap-ld_infobar-' . absint( $course_id ) . '_' . (int) get_the_ID() . '_' . absint( $user_id );
						if ( false === strstr( $content, $shown_content_key ) ) {
							$shortcode_out = do_shortcode( '[ld_infobar course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]' );
							if ( ! empty( $shortcode_out ) ) {
								echo $shortcode_out;
							}
						}
					} else {
						learndash_get_template_part(
							'modules/infobar.php',
							array(
								'context'   => 'lesson',
								'course_id' => $course_id,
								'user_id'   => $user_id,
							),
							true
						);
					}

					/**
					 * If the user needs to complete the previous lesson display an alert
					 */
					if ( ( isset( $lesson_progression_enabled ) ) && ( true === (bool) $lesson_progression_enabled ) && ( isset( $previous_lesson_completed ) ) && ( true !== $previous_lesson_completed ) ) {
						if ( ( ! learndash_is_sample( $post ) ) || ( learndash_is_sample( $post ) && true === ( bool) $has_access ) ) {
							$previous_item_id = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $post->ID );
							if ( ! empty( $previous_item_id ) ) {
								learndash_get_template_part(
									'modules/messages/lesson-progression.php',
									array(
										'previous_item' => get_post( $previous_item_id ),
										'course_id'     => $course_id,
										'context'       => 'lesson',
										'user_id'       => $user_id,
									),
									true
								);
							}
						}
					}
					if ( $show_content ) :

						/**
						 * Content and/or tabs
						 */
						learndash_get_template_part(
							'modules/tabs.php',
							array(
								'course_id' => $course_id,
								'post_id'   => get_the_ID(),
								'user_id'   => $user_id,
								'content'   => $content,
								'materials' => $materials,
								'context'   => 'lesson',
							),
							true
						);

						if ( ( defined( 'LEARNDASH_TEMPLATE_CONTENT_METHOD' ) ) && ( 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD ) ) {
							$shown_content_key = 'learndash-shortcode-wrap-course_content-' . absint( $course_id ) . '_' . (int) get_the_ID() . '_' . absint( $user_id );
							if ( false === strstr( $content, $shown_content_key ) ) {
								$shortcode_out = do_shortcode( '[course_content course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]' );
								if ( ! empty( $shortcode_out ) ) {
									echo $shortcode_out;
								}
							}
						} else {
							/**
							 * Display Lesson Assignments
							 */
							if ( learndash_lesson_hasassignments( $post ) && ! empty( $user_id ) ) : // cspell:disable-line.
								$bypass_course_limits_admin_users = learndash_can_user_bypass( $user_id, 'learndash_lesson_assignment' );
								$course_children_steps_completed  = learndash_user_is_course_children_progress_complete( $user_id, $course_id, $post->ID );

								if ( ( learndash_lesson_progression_enabled() && $course_children_steps_completed ) || ! learndash_lesson_progression_enabled() || $bypass_course_limits_admin_users ) :
									/**
									 * Fires before the lesson assignment.
									 *
									 * @since 3.0.0
									 *
									 * @param int $post_id   Post ID.
									 * @param int $course_id Course ID.
									 * @param int $user_id   User ID.
									 */
									do_action( 'learndash-lesson-assignment-before', get_the_ID(), $course_id, $user_id );

									learndash_get_template_part(
										'assignment/listing.php',
										array(
											'course_step_post' => $post,
											'user_id'          => $user_id,
											'course_id'        => $course_id,
										),
										true
									);

									/**
									 * Fires after the lesson assignment.
									 *
									 * @since 3.0.0
									 *
									 * @param int $post_id   Post ID.
									 * @param int $course_id Course ID.
									 * @param int $user_id   User ID.
									 */
									do_action( 'learndash-lesson-assignment-after', get_the_ID(), $course_id, $user_id );

								endif;
							endif;

							/**
							 * Lesson Topics or Quizzes
							 */
							if ( ! empty( $topics ) || ! empty( $quizzes ) ) :

								/**
								 * Fires before the course certificate link
								 *
								 * @since 3.0.0
								 *
								 * @param int $post_id   Post ID.
								 * @param int $course_id Course ID.
								 * @param int $user_id   User ID.
								 */
								do_action( 'learndash-lesson-content-list-before', get_the_ID(), $course_id, $user_id );

								global $post;
								$lesson = array(
									'post' => $post,
								);
								?>

								<div class="ld-lesson-topic-list">
									<?php
									learndash_get_template_part(
										'lesson/listing.php',
										array(
											'course_id' => $course_id,
											'lesson'    => $lesson,
											'topics'    => $topics,
											'quizzes'   => $quizzes,
											'user_id'   => $user_id,
										),
										true
									);
									?>
								</div>

								<?php
								/**
								 * Fires before the course certificate link
								 *
								 * @since 3.0.0
								 *
								 * @param int $post_id   Post ID.
								 * @param int $course_id Course ID.
								 * @param int $user_id   User ID.
								 */
								do_action( 'learndash-lesson-content-list-after', get_the_ID(), $course_id, $user_id );

							endif;
						}

					endif; // end $show_content.

					if ( $soundslice_id ):
						echo '<div id="ssembed" class="soundslice-container"><iframe src="https://www.soundslice.com/slices/' . $soundslice_id  . '/embed/?api=1&enable_print=0&branding=2" width="100%" height="100%" frameBorder="0" allowfullscreen ></iframe></div>';
					endif;

					if ( ( defined( 'LEARNDASH_TEMPLATE_CONTENT_METHOD' ) ) && ( 'shortcode' === LEARNDASH_TEMPLATE_CONTENT_METHOD ) ) {
						$shown_content_key = 'learndash-shortcode-wrap-ld_navigation-' . absint( $course_id ) . '_' . (int) get_the_ID() . '_' . absint( $user_id );
						if ( false === strstr( $content, $shown_content_key ) ) {
							$shortcode_out = do_shortcode( '[ld_navigation course_id="' . $course_id . '" user_id="' . $user_id . '" post_id="' . get_the_ID() . '"]' );
							if ( ! empty( $shortcode_out ) ) {
								echo $shortcode_out;
							}
						}
					} else {

						/**
						 * Set a variable to switch the next button to complete button
						 */
						$can_complete = false;

						if ( $all_quizzes_completed && $logged_in && ! empty( $course_id ) ) :
							$can_complete = $previous_lesson_completed;

							/**
							 * Filters whether a user can complete the lesson or not.
							 *
							 * @since 3.0.0
							 *
							 * @param boolean $can_complete Whether user can complete lesson or not.
							 * @param int     $post_id      Lesson ID/Topic ID.
							 * @param int     $course_id    Course ID.
							 * @param int     $user_id      User ID.
							 */
							$can_complete = apply_filters( 'learndash-lesson-can-complete', $can_complete, get_the_ID(), $course_id, $user_id );
						endif;

						learndash_get_template_part(
							'modules/course-steps.php',
							array(
								'course_id'        => $course_id,
								'course_step_post' => $post,
								'user_id'          => $user_id,
								'course_settings'  => isset( $course_settings ) ? $course_settings : array(),
								'can_complete'     => $can_complete,
								'context'          => 'lesson',
							),
							true
						);
					}

					/**
					 * Fires after the lesson
					 *
					 * @since 3.0.0
					 *
					 * @param int $post_id   Post ID.
					 * @param int $course_id Course ID.
					 * @param int $user_id   User ID.
					 */
					do_action( 'learndash-lesson-after', get_the_ID(), $course_id, $user_id );
					learndash_load_login_modal_html();
					?>

				</div> <!--/.learndash-wrapper-->

				</div><!-- .entry-content -->

			<footer class="entry-footer">
				<?php rslfranchise_entry_footer(); ?>
			</footer><!-- .entry-footer -->

		</div>

	</div>

</article><!-- #post-<?php the_ID(); ?> -->


