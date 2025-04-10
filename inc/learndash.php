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

// Add custom column to lessons and topics for associated courses
add_filter('manage_sfwd-lessons_posts_columns', 'add_associated_course_column');
add_filter('manage_sfwd-topic_posts_columns', 'add_associated_course_column');
function add_associated_course_column($columns) {
    $columns['associated_course'] = __('Associated Course');
    return $columns;
}

// Add custom column to topics for associated lesson
add_filter('manage_sfwd-topic_posts_columns', 'add_associated_lesson_column');
function add_associated_lesson_column($columns) {
    $columns['associated_lesson'] = __('Associated Lesson');
    return $columns;
}

// Show associated course(s) in column using LearnDash helper (handles shared steps)
add_action('manage_sfwd-lessons_posts_custom_column', 'show_associated_course_column', 10, 2);
add_action('manage_sfwd-topic_posts_custom_column', 'show_associated_course_column', 10, 2);
function show_associated_course_column($column, $post_id) {
    if ($column === 'associated_course') {
        $courses = learndash_get_courses_for_step($post_id);

        if (!empty($courses)) {
            $links = [];

            // Primary course(s)
            if (!empty($courses['primary']) && is_array($courses['primary'])) {
                foreach ($courses['primary'] as $id => $title) {
                    $edit_link = get_edit_post_link($id);
                    $links[] = '<a href="' . esc_url($edit_link) . '"><strong>' . esc_html($title) . '</strong></a>';
                }
            }

            // Secondary (shared) courses
            if (!empty($courses['secondary']) && is_array($courses['secondary'])) {
                foreach ($courses['secondary'] as $id => $title) {
                    $edit_link = get_edit_post_link($id);
                    $links[] = '<a href="' . esc_url($edit_link) . '">' . esc_html($title) . '</a>';
                }
            }

            echo !empty($links) ? implode(', ', $links) : '—';
        } else {
            echo '—';
        }
    }
}

// Show associated lesson (topics only — one-to-one)
add_action('manage_sfwd-topic_posts_custom_column', 'show_associated_lesson_column', 10, 2);
function show_associated_lesson_column($column, $post_id) {
    if ($column === 'associated_lesson') {
        $lesson_id = learndash_get_lesson_id($post_id);
        if ($lesson_id) {
            $lesson_title = get_the_title($lesson_id);
            $edit_link = get_edit_post_link($lesson_id);
            echo '<a href="' . esc_url($edit_link) . '">' . esc_html($lesson_title) . '</a>';
        } else {
            echo '—';
        }
    }
}

