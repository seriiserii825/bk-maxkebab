<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/helpers/get_products_by_query.php';

function offers_register()
{
  register_rest_route('page/v1', '/offers', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'offersHandle',
  ]);
}
add_action('rest_api_init', 'offers_register');

function offersHandle()
{
  $promo_term_id = 15;

  $args = [
    'post_type' => 'product',
    'tax_query' => [
      [
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => $promo_term_id,
      ],
    ],
  ];
  return get_products_by_query($args);
}
