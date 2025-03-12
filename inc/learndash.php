<?php
/**
 * LearnDash functions
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// LearnDash categories
function register_learndash_taxonomies() {

    register_taxonomy(
        'ld_course_level',
        'sfwd-courses',
        array(
            'label'             => __( 'Level', 'textdomain' ),
            'rewrite'           => array( 'slug' => 'course-level' ),
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true, // Enables REST API support
        )
    );

    register_taxonomy(
        'ld_course_term',
        'sfwd-courses',
        array(
            'label'             => __( 'Term', 'textdomain' ),
            'rewrite'           => array( 'slug' => 'course-term' ),
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true, // Enables REST API support
        )
    );
    
}

add_action( 'init', 'register_learndash_taxonomies' );

// Add scheme of work class to courses

add_filter('body_class', function($classes) {
    if (is_singular(['sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz'])) {
        $group_category = get_field('schemes_of_work_taxonomy', 'option');
        $post_id = get_the_ID();
        $course_id = null;

        // Determine the course ID based on post type
        if (get_post_type() === 'sfwd-courses') {
            $course_id = $post_id;
        } elseif (function_exists('learndash_get_course_id')) {
            $course_id = learndash_get_course_id($post_id);
        }

        if ($course_id) {
            $group_ids = learndash_get_course_groups($course_id);
            if (!empty($group_ids)) {
                foreach ($group_ids as $group_id) {
                    if (has_term($group_category, 'ld_group_category', $group_id)) {
                        $classes[] = 'sow-course';
                        break;
                    }
                }
            }
        }
    }

    return $classes;
});

