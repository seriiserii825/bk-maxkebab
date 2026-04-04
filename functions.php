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
require_once get_template_directory()  . '/api/menu-api.php';
require_once get_template_directory()  . '/api/home-api.php';
require_once get_template_directory()  . '/inc/restapi-secret.php';

/**
 * Load WooCommerce compatibility file.
 */
if (class_exists('WooCommerce')) {
  require get_template_directory() . '/inc/woocommerce.php';
}
