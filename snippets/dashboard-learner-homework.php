<?php
/**
 * Dashboard outstanding homework widget
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user; 
wp_get_current_user();
$user_id = $current_user->ID;
$acf_user_id = 'user_' . $current_user->ID;
?>

<!-- Show My Homework (For Learners) -->
<?php
$my_homework_query = new WP_Query([
    'post_type' => 'homework',
    'posts_per_page' => 3,
    'meta_query' => [
        [
            'key' => 'learner',
            'value' => $current_user->ID,
            'compare' => '=',
        ],
        [
            'key'     => 'response',  
            'compare' => 'NOT EXISTS'
        ],
    ],
]);

if ($my_homework_query->have_posts()) : ?>
    <h2><?php _e('Outstanding Homework', 'rslfranchise'); ?></h2>
    <p><?php _e('Outstanding homework assigned to you', 'rslfranchise'); ?></p>

    <section class="dashboard-section">
        <h3><?php _e('Homework Tasks', 'rslfranchise'); ?></h3>
        <div class="linked-learners-list">
            <?php while ($my_homework_query->have_posts()) : $my_homework_query->the_post(); 
                $instructor = get_field('instructor'); 
                $title      = get_field('title'); 
                $response   = get_field('response'); 
                $is_completed = !empty($response);
                $task = get_field('task');

                $instructor_avatar = $instructor ? get_avatar_url($instructor['ID'], ['size' => 50]) : 'https://secure.gravatar.com/avatar/?s=50&d=mm&r=g';
            ?>
                <div class="linked-learner">
                    <img alt="<?php echo esc_attr($instructor['display_name'] ?? 'Instructor'); ?>" 
                         src="<?php echo esc_url($instructor_avatar); ?>" 
                         class="avatar avatar-50 photo" height="50" width="50" loading="lazy" decoding="async">
                    
                    <div class="learner-details">
                        <div class="learner-details-copy">
                            <strong><?php echo esc_html($instructor['display_name'] ?? 'Unknown Instructor'); ?></strong>
                            <div class="homework-preview-title"><?php echo esc_html($title); ?></div>
                            <div class="homework-excerpt"><?php echo wp_trim_words(wp_strip_all_tags($task), 20, '...'); ?></div>
                        </div>

                        <div class="homework-button-status">
                            <div class="homework-status <?php echo $is_completed ? 'completed' : 'pending'; ?>">
                                <?php echo $is_completed ? __('<i class="fa fa-check"></i> Completed', 'rslfranchise') : __('<i class="fa fa-times"></i> Pending', 'rslfranchise'); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>"><button><?php _e('View', 'rslfranchise'); ?></button></a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <hr>

        <a href="/homework/" class="dashboard-section-link"><?php _e('View all', 'rslfranchise'); ?></a>

    </section>
<?php endif; ?>
<?php wp_reset_postdata(); ?>