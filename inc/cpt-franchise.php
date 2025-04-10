<?php
/**
 * Custom post type franchise
 *
 * @package underscores
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function register_franchise_post_type() {
    $labels = array(
        'name'               => 'Franchise',
        'singular_name'      => 'Franchise',
        'menu_name'          => 'Franchise',
        'name_admin_bar'     => 'Franchise',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Franchise',
        'new_item'           => 'New Franchise',
        'edit_item'          => 'Edit Franchise',
        'view_item'          => 'View Franchise',
        'all_items'          => 'All Franchise',
        'search_items'       => 'Search Franchise',
        'not_found'          => 'No franchise found',
        'not_found_in_trash' => 'No franchise found in trash',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'franchise'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 9,
        'menu_icon'          => 'dashicons-building',
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true, 
    );

    register_post_type('franchise', $args);
}

add_action('init', 'register_franchise_post_type');

