<?php

/**
 * Custom bbPress template settings.
 * 
 * * @package rslfranchise
 */
function custom_bbpress_template($template) {

  if (is_singular('topic') && file_exists(get_template_directory() . '/bb-forum/single-topic.php')) {
      error_log('Topic Template Loaded');
      return get_template_directory() . '/bb-forum/single-topic.php';
  }
  if (is_singular('forum') && file_exists(get_template_directory() . '/bb-forum/single-forum.php')) {
      return get_template_directory() . '/bb-forum/single-forum.php';
  }
  if (is_author() && file_exists(get_template_directory() . '/bb-forum/single-user.php')) {
      error_log('User Profile Template Loaded');
      return get_template_directory() . '/bb-forum/single-user.php';
  }
  if (is_post_type_archive('forum') && file_exists(get_template_directory() . '/bb-forum/archive-forum.php')) {
      return get_template_directory() . '/bb-forum/archive-forum.php';
  }
  
  return $template;
}
add_filter('template_include', 'custom_bbpress_template');


/**
* Rrestrict bbPress template.
*/
function restrict_bbpress_access($template) {
    
    if (is_bbpress() && !current_user_can('administrator') && !current_user_can('instructor') && !current_user_can('editor') && !current_user_can('author') && !has_learncdash_user_forum_access()) {
        wp_redirect(home_url('/no-permission'));
        exit;
    }

    return $template;
}
add_action('template_redirect', 'restrict_bbpress_access');


function has_learncdash_user_forum_access() {
    if ( class_exists( 'Learndash_BBPress' ) ) {
        $user_id = get_current_user_id();
        $post_id = get_the_ID();
        return learndash_bbpress_user_has_forum_access_validate( $user_id, $post_id );
    }
}

function learndash_bbpress_user_has_forum_access_validate( $user_id, $forum_id )
{
	$associated_courses = get_post_meta( $forum_id, '_ld_associated_courses', true );
	$associated_groups  = get_post_meta( $forum_id, '_ld_associated_groups', true );
	$allow_post = get_post_meta( $forum_id, '_ld_post_limit_access', true );
	$has_access_course = false;
	$has_access_group  = false;
	if ( is_array( $associated_courses ) && ! empty( $associated_courses ) ) {
		foreach( $associated_courses as $associated_course ) {

			// Default value of $allow_post is 'all'
			if ( $allow_post == 'all' ) {
				if ( ! sfwd_lms_has_access( $associated_course, $user_id ) || ! is_user_logged_in() ) {
					return false;
				} else {
					$has_access_course = true;
				}
			} else {
				if ( sfwd_lms_has_access( $associated_course, $user_id ) && is_user_logged_in() ) {
					return true;
				} else {
					$has_access_course = false;
				}
			}
		}
	}

	if ( is_array( $associated_groups ) && ! empty( $associated_groups ) ) {
		foreach ( $associated_groups as $associated_group ) {
			// Default value of $allow_post is 'all'
			if ( $allow_post == 'all' ) {
				if ( ! learndash_is_user_in_group( $user_id, $associated_group ) || ! is_user_logged_in() ) {
					return false;
				} else {
					$has_access_group = true;
				}
			} else {
				if ( learndash_is_user_in_group( $user_id, $associated_group ) && is_user_logged_in() ) {
					return true;
				} else {
					$has_access_group = false;
				}
			}
		}
	}

	if ( $has_access_course && $has_access_group ) {
		return true;
	} else {
		return false;
	}
}



// Add a custom meta box for 'is_featured' in bbPress forums
function add_featured_forum_meta_box() {
add_meta_box(
    'featured_forum_meta_box', // ID
    __('Featured Forum', 'textdomain'), // Title
    'render_featured_forum_checkbox', // Callback function
    'forum', // Post type
    'side', // Context
    'high' // Priority
);
}
add_action('add_meta_boxes', 'add_featured_forum_meta_box');

// Render the checkbox field
function render_featured_forum_checkbox($post) {
// Get existing value
$is_featured = get_post_meta($post->ID, 'is_featured', true);
?>
<label for="is_featured">
    <input type="checkbox" name="is_featured" id="is_featured" value="1" <?php checked($is_featured, '1'); ?>>
    <?php _e('Mark as Featured', 'textdomain'); ?>
</label>
<?php
}

// Save the custom field value
function save_featured_forum_meta($post_id) {
// Check if the value is set and update it, otherwise delete
if (isset($_POST['is_featured'])) {
    update_post_meta($post_id, 'is_featured', '1');
} else {
    delete_post_meta($post_id, 'is_featured');
}
}
add_action('save_post', 'save_featured_forum_meta');


//remove seperator function
add_filter('bbp_before_subscription_link_parse_args', 'remove_subscription_separator');

function remove_subscription_separator( $args ) {
    $args['before'] = '';
    $args['after']  = '';
    return $args;
}
