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




