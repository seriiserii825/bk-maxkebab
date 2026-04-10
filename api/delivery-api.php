<?php
if (!defined('ABSPATH')) exit;

function delivery_register()
{
  register_rest_route('page/v1', '/delivery', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback'            => 'deliveryHandle',
  ]);
}
add_action('rest_api_init', 'delivery_register');

function deliveryHandle()
{
  $page_id = 134;
  $items   = get_field('delivery_info', $page_id) ?: [];

  $result = [];
  foreach ($items as $item) {
    $result[] = [
      'title'                => $item['title'],
      'description'          => $item['description'],
      'description_Ialoveni' => $item['description_Ialoveni'],
    ];
  }

  return [
    'image' => get_the_post_thumbnail_url($page_id, 'full'),
    'items' => $result,
  ];
}
