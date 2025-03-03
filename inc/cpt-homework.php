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
        'Uploaded homework Files',         // Box title
        'homework_files_meta_box_content', // Content callback
        'homework',                       // Post type
        'normal',                         // Context
        'high'                            // Priority
    );

}

add_action('add_meta_boxes', 'homework_files_meta_box');

function homework_files_meta_box_content($post) {
    // Get file URLs stored in the custom field
    $file_urls = get_post_meta($post->ID, 'project_file_url');

    echo '<ul>';
    foreach ($file_urls as $url) {
        echo '<li><a href="' . esc_url($url) . '" target="_blank">' . esc_html($url) . '</a></li>';
    }
    echo '</ul>';
}