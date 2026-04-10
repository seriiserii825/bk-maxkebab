<?php

// Block any REST request missing a valid X-WP-Secret header (priority 10, runs first).
add_filter('rest_authentication_errors', function ($result) {
  if (true === $result) return $result;
  if (is_user_logged_in()) return $result;

  $secret   = $_SERVER['HTTP_X_WP_SECRET'] ?? '';
  $expected = defined('NUXT_API_SECRET') ? NUXT_API_SECRET : '';

  if (empty($expected) || !hash_equals($expected, $secret)) {
    return new WP_Error('rest_forbidden', 'Access denied. Secret key is missing or invalid.', ['status' => 403]);
  }

  return $result;
}, 10);

// After WooCommerce's OAuth check (priority 99), clear any OAuth error when
// X-WP-Secret is valid — products are public and don't require a logged-in user.
add_filter('rest_authentication_errors', function ($result) {
  if (true === $result) return $result;
  if (is_user_logged_in()) return $result;

  $secret   = $_SERVER['HTTP_X_WP_SECRET'] ?? '';
  $expected = defined('NUXT_API_SECRET') ? NUXT_API_SECRET : '';

  if (!empty($expected) && hash_equals($expected, $secret)) {
    return null;
  }

  return $result;
}, 100);

// Allow unauthenticated read access to WooCommerce products via the REST API.
add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
  if ($post_type === 'product' && $context === 'read') {
    return true;
  }
  return $permission;
}, 10, 4);
