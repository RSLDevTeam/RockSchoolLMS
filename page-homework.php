<?php
/*
Template Name: Homework page
Template Post Type: page
*/

get_header();
global $current_user;
wp_get_current_user();
$acf_user_id = 'user_' . $current_user->ID;
$linked_learners = get_field('linked_learners', 'user_' . $current_user->ID);
?>

<main id="primary" class="site-main dashboard-page">

    <?php while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="entry-content">

                <h1><?php _e('Homework', 'rslfranchise'); ?></h1>
                <?php the_content(); ?>

                <!-- Assign Homework Form -->
                <?php if ( in_array('administrator', $current_user->roles) || in_array('instructor', $current_user->roles) && !empty($linked_learners) ) : ?>
                    <h2><?php _e('Assign Homework', 'rslfranchise'); ?></h2>
                    <p><?php _e('Assign a homework task to one of your learners', 'rslfranchise'); ?></p>

                    <section class="dashboard-section">
                        <h3><?php _e('Assign New Homework', 'rslfranchise'); ?></h3>
                        <form method="POST" class="create-homework">
                            <label for="learner"><?php _e('Select Learner', 'rslfranchise'); ?></label>
                            <select name="learner" required>
                                <option value=""><?php _e('Choose a learner', 'rslfranchise'); ?></option>
                                <?php
                                if ($linked_learners) :
                                    foreach ($linked_learners as $learner) :
                                        echo '<option value="' . esc_attr($learner['ID']) . '">' . esc_html($learner['display_name']) . '</option>';
                                    endforeach;
                                endif;
                                ?>
                            </select>

                            <label for="homework_title"><?php _e('Title', 'rslfranchise'); ?></label>
                            <input type="text" name="homework_title" required>

                            <label for="homework_task"><?php _e('Task', 'rslfranchise'); ?></label>
                            <textarea name="homework_task" required></textarea>

                            <input type="submit" name="assign_homework" value="<?php _e('Assign Homework', 'rslfranchise'); ?>">
                        </form>
                    </section>
                <?php endif; ?>

                <?php
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_homework'])) {
                    $learner_id = intval($_POST['learner']);
                    $homework_title = sanitize_text_field($_POST['homework_title']);
                    $homework_task = sanitize_textarea_field($_POST['homework_task']);

                    if ($learner_id && $homework_title && $homework_task) {
                        $learner_user = get_userdata($learner_id);
                        $instructor_name = $current_user->display_name;
                        $learner_name = $learner_user->display_name;
                        $post_title = $learner_name . ' - ' . $instructor_name . ' - ' . current_time('Y-m-d H:i:s');

                        // Create new homework post
                        $post_id = wp_insert_post([
                            'post_title'  => $post_title,
                            'post_status' => 'publish',
                            'post_type'   => 'homework',
                        ]);

                        if ($post_id) {
                            // Assign ACF fields
                            update_field('title', $homework_title, $post_id);
                            update_field('task', $homework_task, $post_id);
                            update_field('learner', $learner_id, $post_id);
                            update_field('instructor', $current_user->ID, $post_id);
                            echo '<p class="success-msg">' . __('Homework assigned successfully!', 'rslfranchise') . '</p>';
                        } else {
                            echo '<p class="error-msg">' . __('Error assigning homework.', 'rslfranchise') . '</p>';
                        }

                        send_homework_notification($post_id);
                    }
                }
                ?>

                <!-- Show Assigned Homework for Instructor -->
                <?php
                $homework_query = new WP_Query([
                    'post_type' => 'homework',
                    'posts_per_page' => -1,
                    'meta_query' => [
                        [
                            'key' => 'instructor',
                            'value' => $current_user->ID,
                            'compare' => '=',
                        ],
                    ],
                ]);

                if ($homework_query->have_posts()) : ?>
                    <h2><?php _e('Your Assigned Homework', 'rslfranchise'); ?></h2>
                    <p><?php _e('View homework assignments that you have assigned to learners', 'rslfranchise'); ?></p>

                    <section class="dashboard-section">
                        <h3><?php _e('Homework Tasks', 'rslfranchise'); ?></h3>
                        <div class="linked-learners-list">
                            <?php while ($homework_query->have_posts()) : $homework_query->the_post(); 
                                $learner   = get_field('learner'); 
                                $title     = get_field('title'); 
                                $response  = get_field('response'); 
                                $is_completed = !empty($response);
                                $task = get_field('task');
                                
                                $learner_avatar = $learner ? get_avatar_url($learner['ID'], ['size' => 50]) : 'https://secure.gravatar.com/avatar/?s=50&d=mm&r=g';
                            ?>
                                <div class="linked-learner">
                                    <img alt="<?php echo esc_attr($learner['display_name'] ?? 'Learner'); ?>" 
                                         src="<?php echo esc_url($learner_avatar); ?>" 
                                         class="avatar avatar-50 photo" height="50" width="50" loading="lazy" decoding="async">
                                    
                                    <div class="learner-details">
                                        <div class="learner-details-copy">
                                            <strong><?php echo esc_html($learner['display_name'] ?? 'Unknown Learner'); ?></strong>
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
                    </section>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>

                <!-- Show My Homework (For Learners) -->
                <?php
                $my_homework_query = new WP_Query([
                    'post_type' => 'homework',
                    'posts_per_page' => -1,
                    'meta_query' => [
                        [
                            'key' => 'learner',
                            'value' => $current_user->ID,
                            'compare' => '=',
                        ],
                    ],
                ]);

                if ($my_homework_query->have_posts()) : ?>
                    <h2><?php _e('My Homework', 'rslfranchise'); ?></h2>
                    <p><?php _e('View homework assigned to you', 'rslfranchise'); ?></p>

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
                    </section>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>

            </div>
        </article>

    <?php endwhile; ?>

</main>

<?php
get_sidebar();
get_footer();