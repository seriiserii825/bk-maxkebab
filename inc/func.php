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
  $ids = [50,44];
  if (in_array(get_the_ID(), $ids)) {
    remove_post_type_support('page', 'editor');
  } // end if
} // end remove_pages_editor
add_action('add_meta_boxes', 'remove_pages_editor');


function parse_globus_content($content)
{
  $locale_map = [
    'it_IT' => 'it',
    'en_US' => 'en',
    'ru_RU' => 'ru',
    'ro_RO' => 'ro',
    'de_DE' => 'de',
  ];

  $lang = $locale_map[get_locale()] ?? 'it';

  preg_match_all('/\{:([a-z]+)\}(.*?)\{:\}/s', $content, $matches, PREG_SET_ORDER);

  if (empty($matches)) {
    return $content;
  }

  foreach ($matches as $match) {
    if ($match[1] === $lang) {
      return trim($match[2]);
    }
  }

  // Fallback to Italian
  foreach ($matches as $match) {
    if ($match[1] === 'it') {
      return trim($match[2]);
    }
  }

  return trim($matches[0][2]);
}
