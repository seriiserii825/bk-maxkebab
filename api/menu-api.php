<?php
if (!defined('ABSPATH')) exit;

function menu_register()
{
  register_rest_route('global/v1', '/menu', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'menuHandle',
  ]);
}
add_action('rest_api_init', 'menu_register');

function menuHandle()
{
  $menu = get_field('menu', 'option');
  $product_categories = $menu['product_categories'] ?? [];
  if (empty($product_categories)) {
    return [];
  }
  $result = [];
  foreach ($product_categories as $product_category) {
    $category = $product_category['category'] ?? null;
    if (!$category) {
      continue;
    }
    $result[] = [
      'id' => $category->term_id,
      'name' => $category->name,
      'slug' => $category->slug,
    ];
  }
  return $result;
}
