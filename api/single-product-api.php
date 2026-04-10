<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/helpers/get_wp_products.php';

function single_product_register()
{
  register_rest_route('global/v1', '/menu', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'get_single_product',
  ]);
}
add_action('rest_api_init', 'single_product_register');

function get_single_product($data)
{
  $product_cat_id = $data['product_cat_id'];
  $term = get_term($product_cat_id, 'product_cat');
  if (is_wp_error($term)) {
    return new WP_Error('invalid_category', 'Invalid product category ID', ['status' => 400]);
  }

  $related_products  = get_wp_products($term);
  return [
    'related_products' => $related_products,
  ];
}
