<?php

/**
 * bk-maxkebab functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package bk-maxkebab
 */

if (!defined('_S_VERSION')) {
  // Replace the version number of the theme on each release.
  define('_S_VERSION', '1.0.0');
}

require_once get_template_directory()  . '/inc/setup.php';
require_once get_template_directory()  . '/inc/func.php';
require_once get_template_directory()  . '/inc/acf.php';
// require_once get_template_directory()  . '/api/menu-api.php';
require_once get_template_directory()  . '/api/home-api.php';
require_once get_template_directory()  . '/api/footer-api.php';
require_once get_template_directory()  . '/api/product-addons-api.php';
require_once get_template_directory()  . '/api/related-products-by-category-api.php';
require_once get_template_directory()  . '/api/offers-api.php';
require_once get_template_directory()  . '/inc/restapi-secret.php';

/**
 * Load WooCommerce compatibility file.
 */
if (class_exists('WooCommerce')) {
  require get_template_directory() . '/inc/woocommerce.php';
}
add_filter('acfwpcli_fieldgroup_paths', 'add_plugin_path');
function add_plugin_path($paths)
{
  $paths['my_plugin'] = get_template_directory() . '/acf/';
  return $paths;
}

add_filter('woocommerce_store_api_disable_nonce_check', '__return_true');

add_filter('woocommerce_rest_product_object_query', function ($args, $request) {
  if ($request->get_param('product_brand')) {
    $args['tax_query'][] = [
      'taxonomy' => 'product_brand',
      'field'    => 'term_id',
      'terms'    => array_map('intval', explode(',', $request->get_param('product_brand'))),
    ];
  }
  return $args;
}, 10, 2);

add_action('rest_api_init', function () {

  // POST /wp-json/custom/v1/auth/token  { username, password }
  // Note: X-WP-Secret is already validated by the rest_authentication_errors filter above.
  register_rest_route('custom/v1', '/auth/token', [
    'methods'             => 'POST',
    'permission_callback' => '__return_true',
    'callback'            => function (WP_REST_Request $req) {
      $user = wp_authenticate($req->get_param('username'), $req->get_param('password'));
      if (is_wp_error($user)) {
        return new WP_Error('invalid_credentials', 'Wrong username or password.', ['status' => 401]);
      }
      $token = wp_generate_password(64, false);
      update_user_meta($user->ID, '_api_token',     $token);
      update_user_meta($user->ID, '_api_token_exp', time() + WEEK_IN_SECONDS);
      return [
        'token'             => $token,
        'user_email'        => $user->user_email,
        'user_display_name' => $user->display_name,
        'user_nicename'     => $user->user_nicename,
      ];
    },
  ]);

  // POST /wp-json/custom/v1/auth/validate  (Authorization: Bearer <token>)
  register_rest_route('custom/v1', '/auth/validate', [
    'methods'             => 'POST',
    'permission_callback' => '__return_true',
    'callback'            => function (WP_REST_Request $req) {
      $auth  = $req->get_header('authorization') ?? '';
      $token = str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : '';
      if (!$token) {
        return new WP_Error('no_token', 'Missing token.', ['status' => 401]);
      }
      global $wpdb;
      $user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='_api_token' AND meta_value=%s",
        $token
      ));
      if (!$user_id) {
        return new WP_Error('invalid_token', 'Invalid token.', ['status' => 401]);
      }
      $exp = (int) get_user_meta($user_id, '_api_token_exp', true);
      if (time() > $exp) {
        return new WP_Error('expired_token', 'Token expired.', ['status' => 401]);
      }
      return ['code' => 'valid_token', 'data' => ['status' => 200]];
    },
  ]);

  register_rest_route('custom/v1', '/auth/register', [
    'methods'             => 'POST',
    'callback'            => function (WP_REST_Request $req) {
      $email    = sanitize_email($req->get_param('email'));
      $username = sanitize_user($req->get_param('username'));
      $password = $req->get_param('password');

      $user_id = wc_create_new_customer($email, $username, $password, [
        'first_name' => sanitize_text_field($req->get_param('firstName') ?? ''),
        'last_name'  => sanitize_text_field($req->get_param('lastName') ?? ''),
      ]);

      if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', $user_id->get_error_message(), ['status' => 400]);
      }

      return ['success' => true];
    },
    'permission_callback' => '__return_true',
  ]);
});
