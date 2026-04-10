<?php
if (!defined('ABSPATH')) exit;

function contacts_register()
{
  register_rest_route('page/v1', '/contacts', [
    'methods'             => WP_REST_SERVER::READABLE,
    'permission_callback' => '__return_true',
    'callback'            => 'contactsHandle',
  ]);
}
add_action('rest_api_init', 'contacts_register');

function contactsHandle(WP_REST_Request $request)
{
  $lang = $request->get_param('lang') ?? 'ro';

  return [
    'company_name'        => get_field('full_company_name_' . $lang, 'options'),
    'phone_chisinau'      => get_field('phone_number_chisinau', 'options'),
    'phone_ialoveni'      => get_field('phone_number_ialoveni', 'options'),
    'email'               => get_field('email', 'options'),
    'address_chisinau'    => get_field('full_address_chisinau', 'options'),
    'address_ialoveni'    => get_field('full_address_ialoveni', 'options'),
    'opening_times'       => get_field('opening_times_' . $lang, 'options'),
    'map_chisinau'        => get_field('map_chisinau', 'options'),
    'map_ialoveni'        => get_field('map_ialoveni', 'options'),
    'social_links'        => get_field('social_links', 'options'),
  ];
}
