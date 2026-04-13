<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/helpers/get_products_by_query.php';

function random_products_register()
{
  register_rest_route('product/v1', '/random-products', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'get_random_products',
  ]);
}
add_action('rest_api_init', 'random_products_register');

function get_random_products($data)
{
  $exclude = $data['exclude'] ?? [];

  if (is_string($exclude)) {
    $exclude = array_filter(array_map('intval', explode(',', $exclude)));
  }

  return get_products_by_query([
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'orderby'        => 'rand',
    'post__not_in'   => $exclude,
  ]);
}
