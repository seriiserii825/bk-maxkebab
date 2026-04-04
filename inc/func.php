<?php
if (!defined('ABSPATH')) {
  exit;
}
function vardump($var)
{
  echo '<pre>';
  var_dump($var);
  echo '</pre>';
}
add_filter('big_image_size_threshold', '__return_zero');
function my_revisions_to_keep($revisions)
{
  return 3;
}
add_filter('wp_revisions_to_keep', 'my_revisions_to_keep');



function remove_pages_editor()
{
  $ids = [50];
  if (in_array(get_the_ID(), $ids)) {
    remove_post_type_support('page', 'editor');
  } // end if
} // end remove_pages_editor
add_action('add_meta_boxes', 'remove_pages_editor');
