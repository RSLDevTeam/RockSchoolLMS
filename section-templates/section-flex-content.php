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

                <section class="learndash-group-flex-element">
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

        <?php // Next section here ?>

        <?php endif; // end of flexible content  

    // End loop.
    endwhile;

// No value.
else :
    // Do nothing.
endif;