<?php
/**
 * Custom post type homework
 *
 * @package underscores
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function register_homework_post_type() {
    $labels = array(
        'name'               => 'Homework',
        'singular_name'      => 'Homework',
        'menu_name'          => 'Homework',
        'name_admin_bar'     => 'Homework',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Homework',
        'new_item'           => 'New Homework',
        'edit_item'          => 'Edit Homework',
        'view_item'          => 'View Homework',
        'all_items'          => 'All Homework',
        'search_items'       => 'Search Homework',
        'not_found'          => 'No homework found',
        'not_found_in_trash' => 'No homework found in trash',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'homework'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 9,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true, 
    );

    register_post_type('homework', $args);
}

add_action('init', 'register_homework_post_type');

// Meta boxes for homework
function homework_files_meta_box() {
    add_meta_box(
        'homework_files_meta_box',          // Unique ID
        'Uploaded Homework Files',         // Box title
        'homework_files_meta_box_content', // Content callback
        'homework',                        // Post type
        'normal',                          // Context
        'high'                             // Priority
    );
}
add_action('add_meta_boxes', 'homework_files_meta_box');

function homework_files_meta_box_content($post) {
    $file_urls = get_post_meta($post->ID, 'project_file_url');

    echo '<ul>';
    foreach ($file_urls as $url) {
        echo '<li><a href="' . esc_url($url) . '" target="_blank">' . esc_html($url) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Add Custom Columns for Assigned Learner, Instructor, and Homework Title
 */
function add_homework_columns($columns) {
    $columns['homework_title'] = __('Homework Title', 'rslfranchise');
    $columns['assigned_learner'] = __('Assigned Learner', 'rslfranchise');
    $columns['assigned_instructor'] = __('Instructor', 'rslfranchise');
    return $columns;
}
add_filter('manage_edit-homework_columns', 'add_homework_columns');

/**
 * Populate Custom Columns with Homework Title, Learner, and Instructor Data
 */
function display_homework_columns($column, $post_id) {
    if ($column == 'homework_title') {
        $homework_title = get_field('title', $post_id); // Get 'title' ACF field
        echo esc_html($homework_title ? $homework_title : __('Untitled', 'rslfranchise'));
    }

    if ($column == 'assigned_learner') {
        $learner = get_field('learner', $post_id); // Get learner from ACF field
        if ($learner) {
            echo esc_html($learner['display_name']);
        } else {
            echo __('Not assigned', 'rslfranchise');
        }
    }

    if ($column == 'assigned_instructor') {
        $instructor = get_field('instructor', $post_id); // Get instructor from ACF field
        if ($instructor) {
            echo esc_html($instructor['display_name']);
        } else {
            echo __('Not assigned', 'rslfranchise');
        }
    }
}
add_action('manage_homework_posts_custom_column', 'display_homework_columns', 10, 2);

/**
 * Make Columns Sortable
 */
function make_homework_columns_sortable($columns) {
    $columns['homework_title'] = 'homework_title';
    $columns['assigned_learner'] = 'assigned_learner';
    $columns['assigned_instructor'] = 'assigned_instructor';
    return $columns;
}
add_filter('manage_edit-homework_sortable_columns', 'make_homework_columns_sortable');