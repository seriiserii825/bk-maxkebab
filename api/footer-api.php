<?php
if (!defined('ABSPATH')) exit;

function footer_register()
{
  register_rest_route('global/v1', '/footer', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback' => 'footerHandle',
  ]);
}
add_action('rest_api_init', 'footer_register');

function footerHandle()
{
  $footer_page_id = 'options';
  $footer       = get_field('footer', $footer_page_id);

  return [
    'footer'  => $footer,
  ];
}
