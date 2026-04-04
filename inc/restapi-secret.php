<?php
add_filter('rest_authentication_errors', function ($result) {
  // Пропускаем если уже авторизован (wp-admin, JWT и т.д.)
  if (true === $result || is_wp_error($result)) {
    return $result;
  }

  // Пропускаем для залогиненных пользователей (wp-admin React-страницы)
  if (is_user_logged_in()) {
    return $result;
  }

  $secret = $_SERVER['HTTP_X_WP_SECRET'] ?? '';
  $expected = defined('NUXT_API_SECRET') ? NUXT_API_SECRET : '';

  if (empty($expected) || !hash_equals($expected, $secret)) {
    return new WP_Error(
      'rest_forbidden',
      'Access denied. Secret key is missing or invalid.',
      ['status' => 403]
    );
  }

  return $result;
});
