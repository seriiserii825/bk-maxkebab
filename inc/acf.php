<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

function _acf_set_menu_order(array $fields, int $start = 0): array
{
  $i = $start;
  foreach ($fields as &$field) {
    $field['menu_order'] = $i++;
    foreach (['sub_fields', 'layouts'] as $key) {
      if (!empty($field[$key]) && is_array($field[$key])) {
        $field[$key] = _acf_set_menu_order($field[$key]);
      }
    }
  }
  return $fields;
}

// On import: set menu_order from JSON array position → DB respects it
add_filter('acf/import/field_group', function (array $field_group): array {
  if (!empty($field_group['fields'])) {
    $field_group['fields'] = _acf_set_menu_order($field_group['fields']);
  }
  return $field_group;
});

// On export: write menu_order per field into JSON → order is explicit in file
add_filter('acf/export/field_group', function (array $field_group): array {
  if (!empty($field_group['fields'])) {
    $field_group['fields'] = _acf_set_menu_order($field_group['fields']);
  }
  return $field_group;
});

if (function_exists('acf_add_options_page')) {
  acf_add_options_page(array(
    'page_title' => 'Acf Settings',
    'menu_title' => 'Acf Settings',
    'menu_slug'  => 'theme-general-settings',
    'capability' => 'edit_posts',
    'redirect'   => false
  ));
  acf_add_options_sub_page(array(
    'page_title'  => 'Product categories custom',
    'menu_title'  => 'Product categories custom',
    'parent_slug' => 'theme-general-settings',
  ));
  acf_add_options_sub_page(array(
    'page_title'  => 'Footer',
    'menu_title'  => 'Footer',
    'parent_slug' => 'theme-general-settings',
  ));
  acf_add_options_sub_page(array(
    'page_title'  => 'Menu',
    'menu_title'  => 'Menu',
    'parent_slug' => 'theme-general-settings',
  ));
  acf_add_options_sub_page(array(
    'page_title'  => 'Order',
    'menu_title'  => 'Order',
    'parent_slug' => 'theme-general-settings',
  ));
  acf_add_options_sub_page(array(
    'page_title'  => 'Sectors',
    'menu_title'  => 'Sectors',
    'parent_slug' => 'theme-general-settings',
  ));
}
