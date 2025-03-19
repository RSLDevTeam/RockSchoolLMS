<?php
/**
 * Search webhook function
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Search only post type coursers from learn dash
function searchfilter($query) {
  if ($query->is_search && !is_admin() ) {

      $query->set('post_type',array('sfwd-courses'));
  }
  
  return $query;
  }
  
  add_filter('pre_get_posts','searchfilter');

