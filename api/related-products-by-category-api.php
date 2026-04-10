<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/helpers/get_wp_products.php';
require_once get_template_directory() . '/api/helpers/get_products_by_query.php';

function single_product_register()
{
  register_rest_route('product/v1', '/related-products', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'get_single_product',
  ]);
}
add_action('rest_api_init', 'single_product_register');

function get_single_product($data)
{
  $term_id = $data['term_id'] ?? null;
  $product_id = $data['product_id'] ?? null;

  if (!isset($term_id) || empty($term_id)) {
    return new WP_Error('invalid_term_id', 'Invalid product term_id', ['status' => 400]);
  }

  return  get_products_by_query([
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'post__not_in'   => [$product_id],
    'tax_query'      => [
      [
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => $term_id,
      ],
    ],
  ]);
}
