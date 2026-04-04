<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/catalog-helpers.php';

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
  $slider       = get_field('slider', $home_page_id);

  return [
    'slider'  => $slider,
    'catalog' => home_get_catalog(),
  ];
}
