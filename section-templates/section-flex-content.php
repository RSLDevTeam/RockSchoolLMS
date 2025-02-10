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

        		<?php echo get_sub_field('content'); ?>

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

        <?php // Next section here ?>

        <?php endif; // end of flexible content  

    // End loop.
    endwhile;

// No value.
else :
    // Do nothing.
endif;