<?php
if (!defined('ABSPATH')) exit;

function about_register()
{
  register_rest_route('page/v1', '/about', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback'            => 'aboutHandle',
  ]);
}
add_action('rest_api_init', 'about_register');

function aboutHandle(WP_REST_Request $request)
{
  $page    = get_page_by_path('despre-noi');
  $page_id = $page ? $page->ID : 0;

  return [
    'title'   => get_the_title($page_id),
    'content' => apply_filters('the_content', get_post_field('post_content', $page_id)),
    'image'   => get_the_post_thumbnail_url($page_id, 'full'),
  ];
}
