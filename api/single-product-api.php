<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/helpers/get_wp_products.php';
require_once get_template_directory() . '/api/helpers/get_products_by_query.php';

function single_product_register()
{
  register_rest_route('product/v1', '/single-product', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'get_single_product',
  ]);
}
add_action('rest_api_init', 'single_product_register');

function get_single_product($data)
{
  $slug = $data->get_param('slug');

  if (!isset($slug) || empty($slug)) {
    return new WP_Error('invalid_slug', 'Invalid product slug', ['status' => 400]);
  }
  // $query = new WP_Query([
  //   'post_type'      => 'product',
  //   'posts_per_page' => 1,
  //   'name'           => $slug,
  // ]);
  //
  // return [
  //   'slug'       => $slug,
  //   'found'      => $query->found_posts,
  //   'query_vars' => $query->query_vars,
  // ];

  return get_products_by_query([
    'post_type'      => 'product',
    'posts_per_page' => 1,
    'name'           => $slug,
  ]);
}
