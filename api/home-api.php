<?php
if (!defined('ABSPATH')) exit;

function home_register()
{
  register_rest_route('page/v1', '/home', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'homeHandle',
  ]);
}
add_action('rest_api_init', 'home_register');

function homeHandle()
{
  $home_page_id = 44;
  $slider = get_field('slider', $home_page_id);
  return [
    'slider' => $slider
  ];
}
