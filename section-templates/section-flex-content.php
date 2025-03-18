<?php
/**
 * Flexible Content (ACF 'page builder')
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Check value exists.
if( have_rows('flexible_elements') ):

    // Loop through rows.
    while ( have_rows('flexible_elements') ) : the_row();

        if( get_row_layout() == 'text_module' ): ?>

        	<section class="text_module section-padding">

        		<?php 
                echo get_sub_field('content'); 
                ?>

                <?php 
                $button_text = get_sub_field('button_text');
                $button_link = get_sub_field('button_link');
                render_acf_button($button_text, $button_link);
                ?>

        	</section>

        <?php elseif( get_row_layout() == 'dashboard_hero' ): ?>

            <section class="dashboard_hero">
                
                <?php 
                $image = get_sub_field('image');
                if( !empty( $image ) ): ?>
                    <img class="dashboard-hero-img" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                <?php endif; ?>

                <div class="half-overlay"></div>

                <div class="dashboard_hero-copy">

                    <?php 
                    global $current_user; wp_get_current_user();
                    ?>

                    <h1>
                        <?php 
                        _e('Welcome back', 'rslfranchise'); 
                        echo ' ' . $current_user->display_name; 
                        ?>
                    </h1>

                    <div class="dashboard-hero-intro"><?php the_sub_field('intro'); ?></div>

                </div>

                <div class="dashboard-hero-stats">
                    <?php echo do_shortcode('[ld_profile]'); ?>
                </div>

            </section>

        <?php elseif( get_row_layout() == 'dashboard_courses' ): ?>

            <section class="dashboard_courses">
                    
                <?php
                $current_user_id = get_current_user_id();

                // Get the list of course IDs the current user is enrolled in
                $enrolled_courses = learndash_user_get_enrolled_courses($current_user_id);
                if (!empty($enrolled_courses)) :
                    $args = [
                        'post_type'      => 'sfwd-courses',
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                        'post__in'       => $enrolled_courses, 
                        'orderby'        => 'date',
                        'order'          => 'ASC',
                    ];

                    $query = new WP_Query($args);

                    if ($query->have_posts()) : ?>

                        <div class="row">

                            <?php while ($query->have_posts()) :
                                $query->the_post(); ?>

                                <div class="col-md-6 col-lg-3">

                                    <a href="<?php echo get_permalink(); ?>">
                                        <div class="course-card-inner">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="course-thumbnail">
                                                    <?php the_post_thumbnail('large'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <h3><?php the_title(); ?></h3>
                                            <div class="course-progress">
                                                <?php echo do_shortcode('[learndash_course_progress course_id="' . get_the_ID() . '"]'); ?>
                                            </div>
                                        </div>
                                    </a>

                                </div>

                            <?php endwhile; ?>
                            
                        </div>

                    <?php else:
                        echo '<p>No courses assigned.</p>';
                    endif;

                    // Reset post data
                    wp_reset_postdata();

                else :
                    echo '<p>No courses found.</p>';
                endif;
                ?>

            </section>

        <?php elseif( get_row_layout() == 'group_display' ): ?>

            <?php 
            $group = get_sub_field('ld_group');
            $allowed_roles = get_sub_field('allowed_roles');
            $dont_show_if_all_courses_complete = get_sub_field('dont_show_if_all_courses_complete');
            $user_id = get_current_user_id();

            if ( $group && (!is_user_logged_in() || empty($allowed_roles) || array_intersect($allowed_roles, wp_get_current_user()->roles)) ): ?>

                <section class="learndash-group-dashboard-element">
                    <?php 
                    // override main query
                    global $wp_query;
                    $original_query = $wp_query;
                    $wp_query = new WP_Query([
                        'post_type' => 'groups',
                        'p'         => $group->ID
                    ]);

                    if (have_posts()): while (have_posts()): the_post();

                        echo '<h2 class="group-flex-element-title">' . get_the_title() . '</h2>';
                        the_content(); 

                    endwhile; endif;

                    // Restore original query
                    $wp_query = $original_query;
                    wp_reset_postdata();
                    ?>
                </section>

                <?php wp_reset_postdata(); ?>

            <?php endif; ?>

        <?php elseif( get_row_layout() == 'course_grid' ): ?>

            <?php
            $current_user_id = get_current_user_id();
            $group_category_id = get_sub_field('group_category'); 
            $allow_parent_transfer = get_sub_field('allow_parent_transfer');

            // Check for success message via URL parameter
            if (isset($_GET['assigned']) && $_GET['assigned'] === '1' && isset($_GET['learner_id'])) :
                $learner_id = intval($_GET['learner_id']);
                $manage_learner_url = home_url('/manage-learner/?learner_id=' . $learner_id);
                ?>
                <p class="success-message">
                    <?php echo sprintf(
                        __('Course assigned successfully! <a href="%s">Manage this learner</a>.', 'rslfranchise'),
                        esc_url($manage_learner_url)
                    ); ?>
                </p>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        document.getElementById('assignment-messages')?.scrollIntoView({ behavior: 'smooth' });
                    });
                </script>
            <?php endif;
            if (!empty($error_message)) : ?>
                <p class="error-message"><?php echo esc_html($error_message); ?></p>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        document.getElementById('assignment-messages').scrollIntoView({ behavior: 'smooth' });
                    });
                </script>
            <?php endif; 

            $group_ids = get_posts([
                'post_type'   => 'groups',
                'tax_query'   => [
                    [
                        'taxonomy' => 'ld_group_category',
                        'field'    => 'term_id', 
                        'terms'    => $group_category_id,
                    ],
                ],
                'fields'      => 'ids',
                'numberposts' => -1,
            ]);

            $course_ids = [];

            if (!empty($group_ids)) {
                foreach ($group_ids as $group_id) {
                    $group_courses = learndash_get_group_courses_list($group_id);
                    if (!empty($group_courses)) {
                        foreach ($group_courses as $course_id) {
                            if (sfwd_lms_has_access($course_id, $current_user_id)) { 
                                $course_ids[] = $course_id;
                            }
                        }
                    }
                }
            }

            // echo '<pre>'; print_r($course_ids); echo '</pre>'; // Debugging

            // tax query based on current filters
            $tax_query = [];

            if (!empty($_GET['category'])) {
                $tax_query[] = [
                    'taxonomy' => 'ld_course_category',
                    'field'    => 'slug',
                    'terms'    => $_GET['category'],
                ];
            }

            if (!empty($_GET['tag'])) {
                $tax_query[] = [
                    'taxonomy' => 'ld_course_tag',
                    'field'    => 'slug',
                    'terms'    => $_GET['tag'],
                ];
            }

            if (!empty($_GET['level'])) {
                $tax_query[] = [
                    'taxonomy' => 'ld_course_level',
                    'field'    => 'slug',
                    'terms'    => $_GET['level'],
                ];
            }

            if (!empty($_GET['term'])) {
                $tax_query[] = [
                    'taxonomy' => 'ld_course_term',
                    'field'    => 'slug',
                    'terms'    => $_GET['term'],
                ];
            }

            $args = [
                'post_type'      => 'sfwd-courses',
                'post__in'       => !empty($course_ids) ? $course_ids : [0],
                'posts_per_page' => -1,
                'tax_query'      => !empty($tax_query) ? $tax_query : '',
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ];

            if (!empty($course_ids)) {
                $courses_query = new WP_Query($args);
                // Extract only the IDs of the filtered courses
                $filtered_course_ids = wp_list_pluck($courses_query->posts, 'ID');
                // Pass the course IDs to the filter template
                set_query_var('filtered_course_ids', $filtered_course_ids);
            } else {
                $courses_query = null; // Set to null to avoid processing a query
            } ?>

            <section class="course-grid">
                <h1><?php echo get_sub_field('title'); ?></h1>

                <?php if (get_sub_field('intro')) { echo '<div class="course-grid-intro">' . get_sub_field('intro') . '</div>'; } ?>

                <div class="row">
                    <div class="col-md-4 col-lg-3">
                        <?php get_template_part('section-templates/section', 'course-filters'); ?>
                    </div>
                    <div class="col-md-8 col-lg-9">
                        <?php
                        if (!empty($course_ids) && $courses_query->have_posts()) {
                            echo '<div class="row">';
                            while ($courses_query->have_posts()) {
                                $courses_query->the_post();

                                // ddoublecheck if user has access before displaying course
                                if (!sfwd_lms_has_access(get_the_ID(), $current_user_id)) {
                                    continue; 
                                }
                                ?>
                                <div class="col-md-6 col-lg-4">
                                    <?php 
                                    set_query_var( 'allow_parent_transfer', $allow_parent_transfer );
                                    get_template_part('template-parts/loop', 'sfwd-courses'); 
                                    ?>
                                </div>
                                <?php
                            }
                            echo '</div>';
                            wp_reset_postdata();
                        } else {
                            echo 'No enrolled courses match your filters.';
                        }
                        ?>
                    </div>
                </div>
            </section>

        <?php // Next section here ?>

        <?php endif; // end of flexible content  

    // End loop.
    endwhile;

// No value.
else :
    // Do nothing.
endif;